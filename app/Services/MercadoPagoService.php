<?php

namespace App\Services;

use App\Models\Cotizacione;
use App\Models\EmpresaIntegracione;
use App\Models\PagoCotizacion;
use App\Models\PagoCotizacionIntento;
use App\Models\Usuario;
use Carbon\Carbon;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class MercadoPagoService
{
    private const API_URL = 'https://api.mercadopago.com';

    public static function obtenerConfigMercadoPago(int $empresaId): array
    {
        $integracion = EmpresaIntegracione::where('empresa_id', $empresaId)
            ->where('proveedor', 'mercadopago')
            ->first();

        if (!$integracion) {
            return [
                'activo' => false,
                'access_token' => null,
                'modo' => 'pruebas',
                'webhook_secret' => null,
                'ambiente_valido' => true,
            ];
        }

        $configuracion = is_array($integracion->configuracion_json)
            ? $integracion->configuracion_json
            : [];
        $accessToken = trim((string) ($integracion->api_key ?? ''));
        $modo = (string) ($configuracion['modo'] ?? 'pruebas');
        $ambienteValido = in_array($modo, ['pruebas', 'produccion'], true)
            && ($accessToken === '' || str_starts_with($accessToken, 'APP_USR-'));
        $activo = (bool) $integracion->activo
            && $accessToken !== ''
            && $ambienteValido;

        return [
            'activo' => $activo,
            'access_token' => $activo ? $accessToken : null,
            'modo' => $modo,
            'webhook_secret' => trim((string) ($integracion->webhook_secret ?? '')),
            'ambiente_valido' => $ambienteValido,
        ];
    }

    public static function credencialesCoincidenConModo(string $accessToken, string $modo): bool
    {
        return in_array($modo, ['pruebas', 'produccion'], true)
            && str_starts_with($accessToken, 'APP_USR-');
    }

    public static function urlSitio(string $ruta): string
    {
        $base = rtrim((string) config('app.url'), '/');

        return $base . '/' . ltrim($ruta, '/');
    }

    public static function urlWebhook(string $referencia): ?string
    {
        $url = self::urlSitio('/mercadopago/webhook/' . rawurlencode($referencia));

        return str_starts_with($url, 'https://') ? $url : null;
    }

    public function crearPreferencia(
        Cotizacione $cotizacion,
        string $idempotencyKey,
        ?int $usuarioId = null
    ): PagoCotizacionIntento {
        if (!Str::isUuid($idempotencyKey)) {
            throw ValidationException::withMessages([
                'mp_idempotency_key' => 'No se pudo identificar este intento de pago. Actualiza la página e intenta de nuevo.',
            ]);
        }

        $config = self::obtenerConfigMercadoPago((int) $cotizacion->empresa_id);
        if (!$config['activo']) {
            throw ValidationException::withMessages([
                'pago' => $config['ambiente_valido']
                    ? 'El pago en línea no está disponible. Contacta al administrador para configurar Mercado Pago.'
                    : 'La configuración de Mercado Pago no es válida. Contacta al administrador.',
            ]);
        }

        $lock = Cache::lock('mercadopago-cotizacion-' . $cotizacion->id, 35);

        if (!$lock->block(5)) {
            throw ValidationException::withMessages([
                'pago' => 'Hay otro intento de pago en curso. Espera un momento e intenta de nuevo.',
            ]);
        }

        try {
            return $this->crearPreferenciaBloqueada(
                $cotizacion,
                $idempotencyKey,
                $usuarioId,
                $config
            );
        } finally {
            $lock->release();
        }
    }

    private function crearPreferenciaBloqueada(
        Cotizacione $cotizacion,
        string $idempotencyKey,
        ?int $usuarioId,
        array $config
    ): PagoCotizacionIntento {
        $monto = round((float) $cotizacion->costo_total, 2);
        $reserva = DB::transaction(function () use (
            $cotizacion,
            $idempotencyKey,
            $usuarioId,
            $monto,
            $config
        ): array {
            $cotizacionBloqueada = Cotizacione::with('cliente.usuario')
                ->where('empresa_id', $cotizacion->empresa_id)
                ->lockForUpdate()
                ->findOrFail($cotizacion->id);

            if ($cotizacionBloqueada->estatus === 'rechazado') {
                throw ValidationException::withMessages([
                    'pago' => 'Esta cotización fue rechazada y ya no permite pagos en línea.',
                ]);
            }

            if ($cotizacionBloqueada->estatus_pago === 'pagado'
                || PagoCotizacion::where('cotizacion_id', $cotizacionBloqueada->id)
                    ->where('estatus', 'pagado')
                    ->exists()) {
                throw ValidationException::withMessages([
                    'pago' => 'Esta cotización ya cuenta con un pago confirmado.',
                ]);
            }

            if ($monto <= 0) {
                throw ValidationException::withMessages([
                    'pago' => 'La cotización no tiene un importe válido para cobrar.',
                ]);
            }

            $intento = PagoCotizacionIntento::where('idempotency_key', $idempotencyKey)
                ->first();

            if ($intento && ($intento->empresa_id !== $cotizacionBloqueada->empresa_id
                || $intento->cotizacion_id !== $cotizacionBloqueada->id)) {
                throw ValidationException::withMessages([
                    'pago' => 'La clave de este intento no corresponde a la cotización.',
                ]);
            }

            $snapshot = is_array($intento?->request_payload) ? $intento->request_payload : [];
            if ($intento && isset($snapshot['monto'])
                && round((float) $snapshot['monto'], 2) !== $monto) {
                throw ValidationException::withMessages([
                    'pago' => 'El importe de la cotización cambió después de iniciar el pago. Solicita una nueva cotización.',
                ]);
            }

            if ($intento?->estado === 'aprobado' || $intento?->pago_cotizacion_id) {
                return [$intento, $cotizacionBloqueada, true];
            }

            if ($intento?->estado === 'pendiente'
                && $intento->checkout_url
                && isset($snapshot['expira_en'])
                && Carbon::parse($snapshot['expira_en'])->greaterThan(now()->addMinute())) {
                return [$intento, $cotizacionBloqueada, true];
            }

            $intentoActivo = PagoCotizacionIntento::where('cotizacion_id', $cotizacionBloqueada->id)
                ->where(function ($query) {
                    $query->where('estado', 'pendiente')
                        ->orWhere(function ($subquery) {
                            $subquery->whereIn('estado', ['creando', 'rechazado'])
                                ->where('updated_at', '>=', now()->subMinutes(10));
                        });
                })
                ->latest('id')
                ->first();

            if ($intentoActivo && $intentoActivo->id !== $intento?->id) {
                $snapshotActivo = is_array($intentoActivo->request_payload)
                    ? $intentoActivo->request_payload
                    : [];
                $activoVigente = in_array($intentoActivo->estado, ['creando', 'pendiente'], true)
                    || ($intentoActivo->estado === 'rechazado'
                        && isset($snapshotActivo['expira_en'])
                        && Carbon::parse($snapshotActivo['expira_en'])->greaterThan(now()->addMinute()));

                if ($activoVigente) {
                    if ($intentoActivo->checkout_url) {
                        return [$intentoActivo, $cotizacionBloqueada, true];
                    }

                    throw ValidationException::withMessages([
                        'pago' => 'Hay otro intento de pago en curso. Espera un momento e intenta de nuevo.',
                    ]);
                }
            }

            if (!$intento) {
                do {
                    $referencia = 'PAG-' . strtoupper(Str::uuid());
                } while (PagoCotizacionIntento::where('referencia', $referencia)->exists());

                $intento = PagoCotizacionIntento::create([
                    'empresa_id' => $cotizacionBloqueada->empresa_id,
                    'cotizacion_id' => $cotizacionBloqueada->id,
                    'idempotency_key' => $idempotencyKey,
                    'provider_idempotency_key' => (string) Str::uuid(),
                    'referencia' => $referencia,
                    'estado' => 'creando',
                ]);
            }

            $usuario = $usuarioId ? Usuario::find($usuarioId) : null;
            $correo = $cotizacionBloqueada->cliente?->email
                ?: $cotizacionBloqueada->cliente?->usuario?->email;
            $correoValido = is_string($correo) && filter_var($correo, FILTER_VALIDATE_EMAIL);

            $intento->update([
                'estado' => 'creando',
                'mercadopago_preference_id' => null,
                'checkout_url' => null,
                'mercadopago_payment_id' => null,
                'mensaje_error' => null,
                'request_payload' => [
                    'monto' => $monto,
                    'moneda' => 'MXN',
                    'modo' => $config['modo'],
                    'usuario_id' => $usuarioId,
                    'es_cliente' => $usuario?->rol === 'cliente',
                    'expira_en' => now()->addHours(2)->toIso8601String(),
                ],
            ]);

            return [$intento, $cotizacionBloqueada, false];
        });

        [$intento, $cotizacion, $listo] = $reserva;
        if ($listo && $intento->estado === 'aprobado') {
            return $intento;
        }

        if ($listo && $intento->checkout_url) {
            return $intento;
        }

        $snapshot = $intento->request_payload;
        $payload = [
            'items' => [[
                'id' => 'cotizacion-' . $cotizacion->id,
                'title' => 'Cotización ' . ($cotizacion->folio ?: 'COT-' . $cotizacion->id),
                'description' => 'Servicio de grúa cotizado por SIGESGA.',
                'quantity' => 1,
                'currency_id' => 'MXN',
                'unit_price' => (float) $snapshot['monto'],
            ]],
            'external_reference' => $intento->referencia,
            'back_urls' => [
                'success' => self::urlSitio('/panel/mercadopago/retorno/' . $intento->referencia),
                'failure' => self::urlSitio('/panel/mercadopago/retorno/' . $intento->referencia),
                'pending' => self::urlSitio('/panel/mercadopago/retorno/' . $intento->referencia),
            ],
            'auto_return' => 'approved',
            'statement_descriptor' => 'SIGESGA',
            'expires' => true,
            'expiration_date_from' => now()->toIso8601String(),
            'expiration_date_to' => Carbon::parse($snapshot['expira_en'])->toIso8601String(),
            'metadata' => [
                'empresa_id' => $cotizacion->empresa_id,
                'cotizacion_id' => $cotizacion->id,
            ],
        ];

        $nombreCliente = trim(implode(' ', array_filter([
            $cotizacion->cliente?->nombre,
            $cotizacion->cliente?->apellido_paterno,
        ])));
        if ($nombreCliente !== '') {
            $payload['payer']['name'] = $nombreCliente;
        }

        $correoCliente = $cotizacion->cliente?->email ?: $cotizacion->cliente?->usuario?->email;
        if (is_string($correoCliente) && filter_var($correoCliente, FILTER_VALIDATE_EMAIL)) {
            $payload['payer']['email'] = $correoCliente;
        }

        $notificationUrl = self::urlWebhook($intento->referencia);
        if ($notificationUrl !== null) {
            $payload['notification_url'] = $notificationUrl;
        }

        try {
            $response = Http::withToken($config['access_token'])
                ->acceptJson()
                ->timeout(25)
                ->post(self::API_URL . '/checkout/preferences', $payload);
        } catch (ConnectionException $exception) {
            $this->marcarError($intento, 'No se pudo conectar con Mercado Pago. Intenta de nuevo.');
            Log::warning('Error de conexión al crear preferencia de Mercado Pago', [
                'intento_id' => $intento->id,
                'error' => $exception->getMessage(),
            ]);

            throw ValidationException::withMessages([
                'pago' => 'No se pudo iniciar el pago por un problema de conexión. Intenta de nuevo.',
            ]);
        }

        $resultado = $response->json();
        $urlCheckout = $config['modo'] === 'produccion'
            ? ($resultado['init_point'] ?? null)
            : ($resultado['sandbox_init_point'] ?? null);

        if (!$response->successful()
            || !is_string($urlCheckout)
            || !filter_var($urlCheckout, FILTER_VALIDATE_URL)
            || !str_starts_with($urlCheckout, 'https://')) {
            $detalle = is_array($resultado)
                ? Str::limit((string) ($resultado['message'] ?? $resultado['error'] ?? ''), 250)
                : '';
            $this->marcarError($intento, $detalle ?: 'Mercado Pago no aceptó la creación del pago.');
            Log::warning('Mercado Pago rechazó la creación de preferencia', [
                'intento_id' => $intento->id,
                'http_status' => $response->status(),
                'detalle' => $detalle,
            ]);

            throw ValidationException::withMessages([
                'pago' => 'No se pudo iniciar el pago. Revisa las credenciales y el entorno configurado en Integraciones.',
            ]);
        }

        $intento->update([
            'estado' => 'pendiente',
            'mercadopago_preference_id' => is_string($resultado['id'] ?? null)
                ? $resultado['id']
                : null,
            'checkout_url' => $urlCheckout,
            'mensaje_error' => null,
        ]);

        return $intento->refresh();
    }

    public function consultarPago(PagoCotizacionIntento $intento, string $paymentId): array
    {
        $config = self::obtenerConfigMercadoPago((int) $intento->empresa_id);
        if (!$config['activo']) {
            throw new RuntimeException('Mercado Pago no está configurado para esta empresa.');
        }

        $response = Http::withToken($config['access_token'])
            ->acceptJson()
            ->timeout(20)
            ->get(self::API_URL . '/v1/payments/' . rawurlencode($paymentId));

        if (!$response->successful() || !is_array($response->json())) {
            Log::warning('No se pudo consultar un pago de Mercado Pago', [
                'intento_id' => $intento->id,
                'http_status' => $response->status(),
            ]);

            throw new RuntimeException('Mercado Pago no devolvió el pago solicitado.');
        }

        return $response->json();
    }

    public function buscarPagoPorReferencia(PagoCotizacionIntento $intento): ?array
    {
        $config = self::obtenerConfigMercadoPago((int) $intento->empresa_id);
        if (!$config['activo']) {
            throw new RuntimeException('Mercado Pago no está configurado para esta empresa.');
        }

        $response = Http::withToken($config['access_token'])
            ->acceptJson()
            ->timeout(20)
            ->get(self::API_URL . '/v1/payments/search', [
                'external_reference' => $intento->referencia,
                'sort' => 'date_created',
                'criteria' => 'desc',
                'limit' => 20,
            ]);

        if (!$response->successful()) {
            throw new RuntimeException('No se pudo reconciliar el pago con Mercado Pago.');
        }

        $resultados = $response->json('results', []);
        if (!is_array($resultados) || $resultados === []) {
            return null;
        }

        $coincidentes = array_values(array_filter(
            $resultados,
            fn ($pago) => is_array($pago)
                && ($pago['external_reference'] ?? null) === $intento->referencia
        ));

        foreach ($coincidentes as $pago) {
            if (($pago['status'] ?? null) === 'approved') {
                return $pago;
            }
        }

        return $coincidentes[0] ?? null;
    }

    public function pagoCorrespondeAlIntento(PagoCotizacionIntento $intento, array $pago): bool
    {
        $snapshot = is_array($intento->request_payload) ? $intento->request_payload : [];
        $montoEsperado = (float) ($snapshot['monto'] ?? 0);
        $monedaEsperada = (string) ($snapshot['moneda'] ?? 'MXN');
        $modoEsperado = (string) ($snapshot['modo'] ?? 'pruebas');

        return ($pago['external_reference'] ?? null) === $intento->referencia
            && ($pago['currency_id'] ?? null) === $monedaEsperada
            && round((float) ($pago['transaction_amount'] ?? -1), 2) === round($montoEsperado, 2)
            && array_key_exists('live_mode', $pago)
            && (bool) $pago['live_mode'] === ($modoEsperado === 'produccion');
    }

    public function firmaWebhookValida(
        string $signature,
        string $requestId,
        string $dataId,
        string $secret
    ): bool {
        if ($signature === '' || $requestId === '' || $dataId === '' || $secret === '') {
            return false;
        }

        $valores = [];
        foreach (explode(',', $signature) as $parte) {
            [$clave, $valor] = array_pad(explode('=', trim($parte), 2), 2, '');
            $valores[$clave] = $valor;
        }

        $ts = $valores['ts'] ?? '';
        if (!ctype_digit($ts) || !isset($valores['v1'])) {
            return false;
        }

        $timestampMs = strlen($ts) <= 10 ? ((int) $ts * 1000) : (int) $ts;
        if (abs(now()->getTimestampMs() - $timestampMs) > 300000) {
            return false;
        }

        $contenido = "id:{$dataId}:request:{$requestId}:ts:{$ts}";
        $esperado = hash_hmac('sha256', $contenido, $secret);

        foreach (explode(',', $valores['v1']) as $firma) {
            if (hash_equals($esperado, trim($firma))) {
                return true;
            }
        }

        return false;
    }

    private function marcarError(PagoCotizacionIntento $intento, string $mensaje): void
    {
        $intento->update([
            'estado' => 'error',
            'mensaje_error' => Str::limit($mensaje, 500),
        ]);
    }
}

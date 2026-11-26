<?php

// Procesador de cobros con tarjeta para cotizaciones con soporte de Mercado Pago Checkout API (API de Orders).
// Con Mercado Pago el navegador tokeniza la tarjeta y el servidor nunca recibe ni almacena el PAN completo o CVV.

namespace App\Services;

use App\Models\Cotizacione;
use App\Models\EmpresaIntegracione;
use App\Models\PagoCotizacion;
use App\Models\PagoCotizacionIntento;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PagoTarjetaService
{
    /** Obtiene las credenciales activas de Mercado Pago desde Integraciones (empresa_integraciones). */
    public static function obtenerConfigMercadoPago(int $empresaId): array
    {
        $integracion = EmpresaIntegracione::where('empresa_id', $empresaId)
            ->where('proveedor', 'mercadopago')
            ->first();

        if (!$integracion || !$integracion->activo) {
            return ['activo' => false, 'public_key' => null, 'access_token' => null, 'ambiente_valido' => true];
        }

        $publicKey = trim((string) ($integracion->configuracion_json['public_key'] ?? ''));
        $accessToken = trim((string) ($integracion->api_key ?? ''));
        $modo = (string) ($integracion->configuracion_json['modo'] ?? 'pruebas');
        $credencialesPresentes = $publicKey !== '' && $accessToken !== '';
        $ambienteValido = !$credencialesPresentes || self::credencialesCoincidenConModo($publicKey, $accessToken, $modo);

        return [
            'activo' => $credencialesPresentes && $ambienteValido,
            'public_key' => $publicKey !== '' && $ambienteValido ? $publicKey : null,
            'access_token' => $accessToken !== '' && $ambienteValido ? $accessToken : null,
            'ambiente_valido' => $ambienteValido,
        ];
    }

    public static function credencialesCoincidenConModo(string $publicKey, string $accessToken, string $modo): bool
    {
        $prefijoLlavePublica = match ($modo) {
            'pruebas' => 'TEST-',
            'produccion' => 'APP_USR-',
            default => null,
        };

        // Orders API test and production Access Tokens both use the APP_USR prefix.
        return $prefijoLlavePublica !== null
            && str_starts_with($publicKey, $prefijoLlavePublica)
            && str_starts_with($accessToken, 'APP_USR-');
    }

    /** Mapea el payment_method_id de Mercado Pago a una marca legible. */
    public static function marcaDesdeMetodoMp(string $methodId, ?string $fallback = null): string
    {
        if (!empty($fallback)) {
            return $fallback;
        }

        return match (strtolower($methodId)) {
            'visa', 'debvisa' => 'Visa',
            'master', 'debmaster' => 'Mastercard',
            'amex' => 'American Express',
            'carnet' => 'Carnet',
            default => ucfirst($methodId),
        };
    }

    /** Verifica que la tarjeta no esté vencida respecto al mes/año actual. */
    public static function validarVigencia(int $mes, int $anio): bool
    {
        if ($mes < 1 || $mes > 12) {
            return false;
        }

        $anioActual = (int) now()->format('Y');
        $mesActual = (int) now()->format('n');

        if ($anio < $anioActual || $anio > $anioActual + 20) {
            return false;
        }

        if ($anio === $anioActual && $mes < $mesActual) {
            return false;
        }

        return true;
    }

    /** Procesa un pago de forma idempotente y conserva el payload cifrado para reintentos seguros. */
    public function cobrar(Cotizacione $cotizacion, array $datosTarjeta, ?int $usuarioId = null): PagoCotizacion
    {
        $titular = trim((string) ($datosTarjeta['tarjeta_titular'] ?? ''));
        $expMes = (int) ($datosTarjeta['tarjeta_exp_mes'] ?? 0);
        $expAnio = (int) ($datosTarjeta['tarjeta_exp_anio'] ?? 0);
        $mpToken = trim((string) ($datosTarjeta['mp_token'] ?? ''));
        $idempotencyKey = trim((string) ($datosTarjeta['mp_idempotency_key'] ?? ''));

        if (!self::validarVigencia($expMes, $expAnio)) {
            throw ValidationException::withMessages([
                'tarjeta_exp_mes' => 'La tarjeta se encuentra vencida o la fecha no es válida.',
            ]);
        }

        $mpConfig = self::obtenerConfigMercadoPago((int) $cotizacion->empresa_id);
        if (!$mpConfig['activo'] || empty($mpConfig['access_token'])) {
            throw ValidationException::withMessages([
                'tarjeta' => $mpConfig['ambiente_valido']
                    ? 'El cobro con tarjeta no está disponible. Contacta al administrador para configurar Mercado Pago.'
                    : 'Las llaves de Mercado Pago no corresponden al entorno seleccionado. Contacta al administrador.',
            ]);
        }
        if ($mpToken === '') {
            throw ValidationException::withMessages([
                'mp_token' => 'No se recibió el token seguro de Mercado Pago. Verifica los datos de tu tarjeta.',
            ]);
        }
        if (!Str::isUuid($idempotencyKey)) {
            throw ValidationException::withMessages([
                'mp_idempotency_key' => 'No se pudo identificar este intento de pago. Actualiza la página e intenta de nuevo.',
            ]);
        }

        $ultimos4 = preg_replace('/\D+/', '', (string) ($datosTarjeta['tarjeta_ultimos4'] ?? '')) ?? '';
        if (strlen($ultimos4) !== 4) {
            throw ValidationException::withMessages([
                'tarjeta_ultimos4' => 'No se pudieron verificar los últimos 4 dígitos de la tarjeta.',
            ]);
        }
        $methodId = strtolower(trim((string) ($datosTarjeta['mp_payment_method_id'] ?? '')));
        $paymentType = strtolower(trim((string) ($datosTarjeta['mp_payment_type_id'] ?? '')));
        $installments = max(1, min(12, (int) ($datosTarjeta['mp_installments'] ?? 1)));
        $deviceId = trim((string) ($datosTarjeta['mp_device_id'] ?? ''));
        $marca = self::marcaDesdeMetodoMp($methodId, $datosTarjeta['tarjeta_marca'] ?? null);

        $reserva = DB::transaction(function () use (
            $cotizacion, $datosTarjeta, $idempotencyKey, $mpToken, $methodId, $paymentType, $usuarioId,
            $installments, $deviceId, $titular, $marca, $ultimos4, $expMes, $expAnio
        ): array {
            $cotBloqueada = Cotizacione::with(['cliente.usuario'])
                ->where('empresa_id', $cotizacion->empresa_id)
                ->lockForUpdate()
                ->findOrFail($cotizacion->id);

            $intento = PagoCotizacionIntento::where('idempotency_key', $idempotencyKey)
                ->lockForUpdate()
                ->first();

            if ($intento) {
                if ((int) $intento->cotizacion_id !== (int) $cotBloqueada->id) {
                    throw ValidationException::withMessages([
                        'mp_idempotency_key' => 'La clave de este intento ya fue utilizada para otra cotización.',
                    ]);
                }
                if ($intento->estado === 'aprobado' && $intento->pago) {
                    return ['pago' => $intento->pago, 'intento' => null];
                }
                if ($intento->estado === 'rechazado') {
                    throw ValidationException::withMessages([
                        'tarjeta' => $intento->mensaje_error ?: 'El pago fue rechazado. Verifica los datos o intenta con otra tarjeta.',
                        'mp_attempt_status' => 'rejected',
                    ]);
                }
                if (!$intento->request_payload) {
                    throw ValidationException::withMessages([
                        'tarjeta' => 'No fue posible recuperar el intento de pago. Contacta a soporte antes de volver a pagar.',
                        'mp_attempt_status' => 'uncertain',
                    ]);
                }

                $intento->update(['estado' => 'procesando']);

                return ['pago' => null, 'intento' => $intento->fresh()];
            }

            if ($cotBloqueada->estatus === 'rechazado') {
                throw ValidationException::withMessages([
                    'tarjeta' => 'No se puede procesar un pago para una cotización rechazada.',
                ]);
            }

            $ultimoIntento = PagoCotizacionIntento::where('cotizacion_id', $cotBloqueada->id)
                ->latest('id')
                ->lockForUpdate()
                ->first();
            if ($ultimoIntento && in_array($ultimoIntento->estado, ['procesando', 'incierto'], true)) {
                throw ValidationException::withMessages([
                    'tarjeta' => 'El resultado del intento anterior aún no está confirmado. Reintenta este mismo pago antes de iniciar otro.',
                    'mp_attempt_status' => 'uncertain',
                ]);
            }

            if ($cotBloqueada->estatus_pago === 'pagado') {
                throw ValidationException::withMessages([
                    'tarjeta' => 'Esta cotización ya fue pagada previamente.',
                ]);
            }

            $monto = round((float) ($cotBloqueada->costo_total ?? 0), 2);
            if ($monto <= 0) {
                throw ValidationException::withMessages([
                    'tarjeta' => 'El monto de la cotización debe ser mayor a $0.00 para procesar el cobro.',
                ]);
            }

            $payerEmail = $cotBloqueada->cliente?->email
                ?: $cotBloqueada->cliente?->usuario?->email
                ?: Usuario::whereKey($usuarioId)->value('email');

            $partesNombre = preg_split('/\s+/', $titular, 2) ?: [];
            $firstName = $partesNombre[0] ?? 'Cliente';
            $lastName = $partesNombre[1] ?? '';
            $referencia = 'PAG-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6));
            $montoStr = number_format($monto, 2, '.', '');
            $folio = (string) ($cotBloqueada->folio ?? $cotBloqueada->id);
            $payer = [
                'first_name' => $firstName,
            ];
            if ($lastName !== '') {
                $payer['last_name'] = $lastName;
            }
            if (filter_var($payerEmail, FILTER_VALIDATE_EMAIL)) {
                $payer['email'] = $payerEmail;
            }
            $requestPayload = [
                'type' => 'online',
                'processing_mode' => 'automatic',
                'capture_mode' => 'automatic',
                'external_reference' => $referencia,
                'description' => 'Cotizacion ' . $folio . ' - SIGESGA',
                'total_amount' => $montoStr,
                'payer' => $payer,
                'items' => [[
                    'title' => 'Servicio de grua - Cotizacion ' . $folio,
                    'description' => 'Servicio de grua cotizado por SIGESGA.',
                    'quantity' => 1,
                    'unit_price' => $montoStr,
                ]],
                'transactions' => [
                    'payments' => [[
                        'amount' => $montoStr,
                        'payment_method' => [
                            'id' => $methodId,
                            'type' => $paymentType,
                            'token' => $mpToken,
                            'installments' => $installments,
                            'statement_descriptor' => 'SIGESGA',
                        ],
                    ]],
                ],
            ];

            $intento = PagoCotizacionIntento::create([
                'empresa_id' => $cotBloqueada->empresa_id,
                'cotizacion_id' => $cotBloqueada->id,
                'idempotency_key' => $idempotencyKey,
                'provider_idempotency_key' => (string) Str::uuid(),
                'referencia' => $referencia,
                'estado' => 'procesando',
                'request_payload' => [
                    'payload' => $requestPayload,
                    'device_id' => $deviceId,
                    'tarjeta' => [
                        'titular' => $titular,
                        'marca' => $marca,
                        'ultimos4' => $ultimos4,
                        'exp_mes' => $expMes,
                        'exp_anio' => $expAnio,
                    ],
                    'usuario_id' => auth()->id(),
                ],
            ]);

            return ['pago' => null, 'intento' => $intento];
        });

        if ($reserva['pago'] instanceof PagoCotizacion) {
            return $reserva['pago'];
        }

        /** @var PagoCotizacionIntento $intento */
        $intento = $reserva['intento'];
        $payloadGuardado = $intento->request_payload;

        $resultadoMp = $this->enviarOrdenMercadoPago(
            accessToken: $mpConfig['access_token'],
            payload: $payloadGuardado['payload'],
            providerIdempotencyKey: $intento->provider_idempotency_key,
            deviceId: $payloadGuardado['device_id'] ?? '',
        );

        if ($resultadoMp['estado'] === 'incierto') {
            $intento->update([
                'estado' => 'incierto',
                'mercadopago_order_id' => $resultadoMp['order_id'],
                'mensaje_error' => $resultadoMp['mensaje'],
            ]);

            throw ValidationException::withMessages([
                'tarjeta' => $resultadoMp['mensaje'],
                'mp_attempt_status' => 'uncertain',
            ]);
        }

        if ($resultadoMp['estado'] === 'rechazado') {
            $intento->update([
                'estado' => 'rechazado',
                'mercadopago_order_id' => $resultadoMp['order_id'],
                'mercadopago_payment_id' => $resultadoMp['payment_id'],
                'request_payload' => null,
                'mensaje_error' => $resultadoMp['mensaje'],
            ]);

            throw ValidationException::withMessages([
                'tarjeta' => $resultadoMp['mensaje'],
                'mp_attempt_status' => 'rejected',
            ]);
        }

        if ($resultadoMp['estado'] === 'error') {
            $intento->update([
                'estado' => 'error',
                'mercadopago_order_id' => $resultadoMp['order_id'],
                'mercadopago_payment_id' => $resultadoMp['payment_id'],
                'request_payload' => null,
                'mensaje_error' => $resultadoMp['mensaje'],
            ]);

            throw ValidationException::withMessages([
                'tarjeta' => $resultadoMp['mensaje'],
                'mp_attempt_status' => 'technical_error',
            ]);
        }

        return DB::transaction(function () use ($cotizacion, $intento, $resultadoMp, $usuarioId): PagoCotizacion {
            $intentoBloqueado = PagoCotizacionIntento::whereKey($intento->id)->lockForUpdate()->firstOrFail();
            if ($intentoBloqueado->estado === 'aprobado' && $intentoBloqueado->pago) {
                return $intentoBloqueado->pago;
            }

            $cotBloqueada = Cotizacione::where('empresa_id', $cotizacion->empresa_id)
                ->lockForUpdate()
                ->findOrFail($cotizacion->id);

            $pagoExistente = PagoCotizacion::where('cotizacion_id', $cotBloqueada->id)
                ->where('estatus', 'pagado')
                ->first();
            if ($pagoExistente) {
                $intentoBloqueado->update([
                    'estado' => 'aprobado',
                    'pago_cotizacion_id' => $pagoExistente->id,
                    'request_payload' => null,
                ]);

                return $pagoExistente;
            }

            $payloadIntento = $intentoBloqueado->request_payload;
            $datosTarjeta = $payloadIntento['tarjeta'];
            $pago = PagoCotizacion::create([
                'empresa_id' => $cotBloqueada->empresa_id,
                'cotizacion_id' => $cotBloqueada->id,
                'cliente_id' => $cotBloqueada->cliente_id,
                'usuario_id' => $payloadIntento['usuario_id'] ?? $usuarioId,
                'referencia' => $intentoBloqueado->referencia,
                'metodo' => 'tarjeta',
                'pasarela' => 'mercadopago_orders',
                'pasarela_transaccion_id' => $resultadoMp['payment_id'] ?: $resultadoMp['order_id'],
                'monto' => (float) $cotBloqueada->costo_total,
                'moneda' => 'MXN',
                'tarjeta_titular' => $datosTarjeta['titular'],
                'tarjeta_marca' => $datosTarjeta['marca'],
                'tarjeta_ultimos4' => $datosTarjeta['ultimos4'],
                'tarjeta_exp_mes' => $datosTarjeta['exp_mes'],
                'tarjeta_exp_anio' => $datosTarjeta['exp_anio'],
                'estatus' => 'pagado',
                'pagado_en' => now(),
            ]);

            $cotBloqueada->update([
                'requiere_pago_tarjeta' => true,
                'estatus_pago' => 'pagado',
                'pagada_at' => $pago->pagado_en,
                'cliente_aprobada_at' => $cotBloqueada->cliente_aprobada_at ?? now(),
            ]);

            $intentoBloqueado->update([
                'estado' => 'aprobado',
                'pago_cotizacion_id' => $pago->id,
                'mercadopago_order_id' => $resultadoMp['order_id'],
                'mercadopago_payment_id' => $resultadoMp['payment_id'],
                'request_payload' => null,
                'mensaje_error' => null,
            ]);

            return $pago;
        });
    }

    /** Envía o reintenta exactamente la misma orden usando la clave idempotente persistida. */
    private function enviarOrdenMercadoPago(
        string $accessToken,
        array $payload,
        string $providerIdempotencyKey,
        string $deviceId = '',
    ): array {
        $headers = [
            'X-Idempotency-Key' => $providerIdempotencyKey,
            'Content-Type' => 'application/json',
        ];
        if ($deviceId !== '') {
            $headers['X-meli-session-id'] = $deviceId;
        }

        try {
            $response = Http::withToken($accessToken)
                ->withHeaders($headers)
                ->timeout(25)
                ->post('https://api.mercadopago.com/v1/orders', $payload);
        } catch (\Throwable $e) {
            Log::error('Error de conexión con Mercado Pago Orders API', ['error' => $e->getMessage()]);

            return [
                'estado' => 'incierto',
                'mensaje' => 'Mercado Pago no confirmó el resultado. Reintenta este mismo intento; no inicies otro pago.',
                'order_id' => null,
                'payment_id' => null,
            ];
        }

        $body = $response->json() ?? [];
        $orderStatus = strtolower((string) ($body['status'] ?? ''));
        $payment = $body['transactions']['payments'][0] ?? [];
        $paymentStatus = strtolower((string) ($payment['status'] ?? ''));
        $statusDetail = (string) ($payment['status_detail'] ?? $body['status_detail'] ?? '');
        if ($statusDetail === '') {
            foreach ($body['errors'] ?? [] as $error) {
                foreach ($error['details'] ?? [] as $errorDetail) {
                    if (!is_string($errorDetail)) {
                        continue;
                    }

                    $separator = strrpos($errorDetail, ':');
                    $candidate = trim($separator === false
                        ? $errorDetail
                        : substr($errorDetail, $separator + 1));
                    if (preg_match('/^[a-z][a-z0-9_]*$/i', $candidate)) {
                        $statusDetail = strtolower($candidate);
                        break 2;
                    }
                }
            }
        }

        $aprobado = $response->successful()
            && in_array($orderStatus, ['processed', 'paid', 'approved'], true)
            && in_array($paymentStatus, ['processed', 'approved', 'accredited'], true);

        if ($aprobado) {
            return [
                'estado' => 'aprobado',
                'mensaje' => '',
                'order_id' => isset($body['id']) ? (string) $body['id'] : null,
                'payment_id' => isset($payment['id']) ? (string) $payment['id'] : null,
            ];
        }

        $definitivamenteRechazado = $response->status() === 402
            || in_array($orderStatus, ['rejected', 'failed', 'cancelled', 'canceled'], true)
            || in_array($paymentStatus, ['rejected', 'failed', 'cancelled', 'canceled'], true)
            || str_contains($statusDetail, 'rejected');

        if ($definitivamenteRechazado) {
            Log::error('Pago rechazado por Mercado Pago Orders API', [
                'http_status' => $response->status(),
                'order_status' => $orderStatus,
                'payment_status' => $paymentStatus,
                'status_detail' => $statusDetail,
                'errors' => $body['errors'] ?? null,
                'message' => $body['message'] ?? null,
            ]);

            return [
                'estado' => 'rechazado',
                'mensaje' => $this->traducirErrorMercadoPago($body, $statusDetail),
                'order_id' => isset($body['id']) ? (string) $body['id'] : null,
                'payment_id' => isset($payment['id']) ? (string) $payment['id'] : null,
            ];
        }

        if ($response->clientError() && $response->status() !== 429) {
            Log::error('Mercado Pago rechazó la solicitud de creación de orden', [
                'http_status' => $response->status(),
                'errors' => $body['errors'] ?? null,
                'message' => $body['message'] ?? null,
            ]);

            return [
                'estado' => 'error',
                'mensaje' => in_array($response->status(), [401, 403], true)
                    ? 'Mercado Pago no autorizó esta operación. El administrador debe revisar las credenciales y permisos de la integración.'
                    : 'Mercado Pago no aceptó los datos técnicos de la solicitud. No se registró el pago; contacta al administrador para revisar la integración.',
                'order_id' => isset($body['id']) ? (string) $body['id'] : null,
                'payment_id' => isset($payment['id']) ? (string) $payment['id'] : null,
            ];
        }

        return [
            'estado' => 'incierto',
            'mensaje' => $response->status() === 429
                ? 'Mercado Pago está limitando temporalmente los intentos. Espera un momento y vuelve a intentar este mismo pago.'
                : 'Mercado Pago todavía no confirma el resultado del pago. Reintenta este mismo intento; no inicies otro pago.',
            'order_id' => isset($body['id']) ? (string) $body['id'] : null,
            'payment_id' => isset($payment['id']) ? (string) $payment['id'] : null,
        ];
    }

    private function traducirErrorMercadoPago(array $body, string $detail): string
    {
        $porDetalle = match (strtolower($detail)) {
            'cc_rejected_insufficient_amount', 'insufficient_amount' => 'Tu tarjeta no cuenta con fondos suficientes para realizar este pago.',
            'rejected_by_issuer' => 'El emisor no autorizó este intento de pago. El resultado puede variar según la operación o el comercio, aunque la tarjeta funcione en otros sitios. Consulta con tu banco; si no reconoce este rechazo, contacta a Mercado Pago.',
            'cc_rejected_bad_filled_security_code', 'bad_filled_security_code' => 'El código de seguridad (CVV) es incorrecto. Verifícalo e intenta de nuevo.',
            'cc_rejected_bad_filled_date', 'bad_filled_date' => 'La fecha de vencimiento de tu tarjeta es incorrecta.',
            'cc_rejected_bad_filled_card_number', 'bad_filled_card_number' => 'El número de tarjeta es incorrecto. Revísalo e intenta nuevamente.',
            'cc_rejected_call_for_authorize', 'call_for_authorize' => 'Tu banco requiere que autorices esta compra. Comunícate con tu banco o intenta con otra tarjeta.',
            'cc_rejected_card_disabled', 'card_disabled' => 'Tu tarjeta no está habilitada para pagos en línea. Actívala desde tu app bancaria o usa otra tarjeta.',
            'cc_rejected_high_risk', 'high_risk' => 'Mercado Pago rechazó este intento por sus controles de seguridad. No repitas el cobro de inmediato; contacta a soporte de Mercado Pago o utiliza otro medio de pago.',
            'cc_rejected_max_attempts', 'max_attempts' => 'Superaste el límite de intentos con esta tarjeta. Intenta con otra tarjeta.',
            'cc_rejected_duplicated_payment', 'duplicated_payment' => 'Ya se registró un pago igual hace unos momentos.',
            'cc_rejected_invalid_installments', 'invalid_installments' => 'Tu tarjeta no permite este esquema de pago. Intenta con otra tarjeta.',
            default => null,
        };

        if ($porDetalle !== null) {
            return $porDetalle;
        }

        $textoCrudo = strtolower(implode(' ', array_filter([
            (string) ($body['errors'][0]['code'] ?? ''),
            (string) ($body['errors'][0]['message'] ?? ''),
            (string) ($body['errors'][0]['details'][0] ?? ''),
            (string) ($body['message'] ?? ''),
            $detail,
        ])));

        if (str_contains($textoCrudo, 'high_risk')) {
            return 'Mercado Pago detuvo el pago por prevención de fraude. Intenta con otra tarjeta, desde otro dispositivo o con una tarjeta digital.';
        }
        if (str_contains($textoCrudo, 'rejected_by_issuer')) {
            return 'El emisor no autorizó este intento de pago. El resultado puede variar según la operación o el comercio, aunque la tarjeta funcione en otros sitios. Consulta con tu banco; si no reconoce este rechazo, contacta a Mercado Pago.';
        }
        if (str_contains($textoCrudo, 'insufficient_amount')) {
            return 'Tu tarjeta no cuenta con fondos suficientes para realizar este pago.';
        }
        if (str_contains($textoCrudo, 'outside the valid range') || str_contains($textoCrudo, 'amount')) {
            return 'El monto de la cotización está fuera del rango permitido para pago con tarjeta (el mínimo es de $10.00 MXN).';
        }
        if (str_contains($textoCrudo, 'token') || str_contains($textoCrudo, 'card')) {
            return 'No se pudieron validar los datos de tu tarjeta. Verifica el número, la vigencia y el CVV.';
        }
        if (str_contains($textoCrudo, 'unauthorized') || str_contains($textoCrudo, 'credential') || str_contains($textoCrudo, 'access_token')) {
            return 'El servicio de cobro con tarjeta no está disponible temporalmente. Contacta a soporte.';
        }
        if (str_contains($textoCrudo, 'payer') || str_contains($textoCrudo, 'email')) {
            return 'Verifica que el correo electrónico del cliente sea válido para procesar el pago.';
        }
        return 'No se pudo completar el pago con tu tarjeta. Verifica los datos ingresados o intenta con otra tarjeta.';
    }
}

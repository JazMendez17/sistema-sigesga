<?php

// Procesador de cobros con tarjeta para cotizaciones con soporte de Mercado Pago Checkout API (API de Orders).
// Cumple buenas prácticas PCI: con Mercado Pago el navegador tokeniza la tarjeta y el servidor
// jamás recibe PAN completo ni CVV; en modo interno solo guarda últimos 4 dígitos y referencia.

namespace App\Services;

use App\Models\Cotizacione;
use App\Models\EmpresaIntegracione;
use App\Models\PagoCotizacion;
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
            return ['activo' => false, 'public_key' => null, 'access_token' => null];
        }

        $publicKey = trim((string) ($integracion->configuracion_json['public_key'] ?? ''));
        $accessToken = trim((string) ($integracion->api_key ?? ''));

        return [
            'activo' => $publicKey !== '' && $accessToken !== '',
            'public_key' => $publicKey !== '' ? $publicKey : null,
            'access_token' => $accessToken !== '' ? $accessToken : null,
        ];
    }

    /** Valida el número de tarjeta con el algoritmo de Luhn. */
    public static function validarLuhn(string $numero): bool
    {
        $digitos = preg_replace('/\D+/', '', $numero) ?? '';
        $len = strlen($digitos);
        if ($len < 13 || $len > 19) {
            return false;
        }

        $suma = 0;
        $doble = false;

        for ($i = $len - 1; $i >= 0; $i--) {
            $n = (int) $digitos[$i];
            if ($doble) {
                $n *= 2;
                if ($n > 9) {
                    $n -= 9;
                }
            }
            $suma += $n;
            $doble = !$doble;
        }

        return $suma % 10 === 0;
    }

    /** Detecta la marca de la tarjeta a partir del BIN inicial. */
    public static function detectarMarca(string $numero): string
    {
        $d = preg_replace('/\D+/', '', $numero) ?? '';

        if (preg_match('/^4/', $d)) {
            return 'Visa';
        }
        if (preg_match('/^(5[1-5]|222[1-9]|22[3-9]\d|2[3-6]\d{2}|27[01]\d|2720)/', $d)) {
            return 'Mastercard';
        }
        if (preg_match('/^3[47]/', $d)) {
            return 'American Express';
        }
        if (preg_match('/^6(?:011|5)/', $d)) {
            return 'Discover';
        }

        return 'Tarjeta';
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

    /**
     * Procesa el cobro con tarjeta sobre una cotización pendiente.
     * Usa Mercado Pago API de Orders cuando se recibe un token de MercadoPago.js y la pasarela está activa.
     */
    public function cobrar(Cotizacione $cotizacion, array $datosTarjeta, ?int $usuarioId = null): PagoCotizacion
    {
        $titular = trim((string) ($datosTarjeta['tarjeta_titular'] ?? ''));
        $expMes = (int) ($datosTarjeta['tarjeta_exp_mes'] ?? 0);
        $expAnio = (int) ($datosTarjeta['tarjeta_exp_anio'] ?? 0);
        $mpToken = trim((string) ($datosTarjeta['mp_token'] ?? ''));

        if (!self::validarVigencia($expMes, $expAnio)) {
            throw ValidationException::withMessages([
                'tarjeta_exp_mes' => 'La tarjeta se encuentra vencida o la fecha no es válida.',
            ]);
        }

        $mpConfig = self::obtenerConfigMercadoPago((int) $cotizacion->empresa_id);
        $usarMercadoPago = $mpToken !== '' && $mpConfig['activo'] && !empty($mpConfig['access_token']);

        if ($usarMercadoPago) {
            $ultimos4 = preg_replace('/\D+/', '', (string) ($datosTarjeta['tarjeta_ultimos4'] ?? '')) ?? '';
            if (strlen($ultimos4) !== 4) {
                throw ValidationException::withMessages([
                    'tarjeta_numero' => 'No se pudieron verificar los últimos 4 dígitos de la tarjeta.',
                ]);
            }
            $methodId = strtolower(trim((string) ($datosTarjeta['mp_payment_method_id'] ?? 'visa')));
            $paymentType = strtolower(trim((string) ($datosTarjeta['mp_payment_type_id'] ?? 'credit_card'))) ?: 'credit_card';
            $installments = max(1, min(12, (int) ($datosTarjeta['mp_installments'] ?? 1)));
            $marca = self::marcaDesdeMetodoMp($methodId, $datosTarjeta['tarjeta_marca'] ?? null);
        } else {
            $digitos = preg_replace('/\D+/', '', (string) ($datosTarjeta['tarjeta_numero'] ?? '')) ?? '';
            $cvv = preg_replace('/\D+/', '', (string) ($datosTarjeta['tarjeta_cvv'] ?? '')) ?? '';

            if (!self::validarLuhn($digitos)) {
                throw ValidationException::withMessages([
                    'tarjeta_numero' => 'El número de tarjeta no es válido.',
                ]);
            }

            $marca = self::detectarMarca($digitos);
            $ultimos4 = substr($digitos, -4);
            $cvvEsperado = $marca === 'American Express' ? 4 : 3;
            if (strlen($cvv) !== $cvvEsperado) {
                throw ValidationException::withMessages([
                    'tarjeta_cvv' => "El código de seguridad (CVV) debe tener {$cvvEsperado} dígitos.",
                ]);
            }
            $methodId = null;
            $paymentType = null;
            $installments = 1;
            unset($digitos, $cvv);
        }

        // Limpiar cualquier dato sensible en memoria de inmediato
        unset($datosTarjeta['tarjeta_numero'], $datosTarjeta['tarjeta_cvv']);

        return DB::transaction(function () use (
            $cotizacion,
            $titular,
            $marca,
            $ultimos4,
            $expMes,
            $expAnio,
            $usuarioId,
            $usarMercadoPago,
            $mpConfig,
            $mpToken,
            $methodId,
            $paymentType,
            $installments
        ) {
            $cotBloqueada = Cotizacione::with(['cliente.usuario'])
                ->where('empresa_id', $cotizacion->empresa_id)
                ->lockForUpdate()
                ->findOrFail($cotizacion->id);

            if ($cotBloqueada->estatus === 'rechazado') {
                throw ValidationException::withMessages([
                    'tarjeta' => 'No se puede procesar un pago para una cotización rechazada.',
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

            $referencia = 'PAG-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6));

            if ($usarMercadoPago) {
                $payerEmail = $cotBloqueada->cliente?->email
                    ?: $cotBloqueada->cliente?->usuario?->email
                    ?: Usuario::whereKey($usuarioId)->value('email')
                    ?: 'pagos@sigesga.com';

                $resultadoMp = $this->cobrarConMercadoPagoOrders(
                    accessToken: $mpConfig['access_token'],
                    referencia: $referencia,
                    monto: $monto,
                    payerEmail: $payerEmail,
                    token: $mpToken,
                    paymentMethodId: $methodId,
                    paymentTypeId: $paymentType,
                    installments: $installments,
                );

                $pasarela = 'mercadopago_orders';
                $transaccionId = $resultadoMp['transaccion_id'];
            } else {
                $pasarela = 'pasarela_interna';
                $transaccionId = 'TXN-' . strtoupper(Str::random(12));
            }

            $pago = PagoCotizacion::create([
                'empresa_id' => $cotBloqueada->empresa_id,
                'cotizacion_id' => $cotBloqueada->id,
                'cliente_id' => $cotBloqueada->cliente_id,
                'usuario_id' => $usuarioId,
                'referencia' => $referencia,
                'metodo' => 'tarjeta',
                'pasarela' => $pasarela,
                'pasarela_transaccion_id' => $transaccionId,
                'monto' => $monto,
                'moneda' => 'MXN',
                'tarjeta_titular' => $titular,
                'tarjeta_marca' => $marca,
                'tarjeta_ultimos4' => $ultimos4,
                'tarjeta_exp_mes' => $expMes,
                'tarjeta_exp_anio' => $expAnio,
                'estatus' => 'pagado',
                'pagado_en' => now(),
            ]);

            $cotBloqueada->update([
                'requiere_pago_tarjeta' => true,
                'estatus_pago' => 'pagado',
                'pagada_at' => $pago->pagado_en,
                'cliente_aprobada_at' => $cotBloqueada->cliente_aprobada_at ?? now(),
            ]);

            return $pago;
        });
    }

    /** Envía la orden de cobro a Mercado Pago Checkout API (API de Orders: POST /v1/orders). */
    private function cobrarConMercadoPagoOrders(
        string $accessToken,
        string $referencia,
        float $monto,
        string $payerEmail,
        string $token,
        string $paymentMethodId,
        string $paymentTypeId,
        int $installments
    ): array {
        $montoStr = number_format($monto, 2, '.', '');

        $payload = [
            'type' => 'online',
            'processing_mode' => 'automatic',
            'external_reference' => $referencia,
            'total_amount' => $montoStr,
            'payer' => [
                'email' => $payerEmail,
            ],
            'transactions' => [
                'payments' => [
                    [
                        'amount' => $montoStr,
                        'payment_method' => [
                            'id' => $paymentMethodId,
                            'type' => $paymentTypeId,
                            'token' => $token,
                            'installments' => $installments,
                        ],
                    ],
                ],
            ],
        ];

        try {
            $response = Http::withToken($accessToken)
                ->withHeaders([
                    'X-Idempotency-Key' => (string) Str::uuid(),
                    'Content-Type' => 'application/json',
                ])
                ->timeout(25)
                ->post('https://api.mercadopago.com/v1/orders', $payload);
        } catch (\Throwable $e) {
            Log::error('Error de conexión con Mercado Pago Orders API', ['error' => $e->getMessage()]);
            throw ValidationException::withMessages([
                'tarjeta' => 'No se pudo conectar con Mercado Pago en este momento. Intenta de nuevo.',
            ]);
        }

        $body = $response->json() ?? [];
        $orderStatus = strtolower((string) ($body['status'] ?? ''));
        $payment = $body['transactions']['payments'][0] ?? [];
        $paymentStatus = strtolower((string) ($payment['status'] ?? ''));
        $statusDetail = (string) ($payment['status_detail'] ?? $body['status_detail'] ?? '');

        $aprobado = $response->successful()
            && in_array($orderStatus, ['processed', 'paid', 'approved'], true)
            && ($paymentStatus === '' || in_array($paymentStatus, ['processed', 'approved', 'accredited'], true));

        if (!$aprobado) {
            Log::error('Pago rechazado por Mercado Pago Orders API', [
                'http_status' => $response->status(),
                'order_status' => $orderStatus,
                'payment_status' => $paymentStatus,
                'status_detail' => $statusDetail,
                'errors' => $body['errors'] ?? null,
                'message' => $body['message'] ?? null,
            ]);

            throw ValidationException::withMessages([
                'tarjeta' => $this->traducirErrorMercadoPago($body, $statusDetail),
            ]);
        }

        $transaccionId = (string) ($payment['id'] ?? $body['id'] ?? ('MP-' . strtoupper(Str::random(10))));

        return [
            'transaccion_id' => $transaccionId,
            'order_id' => $body['id'] ?? null,
        ];
    }

    private function traducirErrorMercadoPago(array $body, string $detail): string
    {
        $porDetalle = match (strtolower($detail)) {
            'cc_rejected_insufficient_amount', 'insufficient_amount' => 'Tu tarjeta no cuenta con fondos suficientes para realizar este pago.',
            'cc_rejected_bad_filled_security_code', 'bad_filled_security_code' => 'El código de seguridad (CVV) es incorrecto. Verifícalo e intenta de nuevo.',
            'cc_rejected_bad_filled_date', 'bad_filled_date' => 'La fecha de vencimiento de tu tarjeta es incorrecta.',
            'cc_rejected_bad_filled_card_number', 'bad_filled_card_number' => 'El número de tarjeta es incorrecto. Revísalo e intenta nuevamente.',
            'cc_rejected_call_for_authorize', 'call_for_authorize' => 'Tu banco requiere que autorices esta compra. Comunícate con tu banco o intenta con otra tarjeta.',
            'cc_rejected_card_disabled', 'card_disabled' => 'Tu tarjeta no está habilitada para pagos en línea. Actívala desde tu app bancaria o usa otra tarjeta.',
            'cc_rejected_high_risk', 'high_risk' => 'El pago fue rechazado por validación de seguridad. Intenta con otra tarjeta.',
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
        ])));

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

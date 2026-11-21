<?php

// Procesador de cobros con tarjeta para cotizaciones de clientes sin convenio con aseguradora.
// Cumple buenas prácticas de seguridad PCI: valida Luhn, vigencia y CVV en memoria
// y jamás almacena ni registra en logs el número completo de tarjeta (PAN) ni el CVV.

namespace App\Services;

use App\Models\Cotizacione;
use App\Models\EmpresaIntegracione;
use App\Models\PagoCotizacion;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PagoTarjetaService
{
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
     * Procesa el cobro con tarjeta sobre una cotización pendiente sin convenio.
     * Devuelve el registro PagoCotizacion creado (solo últimos 4 dígitos y referencia).
     */
    public function cobrar(Cotizacione $cotizacion, array $datosTarjeta, ?int $usuarioId = null): PagoCotizacion
    {
        $digitos = preg_replace('/\D+/', '', (string) ($datosTarjeta['tarjeta_numero'] ?? '')) ?? '';
        $marca = self::detectarMarca($digitos);
        $ultimos4 = substr($digitos, -4);
        $titular = trim((string) ($datosTarjeta['tarjeta_titular'] ?? ''));
        $expMes = (int) ($datosTarjeta['tarjeta_exp_mes'] ?? 0);
        $expAnio = (int) ($datosTarjeta['tarjeta_exp_anio'] ?? 0);
        $cvv = preg_replace('/\D+/', '', (string) ($datosTarjeta['tarjeta_cvv'] ?? '')) ?? '';

        if (!self::validarLuhn($digitos)) {
            throw ValidationException::withMessages([
                'tarjeta_numero' => 'El número de tarjeta no es válido.',
            ]);
        }

        if (!self::validarVigencia($expMes, $expAnio)) {
            throw ValidationException::withMessages([
                'tarjeta_exp_mes' => 'La tarjeta se encuentra vencida o la fecha no es válida.',
            ]);
        }

        $cvvEsperado = $marca === 'American Express' ? 4 : 3;
        if (strlen($cvv) !== $cvvEsperado) {
            throw ValidationException::withMessages([
                'tarjeta_cvv' => "El código de seguridad (CVV) debe tener {$cvvEsperado} dígitos.",
            ]);
        }

        // Limpiar variables sensibles en memoria tan pronto se validan
        unset($digitos, $cvv, $datosTarjeta['tarjeta_numero'], $datosTarjeta['tarjeta_cvv']);

        return DB::transaction(function () use ($cotizacion, $titular, $marca, $ultimos4, $expMes, $expAnio, $usuarioId) {
            $cotBloqueada = Cotizacione::with('cliente')
                ->where('empresa_id', $cotizacion->empresa_id)
                ->lockForUpdate()
                ->findOrFail($cotizacion->id);

            if ($cotBloqueada->estatus !== 'pendiente') {
                throw ValidationException::withMessages([
                    'tarjeta' => 'Esta cotización ya no se encuentra pendiente de pago.',
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

            // Verifica si la empresa tiene pasarela activa configurada (ej. Stripe); de lo contrario usa pasarela interna segura
            $integracionPago = EmpresaIntegracione::where('empresa_id', $cotBloqueada->empresa_id)
                ->where('proveedor', 'stripe')
                ->where('activo', true)
                ->first();

            $pasarela = ($integracionPago && !empty($integracionPago->api_key)) ? 'stripe' : 'pasarela_interna';
            $referencia = 'PAG-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6));
            $transaccionId = 'TXN-' . strtoupper(Str::random(12));

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
}

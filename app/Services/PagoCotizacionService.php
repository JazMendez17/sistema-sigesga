<?php

namespace App\Services;

use App\Mail\PagoConfirmadoMail;
use App\Models\Cotizacione;
use App\Models\Notificacione;
use App\Models\PagoCotizacion;
use App\Models\PagoCotizacionIntento;
use App\Models\Usuario;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use RuntimeException;
use Throwable;

class PagoCotizacionService
{
    public function confirmar(PagoCotizacionIntento $intento, array $pagoMp): ?PagoCotizacion
    {
        $snapshot = is_array($intento->request_payload) ? $intento->request_payload : [];
        $montoEsperado = (float) ($snapshot['monto'] ?? 0);
        $monedaEsperada = (string) ($snapshot['moneda'] ?? 'MXN');

        if (($pagoMp['external_reference'] ?? null) !== $intento->referencia
            || ($pagoMp['currency_id'] ?? null) !== $monedaEsperada
            || round((float) ($pagoMp['transaction_amount'] ?? -1), 2) !== round($montoEsperado, 2)) {
            throw new RuntimeException('El pago de Mercado Pago no coincide con el intento registrado.');
        }

        $pago = DB::transaction(function () use (
            $intento,
            $pagoMp,
            $snapshot,
            $montoEsperado,
            $monedaEsperada
        ): ?PagoCotizacion {
            $intentoBloqueado = PagoCotizacionIntento::whereKey($intento->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($intentoBloqueado->pago_cotizacion_id) {
                $pagoExistente = PagoCotizacion::find($intentoBloqueado->pago_cotizacion_id);
                if ($pagoExistente) {
                    return $pagoExistente;
                }
            }

            $cotizacion = Cotizacione::where('empresa_id', $intentoBloqueado->empresa_id)
                ->lockForUpdate()
                ->findOrFail($intentoBloqueado->cotizacion_id);

            $pagoCotizacion = PagoCotizacion::where('cotizacion_id', $cotizacion->id)
                ->where('estatus', 'pagado')
                ->first();
            if ($pagoCotizacion) {
                if ($pagoCotizacion->referencia === $intentoBloqueado->referencia) {
                    $intentoBloqueado->update([
                        'estado' => 'aprobado',
                        'pago_cotizacion_id' => $pagoCotizacion->id,
                        'mercadopago_payment_id' => (string) ($pagoMp['id'] ?? $pagoCotizacion->pasarela_transaccion_id),
                    ]);

                    return $pagoCotizacion;
                }

                $intentoBloqueado->update([
                    'estado' => 'error',
                    'mercadopago_payment_id' => (string) ($pagoMp['id'] ?? ''),
                    'mensaje_error' => 'La cotización ya tiene otro pago confirmado. Mercado Pago reportó un segundo pago que requiere revisión.',
                    'ultimo_evento_en' => now(),
                ]);

                return null;
            }

            $pagoExistente = PagoCotizacion::where('referencia', $intentoBloqueado->referencia)->first();
            if ($pagoExistente) {
                $intentoBloqueado->update([
                    'estado' => 'aprobado',
                    'pago_cotizacion_id' => $pagoExistente->id,
                    'mercadopago_payment_id' => (string) ($pagoMp['id'] ?? $pagoExistente->pasarela_transaccion_id),
                ]);

                return $pagoExistente;
            }

            $tarjeta = is_array($pagoMp['card'] ?? null) ? $pagoMp['card'] : [];
            $ultimos4 = preg_replace('/\D/', '', (string) ($tarjeta['last_four_digits'] ?? ''));
            $ultimos4 = strlen($ultimos4) === 4 ? $ultimos4 : null;
            $metodoId = (string) ($pagoMp['payment_method']['id'] ?? '');
            $fechaPago = $this->fechaAprobacion($pagoMp);

            $pago = PagoCotizacion::create([
                'empresa_id' => $cotizacion->empresa_id,
                'cotizacion_id' => $cotizacion->id,
                'cliente_id' => $cotizacion->cliente_id,
                'usuario_id' => $snapshot['usuario_id'] ?? null,
                'referencia' => $intentoBloqueado->referencia,
                'metodo' => $this->normalizarMetodo((string) ($pagoMp['payment_type_id'] ?? '')),
                'pasarela' => 'mercadopago_pro',
                'pasarela_transaccion_id' => (string) ($pagoMp['id'] ?? ''),
                'monto' => $montoEsperado,
                'moneda' => $monedaEsperada,
                'tarjeta_titular' => $this->textoOpcional($tarjeta['cardholder']['name'] ?? null, 150),
                'tarjeta_marca' => $ultimos4 ? $this->marca($metodoId) : null,
                'tarjeta_ultimos4' => $ultimos4,
                'tarjeta_exp_mes' => $this->enteroOpcional($tarjeta['expiration_month'] ?? null),
                'tarjeta_exp_anio' => $this->enteroOpcional($tarjeta['expiration_year'] ?? null),
                'estatus' => 'pagado',
                'pagado_en' => $fechaPago,
            ]);

            $cotizacion->update([
                'requiere_pago_tarjeta' => true,
                'estatus_pago' => 'pagado',
                'pagada_at' => $fechaPago,
                'cliente_aprobada_at' => $cotizacion->cliente_aprobada_at ?? $fechaPago,
            ]);

            $intentoBloqueado->update([
                'estado' => 'aprobado',
                'pago_cotizacion_id' => $pago->id,
                'mercadopago_payment_id' => (string) ($pagoMp['id'] ?? ''),
                'mensaje_error' => null,
            ]);

            return $pago;
        });

        if ($pago === null) {
            Log::error('Mercado Pago reportó un segundo pago aprobado para una cotización ya pagada', [
                'intento_id' => $intento->id,
                'cotizacion_id' => $intento->cotizacion_id,
                'payment_id' => $pagoMp['id'] ?? null,
            ]);

            return null;
        }

        if ($pago->wasRecentlyCreated) {
            $this->notificarPagoConfirmado($pago);
        }

        return $pago;
    }

    public function registrarNoAprobado(PagoCotizacionIntento $intento, array $pagoMp): string
    {
        $estadoMp = (string) ($pagoMp['status'] ?? '');
        $estado = in_array($estadoMp, ['rejected', 'cancelled', 'refunded', 'charged_back'], true)
            ? 'rechazado'
            : (in_array($estadoMp, ['pending', 'in_process', 'action_required'], true)
                ? 'pendiente'
                : 'error');

        DB::transaction(function () use ($intento, $pagoMp, $estado): void {
            $intentoBloqueado = PagoCotizacionIntento::whereKey($intento->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($intentoBloqueado->estado === 'aprobado') {
                return;
            }

            $intentoBloqueado->update([
                'estado' => $estado,
                'mercadopago_payment_id' => (string) ($pagoMp['id'] ?? ''),
                'mensaje_error' => $estado === 'rechazado'
                    ? 'Mercado Pago no aprobó el pago.'
                    : null,
                'ultimo_evento_en' => now(),
            ]);

            if ($estado !== 'rechazado' || $intentoBloqueado->rechazo_notificado_en) {
                return;
            }

            $cotizacion = Cotizacione::find($intentoBloqueado->cotizacion_id);
            if (!$cotizacion?->cliente?->usuario_id) {
                $intentoBloqueado->update(['rechazo_notificado_en' => now()]);
                return;
            }

            Notificacione::create([
                'empresa_id' => $cotizacion->empresa_id,
                'usuario_id' => $cotizacion->cliente->usuario_id,
                'mensaje' => "Mercado Pago no aprobó el pago de la cotización {$cotizacion->folio}. Puedes intentar con otro método de pago.",
                'canal' => 'sistema_push',
                'estado' => 'pendiente',
            ]);

            $intentoBloqueado->update(['rechazo_notificado_en' => now()]);
        });

        return $estado;
    }

    public function marcarInconsistencia(PagoCotizacionIntento $intento, string $motivo): void
    {
        $intento->update([
            'estado' => 'error',
            'mensaje_error' => $motivo,
            'ultimo_evento_en' => now(),
        ]);

        Log::warning('Pago de Mercado Pago inconsistente con SIGESGA', [
            'intento_id' => $intento->id,
            'motivo' => $motivo,
        ]);
    }

    private function notificarPagoConfirmado(PagoCotizacion $pago): void
    {
        $pago->loadMissing(['cotizacion.cliente.usuario', 'cotizacion.tipoServicio']);
        $cotizacion = $pago->cotizacion;
        if (!$cotizacion) {
            return;
        }

        $montoFmt = number_format((float) $pago->monto, 2);
        $rastreo = $pago->pasarela_transaccion_id ?: $pago->referencia;
        $metodo = $pago->descripcionMetodo();

        $usuariosGestion = Usuario::where(function ($query) use ($cotizacion) {
            $query->where('rol', 'superadmin')
                ->orWhere(function ($subquery) use ($cotizacion) {
                    $subquery->where('empresa_id', $cotizacion->empresa_id)
                        ->whereIn('rol', ['admin', 'cotizador']);
                });

            if ($cotizacion->usuario_creador_id) {
                $query->orWhere('id', $cotizacion->usuario_creador_id);
            }
        })->get()->unique('id');

        foreach ($usuariosGestion as $destino) {
            $this->crearNotificacion(
                $destino->rol === 'superadmin'
                    ? (int) ($destino->empresa_id ?: $cotizacion->empresa_id)
                    : (int) $cotizacion->empresa_id,
                $destino->id,
                "Pago confirmado (\${$montoFmt} MXN · {$metodo} · Rastreo: {$rastreo}) para la cotización {$cotizacion->folio}."
            );

            if (!empty($destino->email)) {
                try {
                    Mail::to($destino->email)->send(new PagoConfirmadoMail($cotizacion, $pago, paraEquipo: true));
                } catch (Throwable $exception) {
                    Log::error('Error al enviar correo de pago al equipo', [
                        'pago_id' => $pago->id,
                        'usuario_id' => $destino->id,
                        'error' => $exception->getMessage(),
                    ]);
                }
            }
        }

        if ($cotizacion->cliente?->usuario_id) {
            $this->crearNotificacion(
                $cotizacion->empresa_id,
                $cotizacion->cliente->usuario_id,
                "Pago realizado con éxito por \${$montoFmt} MXN ({$metodo} · Rastreo: {$rastreo}) para la cotización {$cotizacion->folio}."
            );
        }

        $correoCliente = $cotizacion->cliente?->email ?: $cotizacion->cliente?->usuario?->email;
        if ($correoCliente) {
            try {
                Mail::to($correoCliente)->send(new PagoConfirmadoMail($cotizacion, $pago, paraEquipo: false));
            } catch (Throwable $exception) {
                Log::error('Error al enviar correo de confirmación de pago al cliente', [
                    'pago_id' => $pago->id,
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        $telefonoCliente = $cotizacion->cliente?->telefono ?: $cotizacion->cliente?->usuario?->telefono;
        if ($telefonoCliente) {
            try {
                app(WhatsAppService::class)->enviarTexto(
                    (int) $cotizacion->empresa_id,
                    $telefonoCliente,
                    "SIGESGA: Confirmamos tu pago por \${$montoFmt} MXN ({$metodo}) para la cotización {$cotizacion->folio}. Clave de rastreo: {$rastreo}."
                );
            } catch (Throwable $exception) {
                Log::error('Error al enviar WhatsApp de confirmación de pago', [
                    'pago_id' => $pago->id,
                    'error' => $exception->getMessage(),
                ]);
            }
        }
    }

    private function crearNotificacion(int $empresaId, int $usuarioId, string $mensaje): void
    {
        Notificacione::create([
            'empresa_id' => $empresaId,
            'usuario_id' => $usuarioId,
            'mensaje' => $mensaje,
            'canal' => 'sistema_push',
            'estado' => 'pendiente',
        ]);
    }

    private function fechaAprobacion(array $pagoMp): Carbon
    {
        $fecha = $pagoMp['date_approved'] ?? $pagoMp['date_created'] ?? null;
        if (is_string($fecha) && $fecha !== '') {
            try {
                return Carbon::parse($fecha);
            } catch (\Exception) {
            }
        }

        return now();
    }

    private function normalizarMetodo(string $metodo): string
    {
        return $metodo !== '' ? $metodo : 'mercadopago';
    }

    private function marca(string $methodId): string
    {
        return match (strtolower($methodId)) {
            'visa', 'debvisa' => 'Visa',
            'master', 'debmaster' => 'Mastercard',
            'amex' => 'American Express',
            'carnet' => 'Carnet',
            default => 'Tarjeta',
        };
    }

    private function textoOpcional(mixed $valor, int $limite): ?string
    {
        $texto = trim((string) $valor);

        return $texto !== '' ? mb_substr($texto, 0, $limite) : null;
    }

    private function enteroOpcional(mixed $valor): ?int
    {
        return is_numeric($valor) ? (int) $valor : null;
    }
}

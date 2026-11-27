<?php

namespace App\Http\Controllers;

use App\Models\PagoCotizacionIntento;
use App\Services\MercadoPagoService;
use App\Services\PagoCotizacionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class MercadoPagoWebhookController extends Controller
{
    public function __invoke(
        Request $request,
        string $referencia,
        MercadoPagoService $mercadoPago,
        PagoCotizacionService $pagos
    ) {
        $intento = PagoCotizacionIntento::where('referencia', $referencia)->first();
        if (!$intento) {
            return response('', 404);
        }

        $config = MercadoPagoService::obtenerConfigMercadoPago((int) $intento->empresa_id);
        $secret = (string) ($config['webhook_secret'] ?? '');
        if ($secret === '') {
            return response('', 503);
        }

        $dataId = trim((string) ($request->query->get('data.id')
            ?? $request->query->get('data_id', '')));
        $firmaValida = $mercadoPago->firmaWebhookValida(
            (string) $request->header('x-signature', ''),
            (string) $request->header('x-request-id', ''),
            $dataId,
            $secret
        );

        if (!$firmaValida) {
            return response('', 401);
        }

        $tipo = (string) ($request->query('type') ?: $request->input('type'));
        if ($tipo !== '' && $tipo !== 'payment') {
            return response('', 200);
        }

        if ($dataId === '' || !ctype_digit($dataId)) {
            return response('', 400);
        }

        try {
            $pagoMp = $mercadoPago->consultarPago($intento, $dataId);

            if (!$mercadoPago->pagoCorrespondeAlIntento($intento, $pagoMp)) {
                $pagos->marcarInconsistencia(
                    $intento,
                    'La referencia, el monto, la moneda o el entorno del pago no coinciden.'
                );

                return response('', 200);
            }

            if (($pagoMp['status'] ?? null) === 'approved') {
                $pagos->confirmar($intento, $pagoMp);
            } else {
                $pagos->registrarNoAprobado($intento, $pagoMp);
            }

            return response('', 200);
        } catch (Throwable $exception) {
            Log::error('Error al procesar webhook de Mercado Pago', [
                'intento_id' => $intento->id,
                'error' => $exception->getMessage(),
            ]);

            return response('', 500);
        }
    }
}

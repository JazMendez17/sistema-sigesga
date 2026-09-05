<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CodigoPostalController extends Controller
{
    public function buscar(string $cp): JsonResponse
    {
        $cp = trim($cp);

        if (!preg_match('/^\d{5}$/', $cp)) {
            return response()->json([
                'success' => false,
                'message' => 'Código postal inválido. Debe ser 5 dígitos.',
            ], 400);
        }

        $apiKey = config('services.postalia.api_key');
        if (!$apiKey) {
            Log::error('POSTALIA_API_KEY no está configurada.');
            return response()->json(['message' => 'El servicio de códigos postales no está configurado.'], 503);
        }

        try {
            $response = Http::withToken($apiKey)
                ->timeout(8)
                ->acceptJson()
                ->get(rtrim(config('services.postalia.base_url'), '/') . "/mx/cp/{$cp}");

            if ($response->failed()) {
                if ($response->status() === 404) {
                    return response()->json(['message' => 'Código postal no encontrado.'], 404);
                }

                Log::warning('postali.app API error', [
                    'cp' => $cp,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return response()->json(['message' => 'Error al consultar el servicio de códigos postales.'], 502);
            }

            $data = $response->json('data', $response->json());
            $estado = data_get($data, 'estado');
            $municipio = data_get($data, 'municipio') ?? data_get($data, 'municipio_alcaldia');
            $colonias = data_get($data, 'colonias', data_get($data, 'asentamientos', []));
            $colonias = collect(is_array($colonias) ? $colonias : [])
                ->map(fn ($colonia) => is_array($colonia) ? ($colonia['nombre'] ?? $colonia['name'] ?? '') : $colonia)
                ->filter(fn ($colonia) => is_string($colonia) && $colonia !== '')
                ->unique()
                ->values()
                ->all();

            if (!$estado || !$municipio) {
                return response()->json(['message' => 'Respuesta incompleta del servicio de códigos postales.'], 502);
            }

            return response()->json([
                'estado' => $estado,
                'municipio' => $municipio,
                'colonias' => $colonias,
            ]);

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('postali.app connection error', ['cp' => $cp, 'error' => $e->getMessage()]);
            return response()->json(['message' => 'No se pudo conectar con el servicio de códigos postales.'], 503);
        } catch (\Throwable $e) {
            Log::error('postali.app unexpected error', ['cp' => $cp, 'error' => $e->getMessage()]);
            return response()->json(['message' => 'Error inesperado al consultar código postal.'], 500);
        }
    }
}
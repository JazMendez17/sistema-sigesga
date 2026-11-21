<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CodigoPostalController extends Controller
{
    public function buscar(string $cp): JsonResponse
    {
        $cp = trim($cp);

        if (!preg_match('/^\d{5}$/', $cp)) {
            return response()->json([
                'message' => 'Código postal inválido. Debe ser de 5 dígitos.',
            ], 400);
        }

        $apiKey = config('services.postalia.api_key');
        if (!$apiKey) {
            Log::error('POSTALIA_API_KEY no está configurada.');

            return response()->json(['message' => 'El servicio de códigos postales no está configurado.'], 503);
        }

        $baseUrl = rtrim((string) (config('services.postalia.base_url') ?: 'https://postali.app/api/v1'), '/');
        $caBundle = base_path('certs/cacert.pem');
        $verify = file_exists($caBundle) ? $caBundle : true;

        $cached = Cache::get("cp:{$cp}");
        if (is_array($cached)) {
            return response()->json($cached);
        }

        try {
            $response = Http::withToken($apiKey)
                ->withOptions(['verify' => $verify])
                ->timeout(8)
                ->acceptJson()
                ->get("{$baseUrl}/mx/cp/{$cp}");

            if ($response->failed()) {
                if ($response->status() === 404) {
                    return response()->json(['message' => 'Código postal no encontrado.'], 404);
                }

                Log::warning('postali.app API error', [
                    'cp' => $cp,
                    'status' => $response->status(),
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

            $payload = [
                'estado' => $estado,
                'municipio' => $municipio,
                'colonias' => $colonias,
            ];

            Cache::put("cp:{$cp}", $payload, now()->addHours(24));

            return response()->json($payload);
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('postali.app connection error', ['cp' => $cp, 'error' => $e->getMessage()]);

            return response()->json(['message' => 'No se pudo conectar con el servicio de códigos postales.'], 503);
        } catch (\Throwable $e) {
            Log::error('postali.app unexpected error', ['cp' => $cp, 'error' => $e->getMessage()]);

            return response()->json(['message' => 'Error inesperado al consultar código postal.'], 500);
        }
    }
}

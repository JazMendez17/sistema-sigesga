<?php

// Servicio para Google Maps Platform — Routes API v2.
// El frontend NUNCA toca esta API directamente: solo el backend conoce la llave de servidor.

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class GoogleMapsService
{
    private const ENDPOINT = 'https://routes.googleapis.com/directions/v2:computeRoutes';

    private const FIELD_MASK = 'routes.distanceMeters,'
        . 'routes.duration,'
        . 'routes.polyline.encodedPolyline,'
        . 'routes.travelAdvisory.tollInfo';

    private string $apiKey;
    private string $country;
    private string $language;
    private string $emissionType;

    public function __construct()
    {
        $this->apiKey = (string) config('services.google.api_key');
        $this->country = (string) config('services.google.country', 'MX');
        $this->language = (string) config('services.google.language', 'es');
        $this->emissionType = (string) config('services.google.emission_type', 'GASOLINE');
    }

    public function rutas(array $datos): array
    {
        if ($this->apiKey === '') {
            throw new RuntimeException('La API Key de Google Maps no está configurada en el servidor.');
        }

        $payload = [
            'origin' => ['address' => $datos['origen']],
            'destination' => ['address' => $datos['destino']],
            'travelMode' => 'DRIVE',
            'routingPreference' => 'TRAFFIC_AWARE',
            'computeAlternativeRoutes' => true,
            'units' => 'METRIC',
            'languageCode' => $this->language,
            'regionCode' => $this->country,
            'extraComputations' => ['TOLLS'],
            'routeModifiers' => [
                'vehicleInfo' => ['emissionType' => $this->emissionType],
            ],
        ];

        $rutasConPeaje = $this->consultarGoogle($payload);
        $rutasLibres = [];

        try {
            $rutasLibres = $this->consultarGoogle(array_replace_recursive($payload, [
                'routeModifiers' => [
                    'avoidTolls' => true,
                    'vehicleInfo' => ['emissionType' => $this->emissionType],
                ],
            ]));
        } catch (\Throwable $e) {
            Log::warning('Google Routes avoidTolls falló, continuando con rutas principales', ['error' => $e->getMessage()]);
        }

        $rutas = $this->normalizarRutas([$rutasConPeaje, $rutasLibres]);

        if ($rutas === []) {
            throw new RuntimeException('Google no devolvió rutas viables entre las direcciones indicadas.');
        }

        return ['rutas' => $rutas];
    }

    private function consultarGoogle(array $payload): array
    {
        $caBundle = base_path('certs/cacert.pem');
        $verify = file_exists($caBundle) ? $caBundle : true;

        $respuesta = Http::withOptions(['verify' => $verify])
            ->timeout(15)
            ->withHeaders([
                'X-Goog-Api-Key' => $this->apiKey,
                'X-Goog-FieldMask' => self::FIELD_MASK,
            ])->post(self::ENDPOINT, $payload);

        if ($respuesta->failed()) {
            $mensaje = $respuesta->json('error.message') ?? 'Error al comunicarse con Google Routes API.';
            $mensaje = str_replace('google.invalid_argument', '', $mensaje);

            throw new RuntimeException(
                trim(ucfirst($mensaje)) . ' (HTTP ' . $respuesta->status() . ')'
            );
        }

        return is_array($respuesta->json()) ? $respuesta->json() : [];
    }

    private function normalizarRutas(array $respuestas): array
    {
        $rutas = [];

        foreach ($respuestas as $respuesta) {
            foreach (($respuesta['routes'] ?? []) as $ruta) {
                $rutas[] = $this->normalizarRuta($ruta, count($rutas));
            }
        }

        return collect($rutas)
            ->unique(fn ($ruta) => $ruta['polyline'] ?: $ruta['distancia_km'] . '|' . $ruta['costo_peaje'])
            ->values()
            ->map(function (array $ruta, int $i): array {
                $ruta['indice'] = $i;

                return $ruta;
            })
            ->all();
    }

    private function normalizarRuta(array $ruta, int $indice): array
    {
        $peajes = $this->resumirPeajes($ruta['travelAdvisory']['tollInfo']['estimatedPrice'] ?? []);
        $polyline = $ruta['polyline']['encodedPolyline'] ?? null;
        $duracionSegundos = $this->duracionASegundos($ruta['duration'] ?? '0s');

        return [
            'indice' => $indice,
            'distancia_km' => round(($ruta['distanceMeters'] ?? 0) / 1000, 2),
            'duracion_texto' => $this->formatearDuracion($duracionSegundos),
            'duracion_segundos' => $duracionSegundos,
            'coordenadas' => $polyline ? $this->decodificarPolyline($polyline) : [],
            'polyline' => $polyline,
            'costo_peaje' => $peajes['total'],
            'moneda_peaje' => $peajes['moneda'],
        ];
    }

    private function resumirPeajes(array $precios): array
    {
        $sumas = [];
        $primeraMoneda = 'MXN';

        foreach ($precios as $precio) {
            $moneda = $precio['currencyCode'] ?? $primeraMoneda;
            $monto = (int) ($precio['units'] ?? 0) + ((int) ($precio['nanos'] ?? 0) / 1_000_000_000);
            $sumas[$moneda] = ($sumas[$moneda] ?? 0.0) + $monto;
        }

        unset($sumas['XXX']);

        if ($sumas === []) {
            return ['total' => 0.0, 'moneda' => $this->country === 'MX' ? 'MXN' : $primeraMoneda];
        }

        $monedaLocal = $this->country === 'MX' ? 'MXN' : $primeraMoneda;

        return isset($sumas[$monedaLocal])
            ? ['total' => round($sumas[$monedaLocal], 2), 'moneda' => $monedaLocal]
            : ['total' => round(array_values($sumas)[0], 2), 'moneda' => array_key_first($sumas)];
    }

    private function duracionASegundos(string $duraccion): int
    {
        $segundos = 0.0;
        if (preg_match('/^(\d+(?:\.\d+)?)s$/', $duraccion, $coincidencias)) {
            $segundos = (float) $coincidencias[1];
        }

        return (int) round($segundos);
    }

    private function formatearDuracion(int $segundos): string
    {
        $horas = intdiv($segundos, 3600);
        $minutos = intdiv($segundos % 3600, 60);

        if ($horas > 0) {
            return $minutos > 0 ? "{$horas} h {$minutos} min" : "{$horas} h";
        }

        return $minutos > 0 ? "{$minutos} min" : "{$segundos} s";
    }

    private function decodificarPolyline(string $polyline): array
    {
        $puntos = [];
        $indice = 0;
        $len = strlen($polyline);
        $lat = 0;
        $lng = 0;

        while ($indice < $len) {
            $b = 0;
            $desplazamiento = 0;
            do {
                if ($indice >= $len) {
                    return $puntos;
                }
                $byte = ord($polyline[$indice++]) - 63;
                $b |= ($byte & 0x1F) << $desplazamiento;
                $desplazamiento += 5;
            } while ($byte >= 0x20);

            $dLat = ($b & 1) ? ~($b >> 1) : ($b >> 1);
            $lat += $dLat;

            $b = 0;
            $desplazamiento = 0;
            do {
                if ($indice >= $len) {
                    return $puntos;
                }
                $byte = ord($polyline[$indice++]) - 63;
                $b |= ($byte & 0x1F) << $desplazamiento;
                $desplazamiento += 5;
            } while ($byte >= 0x20);

            $dLng = ($b & 1) ? ~($b >> 1) : ($b >> 1);
            $lng += $dLng;

            $puntos[] = ['lat' => $lat * 1e-5, 'lng' => $lng * 1e-5];
        }

        return $puntos;
    }
}

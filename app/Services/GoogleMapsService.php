<?php

// Servicio para Google Maps Platform — Routes API v2.
// El frontend NUNCA toca esta API directamente: solo el backend conoce la
// llave de servidor (X-Goog-Api-Key) y decide el FieldMask a solicitar.
// Calcula rutas alternativas + peajes (TOLLS) para cotizaciones de grúas.
// En México se usa DRIVE: el modo TRUCK de Large Vehicle Routing está
// limitado geográficamente y además no devuelve precios de peaje de camión.

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class GoogleMapsService
{
    private const ENDPOINT = 'https://routes.googleapis.com/directions/v2:computeRoutes';

    // Máscara de campos: limita el tamaño de la respuesta y habilita peajes.
    private const FIELD_MASK = 'routes.distanceMeters,'
        . 'routes.duration,'
        . 'routes.polyline.encodedPolyline,'
        . 'routes.localizedValues.distance.text,'
        . 'routes.localizedValues.duration.text,'
        . 'routes.travelAdvisory.tollInfo';

    private string $apiKey;
    private string $country;
    private string $language;

    public function __construct()
    {
        $this->apiKey = (string) config('services.google.api_key');
        $this->country = (string) config('services.google.country', 'MX');
        $this->language = (string) config('services.google.language', 'es');
    }

    /**
     * Calcula las rutas disponibles (con alternativas) y sus peajes.
     *
     * @param  array{destino_lat: float, destino_lng: float, origen_lat: float, origen_lng: float}  $datos
     * @return array{
     *   origen: array|null,
     *   destino: array|null,
     *   rutas: array<int, array{
     *     indice: int,
     *     distancia_km: float,
     *     duracion_texto: string,
     *     duracion_segundos: int,
     *     coordenadas: array<int, array{lat: float, lng: float}>,
     *     polyline: string|null,
     *     costo_peaje: float,
     *     moneda_peaje: string
     *   }>
     * }
     *
     * @throws RuntimeException Si la llave no está configurada, Google rechaza
     *                          la petición o no hay rutas alternativas viables.
     */
    public function rutas(array $datos): array
    {
        if ($this->apiKey === '') {
            throw new RuntimeException('La API Key de Google Maps no está configurada. Revisa GOOGLE_MAPS_API_KEY en el .env.');
        }

        $payload = [
            'origin' => [
                'location' => [
                    'latLng' => [
                        'latitude' => (float) $datos['origen_lat'],
                        'longitude' => (float) $datos['origen_lng'],
                    ],
                ],
            ],
            'destination' => [
                'location' => [
                    'latLng' => [
                        'latitude' => (float) $datos['destino_lat'],
                        'longitude' => (float) $datos['destino_lng'],
                    ],
                ],
            ],
            // Tránsito por carretera, consciente del tráfico actual/estimado.
            'travelMode' => 'DRIVE',
            'routingPreference' => 'TRAFFIC_AWARE',
            // Rutas alternativas (máximo 3 según Google).
            'computeAlternativeRoutes' => true,
            'units' => 'METRIC',
            'languageCode' => $this->language,
            'regionCode' => $this->country,
            // Habilita el cálculo de casetas/peajes.
            'extraComputations' => ['TOLLS'],
            // No se envía vehicleInfo.type: ese campo no existe en Routes API v2.
            // El modo TRUCK requiere Large Vehicle Routing, dimensiones del vehículo,
            // acceso habilitado y actualmente está limitado a los 48 estados contiguos
            // de EE. UU.; para México se calcula la ruta estándar DRIVE.
        ];

        $respuesta = Http::timeout(15)
            ->withHeaders([
                'X-Goog-Api-Key' => $this->apiKey,
                'X-Goog-FieldMask' => self::FIELD_MASK,
            ])
            ->post(self::ENDPOINT, $payload);

        if ($respuesta->failed()) {
            $mensaje = $respuesta->json('error.message') ?? 'Error al comunicarse con Google Routes API.';
            $mensaje = str_replace('google.invalid_argument', '', $mensaje);

            throw new RuntimeException(
                trim(ucfirst($mensaje)) . ' (HTTP ' . $respuesta->status() . ')'
            );
        }

        $json = $respuesta->json();
        $rutas = [];

        foreach (($json['routes'] ?? []) as $indice => $ruta) {
            $peajes = $this->resumirPeajes($ruta['travelAdvisory']['tollInfo']['estimatedPrice'] ?? []);
            $polyline = $ruta['polyline']['encodedPolyline'] ?? null;
            $duracionSegundos = $this->duracionASegundos($ruta['duration'] ?? '0s');

            $rutas[] = [
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

        if ($rutas === []) {
            throw new RuntimeException('Google no devolvió rutas viables entre los puntos seleccionados.');
        }

        return [
            'origen' => $json['geocodingResults']['origin'] ?? null,
            'destino' => $json['geocodingResults']['destination'] ?? null,
            'rutas' => $rutas,
        ];
    }

    /**
     * Suma los montos de peaje de una ruta.
     * Google puede devolver varios precios (uno por moneda); se suman los de
     * la moneda preferida (config) y si aparece otra moneda se notifica.
     *
     * @param  array<int, array{currencyCode?: string, units?: string, nanos?: int}>  $precios
     * @return array{total: float, moneda: string}
     */
    private function resumirPeajes(array $precios): array
    {
        $sumas = [];
        $primeraMoneda = 'MXN';

        foreach ($precios as $precio) {
            $moneda = $precio['currencyCode'] ?? $primeraMoneda;
            $monto = (int) ($precio['units'] ?? 0) + ((int) ($precio['nanos'] ?? 0) / 1_000_000_000);
            $sumas[$moneda] = ($sumas[$moneda] ?? 0.0) + $monto;
        }

        unset($sumas['XXX']); // XXX = monto no disponible

        if ($sumas === []) {
            return ['total' => 0.0, 'moneda' => $this->country === 'MX' ? 'MXN' : $primeraMoneda];
        }

        // Prioriza la moneda local de la región configurada.
        $monedaLocal = $this->country === 'MX' ? 'MXN' : $primeraMoneda;

        return isset($sumas[$monedaLocal])
            ? ['total' => round($sumas[$monedaLocal], 2), 'moneda' => $monedaLocal]
            : ['total' => round(array_values($sumas)[0], 2), 'moneda' => array_key_first($sumas)];
    }

    /**
     * Convierte duración protobuf (ISO-8601, "2851.500s") a segundos.
     */
    private function duracionASegundos(string $duraccion): int
    {
        $segundos = 0.0;
        if (preg_match('/^(\d+(?:\.\d+)?)s$/', $duraccion, $coincidencias)) {
            $segundos = (float) $coincidencias[1];
        }

        return (int) round($segundos);
    }

    /**
     * Formatea una duración en segundos como "2 h 15 min" / "45 min" / "30 s".
     */
    private function formatearDuracion(int $segundos): string
    {
        $horas = intdiv($segundos, 3600);
        $minutos = intdiv($segundos % 3600, 60);

        if ($horas > 0) {
            return $minutos > 0 ? "{$horas} h {$minutos} min" : "{$horas} h";
        }

        return $minutos > 0 ? "{$minutos} min" : "{$segundos} s";
    }

    /**
     * Decodifica un polyline codificado (algoritmo de Google Polyline) a
     * una lista de puntos {lat, lng} listos para google.maps.Polyline.
     *
     * @return array<int, array{lat: float, lng: float}>
     */
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
                $byte = ord($polyline[$indice++]) - 63;
                $b |= ($byte & 0x1F) << $desplazamiento;
                $desplazamiento += 5;
            } while ($byte >= 0x20);

            $dLat = ($b & 1) ? ~($b >> 1) : ($b >> 1);
            $lat += $dLat;

            $b = 0;
            $desplazamiento = 0;
            do {
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

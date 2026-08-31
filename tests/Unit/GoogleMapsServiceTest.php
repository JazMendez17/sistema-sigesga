<?php

namespace Tests\Unit;

use App\Services\GoogleMapsService;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

class GoogleMapsServiceTest extends TestCase
{
    public function test_calcula_rutas_con_payload_compatible_con_routes_api(): void
    {
        Config::set('services.google.api_key', 'test-server-key');
        Config::set('services.google.country', 'MX');
        Config::set('services.google.language', 'es');

        Http::fake([
            'https://routes.googleapis.com/*' => Http::response([
                'routes' => [[
                    'distanceMeters' => 12345,
                    'duration' => '3600s',
                    'polyline' => [
                        'encodedPolyline' => '_p~iF~ps|U_ulLnnqC_mqNvxq`@',
                    ],
                    'travelAdvisory' => [
                        'tollInfo' => [
                            'estimatedPrice' => [[
                                'currencyCode' => 'MXN',
                                'units' => '50',
                                'nanos' => 0,
                            ]],
                        ],
                    ],
                ]],
            ], 200),
        ]);

        $resultado = app(GoogleMapsService::class)->rutas([
            'origen_lat' => 19.4326,
            'origen_lng' => -99.1332,
            'destino_lat' => 20.5888,
            'destino_lng' => -100.3899,
        ]);

        $this->assertSame(1, count($resultado['rutas']));
        $this->assertSame(12.35, $resultado['rutas'][0]['distancia_km']);
        $this->assertSame('1 h', $resultado['rutas'][0]['duracion_texto']);
        $this->assertSame(50.0, $resultado['rutas'][0]['costo_peaje']);
        $this->assertCount(3, $resultado['rutas'][0]['coordenadas']);

        Http::assertSent(function (Request $request): bool {
            $payload = $request->data();

            return $payload['travelMode'] === 'DRIVE'
                && $payload['extraComputations'] === ['TOLLS']
                && $payload['computeAlternativeRoutes'] === true
                && !isset($payload['routeModifiers'])
                && !str_contains($request->header('X-Goog-FieldMask')[0], 'geocodingResults.placeId');
        });
    }

    public function test_rechaza_la_peticion_si_no_hay_llave_de_servidor(): void
    {
        Config::set('services.google.api_key', '');

        $this->expectException(RuntimeException::class);

        app(GoogleMapsService::class)->rutas([
            'origen_lat' => 19.4326,
            'origen_lng' => -99.1332,
            'destino_lat' => 20.5888,
            'destino_lng' => -100.3899,
        ]);
    }
}

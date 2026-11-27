<?php

namespace Tests\Feature;

use App\Models\CatalogoServicio;
use App\Models\Cliente;
use App\Models\Cotizacione;
use App\Models\Empresa;
use App\Models\EmpresaIntegracione;
use App\Models\Notificacione;
use App\Models\PagoCotizacion;
use App\Models\PagoCotizacionIntento;
use App\Models\Usuario;
use App\Services\MercadoPagoService;
use App\Services\PagoCotizacionService;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class CheckoutProCotizacionTest extends TestCase
{
    use DatabaseMigrations;

    private const WEBHOOK_SECRET = 'secreto-prueba-mercadopago-2026';
    private const IDEMPOTENCY_KEY = 'c9bf9e57-1685-4c89-bafb-ff5af830be8a';
    private const PAYMENT_ID = '123456789';

    private Empresa $empresa;
    private Usuario $admin;
    private Usuario $clienteUser;
    private Cliente $cliente;
    private Cotizacione $cotizacion;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        config(['app.url' => 'https://sigesga.test']);

        $this->empresa = Empresa::create([
            'nombre' => 'Grúas SIGESGA',
            'siglas' => 'SGS',
            'email_contacto' => 'contacto@sigesga.test',
        ]);

        $this->admin = Usuario::factory()->create([
            'empresa_id' => $this->empresa->id,
            'rol' => 'admin',
        ]);

        $catalogo = CatalogoServicio::create([
            'empresa_id' => $this->empresa->id,
            'nombre' => 'Grúa Plataforma',
            'activo' => true,
        ]);

        $this->clienteUser = Usuario::factory()->create([
            'empresa_id' => $this->empresa->id,
            'rol' => 'cliente',
        ]);

        $this->cliente = Cliente::create([
            'empresa_id' => $this->empresa->id,
            'usuario_id' => $this->clienteUser->id,
            'nombre' => 'Carlos Particular',
            'email' => 'cliente@prueba.test',
            'tipo_cliente' => 'persona_fisica',
            'nacionalidad' => 'Mexicana',
            'aseguradora_id' => null,
        ]);

        $this->cotizacion = Cotizacione::create([
            'empresa_id' => $this->empresa->id,
            'cliente_id' => $this->cliente->id,
            'tipo_servicio_id' => $catalogo->id,
            'usuario_creador_id' => $this->admin->id,
            'folio' => 'COT-TEST-001',
            'origen_direccion' => 'Origen de prueba',
            'destino_direccion' => 'Destino de prueba',
            'distancia_km' => 10,
            'costo_banderazo' => 500,
            'costo_km' => 25,
            'costo_total' => 750,
            'estatus' => 'pendiente',
            'requiere_pago_tarjeta' => true,
            'estatus_pago' => 'pendiente',
        ]);
    }

    public function test_crea_preferencia_sandbox_sin_recibir_tarjeta_y_reutiliza_el_intento(): void
    {
        $this->configurarMercadoPago();
        Http::fake([
            'https://api.mercadopago.com/checkout/preferences' => function (Request $request) {
                $payload = $request->data();

                return Http::response([
                    'id' => 'PREF-TEST-001',
                    'sandbox_init_point' => 'https://sandbox.mercadopago.com/checkout/v1/pay/PREF-TEST-001',
                    'init_point' => 'https://www.mercadopago.com/checkout/v1/pay/PREF-TEST-001',
                ], 201);
            },
        ]);

        $ruta = route('panel.cotizaciones.pagar.cliente', ['id' => $this->cotizacion->id]);
        $respuesta = $this->actingAs($this->clienteUser)->withHeader('X-Inertia', 'true')->post($ruta, [
            'mp_idempotency_key' => self::IDEMPOTENCY_KEY,
            'tarjeta_numero' => '5474925432670366',
            'tarjeta_cvv' => '123',
        ]);

        $respuesta->assertStatus(409)
            ->assertHeader('X-Inertia-Location', 'https://sandbox.mercadopago.com/checkout/v1/pay/PREF-TEST-001');

        $intento = PagoCotizacionIntento::firstOrFail();
        $this->assertSame('pendiente', $intento->estado);
        $this->assertSame('PREF-TEST-001', $intento->mercadopago_preference_id);
        $this->assertSame(750.0, (float) $intento->request_payload['monto']);
        $this->assertSame('pendiente', $this->cotizacion->fresh()->estatus_pago);
        $this->assertDatabaseCount('pagos_cotizacion', 0);

        Http::assertSent(function (Request $request) use ($intento) {
            $payload = $request->data();

            return $request->url() === 'https://api.mercadopago.com/checkout/preferences'
                && $request->hasHeader('Authorization', 'Bearer APP_USR-TEST-ACCESS-TOKEN')
                && $payload['external_reference'] === $intento->referencia
                && $payload['items'][0]['unit_price'] === 750.0
                && $payload['items'][0]['currency_id'] === 'MXN'
                && $payload['notification_url'] === 'https://sigesga.test/mercadopago/webhook/' . $intento->referencia
                && !array_key_exists('tarjeta_numero', $payload)
                && !array_key_exists('tarjeta_cvv', $payload);
        });

        $this->actingAs($this->clienteUser)->withHeader('X-Inertia', 'true')->post($ruta, [
            'mp_idempotency_key' => self::IDEMPOTENCY_KEY,
        ])->assertStatus(409);

        $this->actingAs($this->clienteUser)->withHeader('X-Inertia', 'true')->post($ruta, [
            'mp_idempotency_key' => (string) Str::uuid(),
        ])->assertStatus(409);

        $this->assertDatabaseCount('pagos_cotizacion_intentos', 1);
        Http::assertSentCount(1);

        $otroClienteUser = Usuario::factory()->create([
            'empresa_id' => $this->empresa->id,
            'rol' => 'cliente',
        ]);
        $otroCliente = Cliente::create([
            'empresa_id' => $this->empresa->id,
            'usuario_id' => $otroClienteUser->id,
            'nombre' => 'Otro Cliente',
            'tipo_cliente' => 'persona_fisica',
            'nacionalidad' => 'Mexicana',
        ]);

        $this->actingAs($otroClienteUser)
            ->withHeader('X-Inertia', 'true')
            ->post($ruta, [
                'mp_idempotency_key' => (string) Str::uuid(),
            ])->assertNotFound();
    }

    public function test_reintento_reutiliza_el_checkout_rechazado_mientras_sigue_vigente(): void
    {
        $this->configurarMercadoPago();
        Http::fake([
            'https://api.mercadopago.com/checkout/preferences' => Http::response([
                'id' => 'PREF-REINTENTO-001',
                'sandbox_init_point' => 'https://sandbox.mercadopago.com/checkout/v1/pay/PREF-REINTENTO-001',
            ], 201),
        ]);

        $ruta = route('panel.cotizaciones.pagar.cliente', ['id' => $this->cotizacion->id]);
        $primera = $this->actingAs($this->clienteUser)
            ->withHeader('X-Inertia', 'true')
            ->post($ruta, ['mp_idempotency_key' => self::IDEMPOTENCY_KEY])
            ->assertStatus(409);
        $checkoutUrl = $primera->headers->get('X-Inertia-Location');

        PagoCotizacionIntento::firstOrFail()->update(['estado' => 'rechazado']);

        $this->actingAs($this->clienteUser)
            ->withHeader('X-Inertia', 'true')
            ->post($ruta, ['mp_idempotency_key' => (string) Str::uuid()])
            ->assertStatus(409)
            ->assertHeader('X-Inertia-Location', $checkoutUrl);

        $this->assertDatabaseCount('pagos_cotizacion_intentos', 1);
        Http::assertSentCount(1);
    }

    public function test_produccion_usa_init_point(): void
    {
        $this->configurarMercadoPago('produccion');
        Http::fake([
            'https://api.mercadopago.com/checkout/preferences' => Http::response([
                'id' => 'PREF-PROD-001',
                'sandbox_init_point' => 'https://sandbox.mercadopago.com/checkout/v1/pay/PREF-PROD-001',
                'init_point' => 'https://www.mercadopago.com/checkout/v1/pay/PREF-PROD-001',
            ], 201),
        ]);

        $this->actingAs($this->admin)
            ->withHeader('X-Inertia', 'true')
            ->post(route('panel.cotizaciones.pagar', ['id' => $this->cotizacion->id]), [
                'mp_idempotency_key' => self::IDEMPOTENCY_KEY,
            ])
            ->assertStatus(409)
            ->assertHeader('X-Inertia-Location', 'https://www.mercadopago.com/checkout/v1/pay/PREF-PROD-001');
    }

    public function test_listado_cliente_solo_ofrece_pago_cuando_esta_configurado(): void
    {
        $integracion = $this->configurarMercadoPago();
        $ruta = route('panel.cliente.cotizaciones');
        $version = hash_file('xxh128', public_path('build/manifest.json'));

        $respuesta = $this->actingAs($this->clienteUser)
            ->withHeader('X-Inertia', 'true')
            ->withHeader('X-Inertia-Version', $version)
            ->get($ruta)
            ->assertOk();
        $props = json_decode($respuesta->getContent(), true)['props'];
        $this->assertTrue($props['cotizaciones'][0]['puede_pagar']);

        $integracion->update(['activo' => false]);
        $respuesta = $this->actingAs($this->clienteUser)
            ->withHeader('X-Inertia', 'true')
            ->withHeader('X-Inertia-Version', $version)
            ->get($ruta)
            ->assertOk();
        $props = json_decode($respuesta->getContent(), true)['props'];
        $this->assertFalse($props['cotizaciones'][0]['puede_pagar']);
    }

    public function test_integraciones_no_expone_credenciales_y_conserva_el_secreto_si_queda_vacio(): void
    {
        $this->configurarMercadoPago();
        $intento = $this->crearIntento();
        $superadmin = Usuario::factory()->superadmin()->create(['empresa_id' => $this->empresa->id]);
        $version = hash_file('xxh128', public_path('build/manifest.json'));

        $respuesta = $this->actingAs($superadmin)
            ->withHeader('X-Inertia', 'true')
            ->withHeader('X-Inertia-Version', $version)
            ->get(route('panel.integraciones.index'))
            ->assertOk();
        $items = json_decode($respuesta->getContent(), true)['props']['integraciones'];
        $mp = collect($items)->firstWhere('proveedor', 'mercadopago');

        $this->assertTrue($mp['config']['webhook_secret_configurada']);
        $this->assertArrayNotHasKey('webhook_secret', $mp['config']);
        $this->assertArrayNotHasKey('api_key', $mp['config']);
        $this->assertSame(
            'https://sigesga.test/mercadopago/webhook/' . $intento->referencia,
            $mp['config']['webhook_url']
        );

        $this->actingAs($superadmin)
            ->put(route('panel.integraciones.update', ['id' => $mp['id']]), [
                'activo' => true,
                'modo' => 'pruebas',
                'api_key' => '',
                'webhook_secret' => '',
            ])
            ->assertSessionHasNoErrors();

        $integracion = EmpresaIntegracione::where('proveedor', 'mercadopago')->firstOrFail();
        $this->assertSame(self::WEBHOOK_SECRET, $integracion->fresh()->webhook_secret);
        $this->assertSame('APP_USR-TEST-ACCESS-TOKEN', $integracion->fresh()->api_key);
    }

    public function test_sin_access_token_no_crea_preferencia(): void
    {
        Http::fake();

        $this->actingAs($this->clienteUser)
            ->withHeader('X-Inertia', 'true')
            ->post(route('panel.cotizaciones.pagar.cliente', ['id' => $this->cotizacion->id]), [
                'mp_idempotency_key' => self::IDEMPOTENCY_KEY,
            ])
            ->assertSessionHasErrors('pago');

        $this->assertDatabaseCount('pagos_cotizacion_intentos', 0);
        Http::assertNothingSent();
    }

    public function test_error_de_preferencia_se_puede_reintentar_con_la_misma_clave(): void
    {
        $this->configurarMercadoPago();
        Http::fakeSequence()
            ->push(['message' => 'Invalid access token'], 401)
            ->push([
                'id' => 'PREF-RETRY-001',
                'sandbox_init_point' => 'https://sandbox.mercadopago.com/checkout/v1/pay/PREF-RETRY-001',
            ], 201);

        $ruta = route('panel.cotizaciones.pagar.cliente', ['id' => $this->cotizacion->id]);
        $this->actingAs($this->clienteUser)->withHeader('X-Inertia', 'true')->post($ruta, [
            'mp_idempotency_key' => self::IDEMPOTENCY_KEY,
        ])->assertSessionHasErrors('pago');

        $this->assertSame('error', PagoCotizacionIntento::firstOrFail()->estado);

        $this->actingAs($this->clienteUser)->withHeader('X-Inertia', 'true')->post($ruta, [
            'mp_idempotency_key' => self::IDEMPOTENCY_KEY,
        ])->assertStatus(409);

        $this->assertDatabaseCount('pagos_cotizacion_intentos', 1);
        $this->assertSame('pendiente', PagoCotizacionIntento::firstOrFail()->estado);
        Http::assertSentCount(2);
    }

    public function test_retorno_reconcilia_el_pago_oficial_e_ignora_el_estado_de_la_url(): void
    {
        $this->configurarMercadoPago();
        $intento = $this->crearIntento();
        $pagoMp = $this->pagoAprobado($intento->referencia);
        Http::fake([
            'https://api.mercadopago.com/v1/payments/search*' => Http::response(['results' => [$pagoMp]], 200),
        ]);

        $ruta = route('panel.mercadopago.retorno', ['referencia' => $intento->referencia]);
        $this->actingAs($this->clienteUser)
            ->get($ruta . '?status=rejected&payment_id=999999999')
            ->assertRedirect(route('panel.cotizaciones.show.cliente', ['id' => $this->cotizacion->id]))
            ->assertSessionHas('success');

        $pago = PagoCotizacion::firstOrFail();
        $this->assertSame('pagado', $pago->estatus);
        $this->assertSame('mercadopago_pro', $pago->pasarela);
        $this->assertSame(self::PAYMENT_ID, $pago->pasarela_transaccion_id);
        $this->assertSame('Visa', $pago->tarjeta_marca);
        $this->assertSame('0366', $pago->tarjeta_ultimos4);
        $this->assertSame('pagado', $this->cotizacion->fresh()->estatus_pago);
        $this->assertNotNull($this->cotizacion->fresh()->cliente_aprobada_at);
        $this->assertSame(2, Notificacione::count());
        Mail::assertSentTimes(\App\Mail\PagoConfirmadoMail::class, 2);

        $this->actingAs($this->clienteUser)->get($ruta)->assertSessionHas('success');
        $this->assertDatabaseCount('pagos_cotizacion', 1);
        $this->assertSame(2, Notificacione::count());
        Mail::assertSentTimes(\App\Mail\PagoConfirmadoMail::class, 2);

        $otraEmpresa = Empresa::create([
            'nombre' => 'Otra Empresa',
            'siglas' => 'OTR',
            'email_contacto' => 'otra@prueba.test',
        ]);
        $adminAjeno = Usuario::factory()->create([
            'empresa_id' => $otraEmpresa->id,
            'rol' => 'admin',
        ]);
        $this->actingAs($adminAjeno)->get($ruta)->assertNotFound();
    }

    public function test_retorno_no_marca_pagado_un_pago_rechazado(): void
    {
        $this->configurarMercadoPago();
        $intento = $this->crearIntento();
        $pagoMp = $this->pagoAprobado($intento->referencia);
        $pagoMp['status'] = 'rejected';
        $pagoMp['status_detail'] = 'cc_rejected_insufficient_amount';
        Http::fake([
            'https://api.mercadopago.com/v1/payments/search*' => Http::response(['results' => [$pagoMp]], 200),
        ]);

        $ruta = route('panel.mercadopago.retorno', ['referencia' => $intento->referencia]);
        $this->actingAs($this->clienteUser)
            ->get($ruta . '?status=approved')
            ->assertSessionHas('error');

        $this->assertDatabaseCount('pagos_cotizacion', 0);
        $this->assertSame('pendiente', $this->cotizacion->fresh()->estatus_pago);
        $this->assertSame('rechazado', $intento->fresh()->estado);
        $this->assertSame(1, Notificacione::count());

        $this->actingAs($this->clienteUser)->get($ruta)->assertSessionHas('error');
        $this->assertSame(1, Notificacione::count());
    }

    public function test_retorno_no_duplica_un_pago_si_mercadopago_reporta_otro_intento_aprobado(): void
    {
        $this->configurarMercadoPago();
        $primero = $this->crearIntento();
        $pagoMp = $this->pagoAprobado($primero->referencia);
        app(PagoCotizacionService::class)->confirmar($primero, $pagoMp);

        $segundo = $this->crearIntento('PAG-TEST-0002');
        $segundoPago = $this->pagoAprobado($segundo->referencia);
        $segundoPago['id'] = '987654321';
        Http::fake([
            'https://api.mercadopago.com/v1/payments/search*' => Http::response(['results' => [$segundoPago]], 200),
        ]);

        $this->actingAs($this->clienteUser)
            ->get(route('panel.mercadopago.retorno', ['referencia' => $segundo->referencia]))
            ->assertRedirect(route('panel.cotizaciones.show.cliente', ['id' => $this->cotizacion->id]))
            ->assertSessionHas('error');

        $this->assertDatabaseCount('pagos_cotizacion', 1);
        $this->assertSame('error', $segundo->fresh()->estado);
        $this->assertDatabaseHas('pagos_cotizacion', ['referencia' => $primero->referencia]);
    }

    public function test_webhook_rechaza_firma_invalida_sin_consultar_mercadopago(): void
    {
        $this->configurarMercadoPago();
        $intento = $this->crearIntento();
        Http::fake();
        $ts = (string) now()->getTimestampMs();

        $this->postJson(
            route('mercadopago.webhook', ['referencia' => $intento->referencia]) . '?data.id=' . self::PAYMENT_ID . '&type=payment',
            [],
            [
                'x-signature' => "ts={$ts},v1=" . str_repeat('0', 64),
                'x-request-id' => 'request-invalida',
            ]
        )->assertUnauthorized();

        Http::assertNothingSent();
        $this->assertDatabaseCount('pagos_cotizacion', 0);
    }

    public function test_webhook_rechaza_firma_vencida(): void
    {
        $this->configurarMercadoPago();
        $intento = $this->crearIntento();
        Http::fake();
        $ts = (string) now()->subMinutes(10)->getTimestampMs();
        $requestId = 'request-vencida';
        $firma = hash_hmac(
            'sha256',
            "id:" . self::PAYMENT_ID . ":request:{$requestId}:ts:{$ts}",
            self::WEBHOOK_SECRET
        );

        $this->postJson(
            route('mercadopago.webhook', ['referencia' => $intento->referencia]) . '?data.id=' . self::PAYMENT_ID . '&type=payment',
            [],
            [
                'x-signature' => "ts={$ts},v1={$firma}",
                'x-request-id' => $requestId,
            ]
        )->assertUnauthorized();

        Http::assertNothingSent();
    }

    public function test_webhook_aprobado_confirma_pago_sin_tarjeta_y_es_idempotente(): void
    {
        $this->configurarMercadoPago();
        $intento = $this->crearIntento();
        $pagoMp = $this->pagoAprobado($intento->referencia);
        $pagoMp['payment_type_id'] = 'ticket';
        $pagoMp['payment_method'] = ['id' => 'oxxo', 'type' => 'ticket'];
        unset($pagoMp['card']);
        Http::fake([
            'https://api.mercadopago.com/v1/payments/' . self::PAYMENT_ID => Http::response($pagoMp, 200),
        ]);

        $url = route('mercadopago.webhook', ['referencia' => $intento->referencia])
            . '?data.id=' . self::PAYMENT_ID . '&type=payment';
        $headers = $this->headersFirmados();

        $this->postJson($url, [], $headers)->assertOk();
        $this->postJson($url, [], $headers)->assertOk();

        $pago = PagoCotizacion::firstOrFail();
        $this->assertSame('ticket', $pago->metodo);
        $this->assertSame('Efectivo', $pago->descripcionMetodo());
        $this->assertNull($pago->tarjeta_titular);
        $this->assertNull($pago->tarjeta_ultimos4);
        $this->assertFalse($pago->tieneTarjeta());
        $this->assertSame('aprobado', $intento->fresh()->estado);
        $this->assertDatabaseCount('pagos_cotizacion', 1);
        $this->assertSame(2, Notificacione::count());
        Mail::assertSentTimes(\App\Mail\PagoConfirmadoMail::class, 2);
        Http::assertSentCount(2);

        $this->actingAs($this->clienteUser)
            ->get(route('panel.pagos.comprobante', ['id' => $pago->id]))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    #[DataProvider('pagosInvalidos')]
    public function test_webhook_no_acepta_datos_inconsistentes(string $campo, mixed $valor): void
    {
        $this->configurarMercadoPago();
        $intento = $this->crearIntento();
        $pagoMp = $this->pagoAprobado($intento->referencia);
        $pagoMp[$campo] = $valor;
        Http::fake([
            'https://api.mercadopago.com/v1/payments/' . self::PAYMENT_ID => Http::response($pagoMp, 200),
        ]);

        $this->postJson(
            route('mercadopago.webhook', ['referencia' => $intento->referencia]) . '?data.id=' . self::PAYMENT_ID . '&type=payment',
            [],
            $this->headersFirmados()
        )->assertOk();

        $this->assertDatabaseCount('pagos_cotizacion', 0);
        $this->assertSame('pendiente', $this->cotizacion->fresh()->estatus_pago);
        $this->assertSame('error', $intento->fresh()->estado);
    }

    public static function pagosInvalidos(): array
    {
        return [
            'monto diferente' => ['transaction_amount', 749.99],
            'moneda diferente' => ['currency_id', 'USD'],
            'referencia ajena' => ['external_reference', 'PAG-OTRO'],
            'entorno production' => ['live_mode', true],
        ];
    }

    public function test_webhook_pendiente_no_aprueba_la_cotizacion(): void
    {
        $this->configurarMercadoPago();
        $intento = $this->crearIntento();
        $pagoMp = $this->pagoAprobado($intento->referencia);
        $pagoMp['status'] = 'in_process';
        $pagoMp['status_detail'] = 'pending_review';
        Http::fake([
            'https://api.mercadopago.com/v1/payments/' . self::PAYMENT_ID => Http::response($pagoMp, 200),
        ]);

        $this->postJson(
            route('mercadopago.webhook', ['referencia' => $intento->referencia]) . '?data.id=' . self::PAYMENT_ID . '&type=payment',
            [],
            $this->headersFirmados()
        )->assertOk();

        $this->assertDatabaseCount('pagos_cotizacion', 0);
        $this->assertSame('pendiente', $intento->fresh()->estado);
        $this->assertSame('pendiente', $this->cotizacion->fresh()->estatus_pago);
        $this->assertSame(0, Notificacione::count());
    }

    public function test_webhook_rechazado_notifica_al_cliente_una_sola_vez(): void
    {
        $this->configurarMercadoPago();
        $intento = $this->crearIntento();
        $pagoMp = $this->pagoAprobado($intento->referencia);
        $pagoMp['status'] = 'rejected';
        $pagoMp['status_detail'] = 'cc_rejected_bad_filled';
        Http::fake([
            'https://api.mercadopago.com/v1/payments/' . self::PAYMENT_ID => Http::response($pagoMp, 200),
        ]);

        $url = route('mercadopago.webhook', ['referencia' => $intento->referencia])
            . '?data.id=' . self::PAYMENT_ID . '&type=payment';
        $headers = $this->headersFirmados();

        $this->postJson($url, [], $headers)->assertOk();
        $this->postJson($url, [], $headers)->assertOk();

        $this->assertDatabaseCount('pagos_cotizacion', 0);
        $this->assertSame('rechazado', $intento->fresh()->estado);
        $this->assertNotNull($intento->fresh()->rechazo_notificado_en);
        $this->assertSame(1, Notificacione::count());
        $this->assertDatabaseHas('notificaciones', ['usuario_id' => $this->clienteUser->id]);
    }

    public function test_secreto_webhook_se_cifra_y_no_se_expone(): void
    {
        $integracion = $this->configurarMercadoPago();

        $this->assertSame(self::WEBHOOK_SECRET, $integracion->fresh()->webhook_secret);
        $this->assertNotSame(self::WEBHOOK_SECRET, DB::table('empresa_integraciones')
            ->where('id', $integracion->id)
            ->value('webhook_secret'));
        $this->assertArrayNotHasKey('webhook_secret', $integracion->fresh()->toArray());
        $this->assertArrayNotHasKey('api_key', $integracion->fresh()->toArray());
        $this->assertTrue(MercadoPagoService::obtenerConfigMercadoPago($this->empresa->id)['activo']);
    }

    private function configurarMercadoPago(string $modo = 'pruebas'): EmpresaIntegracione
    {
        return EmpresaIntegracione::create([
            'empresa_id' => $this->empresa->id,
            'proveedor' => 'mercadopago',
            'api_key' => 'APP_USR-TEST-ACCESS-TOKEN',
            'webhook_secret' => self::WEBHOOK_SECRET,
            'configuracion_json' => ['modo' => $modo],
            'activo' => true,
        ]);
    }

    private function crearIntento(string $referencia = 'PAG-TEST-0001'): PagoCotizacionIntento
    {
        return PagoCotizacionIntento::create([
            'empresa_id' => $this->empresa->id,
            'cotizacion_id' => $this->cotizacion->id,
            'idempotency_key' => (string) Str::uuid(),
            'provider_idempotency_key' => (string) Str::uuid(),
            'referencia' => $referencia,
            'estado' => 'pendiente',
            'mercadopago_preference_id' => 'PREF-TEST-001',
            'request_payload' => [
                'monto' => 750.0,
                'moneda' => 'MXN',
                'modo' => 'pruebas',
                'usuario_id' => $this->clienteUser->id,
                'expira_en' => now()->addHours(2)->toIso8601String(),
            ],
        ]);
    }

    private function pagoAprobado(string $referencia): array
    {
        return [
            'id' => self::PAYMENT_ID,
            'status' => 'approved',
            'status_detail' => 'accredited',
            'external_reference' => $referencia,
            'transaction_amount' => 750.0,
            'currency_id' => 'MXN',
            'live_mode' => false,
            'payment_type_id' => 'credit_card',
            'payment_method' => ['id' => 'visa', 'type' => 'credit_card'],
            'card' => [
                'last_four_digits' => '0366',
                'expiration_month' => 11,
                'expiration_year' => 2030,
                'cardholder' => ['name' => 'Carlos Particular'],
            ],
            'date_approved' => now()->toIso8601String(),
        ];
    }

    private function headersFirmados(): array
    {
        $ts = (string) now()->getTimestampMs();
        $requestId = 'request-prueba-001';
        $firma = hash_hmac(
            'sha256',
            'id:' . self::PAYMENT_ID . ":request:{$requestId}:ts:{$ts}",
            self::WEBHOOK_SECRET
        );

        return [
            'x-signature' => "ts={$ts},v1={$firma}",
            'x-request-id' => $requestId,
        ];
    }
}

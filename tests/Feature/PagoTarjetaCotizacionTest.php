<?php

namespace Tests\Feature;

use App\Models\Aseguradora;
use App\Models\CatalogoServicio;
use App\Models\Cliente;
use App\Models\Cotizacione;
use App\Models\Empleado;
use App\Models\Empresa;
use App\Models\EmpresaIntegracione;
use App\Models\Operadore;
use App\Models\PagoCotizacion;
use App\Models\Notificacione;
use App\Models\Unidade;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Client\ConnectionException;
use App\Mail\PagoConfirmadoMail;
use Tests\TestCase;

class PagoTarjetaCotizacionTest extends TestCase
{
    use DatabaseMigrations;

    private Empresa $empresa;
    private Usuario $admin;
    private CatalogoServicio $servicioCat;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();

        $this->empresa = Empresa::create([
            'nombre' => 'Grúas SIGESGA',
            'siglas' => 'SGS',
            'email_contacto' => 'contacto@sigesga.test',
        ]);

        $this->admin = Usuario::factory()->create([
            'empresa_id' => $this->empresa->id,
            'rol' => 'admin',
        ]);

        $this->servicioCat = CatalogoServicio::create([
            'empresa_id' => $this->empresa->id,
            'nombre' => 'Grúa Plataforma',
            'activo' => true,
        ]);
    }

    public function test_cliente_sin_convenio_requiere_cobro_con_tarjeta_y_no_guarda_pan_ni_cvv(): void
    {
        $clienteUser = Usuario::factory()->create([
            'empresa_id' => $this->empresa->id,
            'rol' => 'cliente',
        ]);
        $superadmin = Usuario::factory()->superadmin()->create(['empresa_id' => $this->empresa->id]);
        $cotizador = Usuario::factory()->create(['empresa_id' => $this->empresa->id, 'rol' => 'cotizador']);

        $clienteParticular = Cliente::create([
            'empresa_id' => $this->empresa->id,
            'usuario_id' => $clienteUser->id,
            'nombre' => 'Carlos Particular',
            'tipo_cliente' => 'persona_fisica',
            'nacionalidad' => 'Mexicana',
            'aseguradora_id' => null,
        ]);

        // Admin crea cotización para cliente sin aseguradora/convenio
        $this->actingAs($this->admin)->post('/panel/cotizaciones', [
            'cliente_id' => $clienteParticular->id,
            'tipo_servicio_id' => $this->servicioCat->id,
            'origen_direccion' => 'Av. Reforma 100',
            'destino_direccion' => 'Av. Insurgentes 500',
            'distancia_km' => 15,
            'costo_banderazo' => 500,
            'km_incluidos' => 10,
            'costo_km' => 30,
            'costo_total' => 754,
        ])->assertSessionHasNoErrors();

        $cotizacion = Cotizacione::firstOrFail();
        $this->assertTrue((bool) $cotizacion->requiere_pago_tarjeta);
        $this->assertSame('pendiente', $cotizacion->estatus_pago);

        // 1. Cliente NO puede aprobar sin pagar con tarjeta
        $this->actingAs($clienteUser)
            ->post("/panel/cliente/cotizaciones/{$cotizacion->id}/aprobar")
            ->assertSessionHas('error');
        $this->assertNull($cotizacion->fresh()->cliente_aprobada_at);

        // 2. Nunca se aceptan los datos de tarjeta directamente en el servidor.
        $this->actingAs($clienteUser)
            ->post("/panel/cliente/cotizaciones/{$cotizacion->id}/pagar-tarjeta", [
                'tarjeta_titular' => 'Carlos Particular',
                'tarjeta_numero' => '4242 4242 4242 4241',
                'tarjeta_exp_mes' => 12,
                'tarjeta_exp_anio' => now()->year + 2,
                'tarjeta_cvv' => '123',
            ])
            ->assertSessionHasErrors('mp_token');

        // 3. Una tarjeta vencida se rechaza aunque llegue un token válido.
        $this->actingAs($clienteUser)
            ->post("/panel/cliente/cotizaciones/{$cotizacion->id}/pagar-tarjeta", [
                ...$this->datosTarjetaTokenizada('Carlos Particular'),
                'tarjeta_exp_mes' => 1,
                'tarjeta_exp_anio' => now()->year - 1,
            ])
            ->assertSessionHasErrors('tarjeta_exp_anio');

        // 4. No se registra ningún pago si las credenciales de la pasarela no están configuradas.
        $this->actingAs($clienteUser)
            ->post("/panel/cliente/cotizaciones/{$cotizacion->id}/pagar-tarjeta", [
                ...$this->datosTarjetaTokenizada('Carlos Particular'),
                'tarjeta_exp_mes' => 12,
                'tarjeta_exp_anio' => now()->year + 3,
            ])
            ->assertSessionHasErrors('tarjeta');
        $this->assertDatabaseCount('pagos_cotizacion', 0);

        $this->configurarMercadoPago();
        $notificacionesAntes = Notificacione::count();
        $ultimaNotificacionAntes = (int) (Notificacione::max('id') ?? 0);

        // 5. Tarjeta tokenizada aprobada por Mercado Pago confirma y aprueba la cotización.
        $this->actingAs($clienteUser)
            ->post("/panel/cliente/cotizaciones/{$cotizacion->id}/pagar-tarjeta", [
                ...$this->datosTarjetaTokenizada('Carlos Particular'),
                'tarjeta_exp_mes' => 12,
                'tarjeta_exp_anio' => now()->year + 3,
            ])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success');

        $cotizacion->refresh();
        $this->assertSame('pagado', $cotizacion->estatus_pago);
        $this->assertNotNull($cotizacion->pagada_at);
        $this->assertNotNull($cotizacion->cliente_aprobada_at);

        $pago = PagoCotizacion::where('cotizacion_id', $cotizacion->id)->firstOrFail();
        $this->assertSame('Visa', $pago->tarjeta_marca);
        $this->assertSame('4242', $pago->tarjeta_ultimos4);
        $this->assertSame('pagado', $pago->estatus);
        $this->assertEquals((float) $cotizacion->costo_total, (float) $pago->monto);
        $this->assertStringStartsWith('PAG-', $pago->referencia);
        $this->assertArrayNotHasKey('tarjeta_numero', $pago->getAttributes());
        $this->assertArrayNotHasKey('tarjeta_cvv', $pago->getAttributes());
        $this->assertDatabaseHas('notificaciones', ['usuario_id' => $clienteUser->id, 'canal' => 'sistema_push']);
        $this->assertDatabaseHas('notificaciones', ['usuario_id' => $this->admin->id, 'canal' => 'sistema_push']);
        $this->assertDatabaseHas('notificaciones', ['usuario_id' => $superadmin->id, 'canal' => 'sistema_push']);
        $this->assertDatabaseHas('notificaciones', ['usuario_id' => $cotizador->id, 'canal' => 'sistema_push']);
        Mail::assertSent(PagoConfirmadoMail::class, 4);

        $respuestaPush = $this->actingAs($clienteUser)
            ->getJson('/panel/notificaciones/no-leidas?after=' . $ultimaNotificacionAntes)
            ->assertOk();
        $this->assertStringContainsString(
            'Pago realizado con éxito',
            $respuestaPush->json('notifications.0.mensaje')
        );

        $this->actingAs($clienteUser)
            ->get("/panel/pagos/{$pago->id}/comprobante")
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');

        // 6. Repetir el mismo intento devuelve el pago registrado sin cobrar ni notificar dos veces.
        $this->actingAs($clienteUser)
            ->post("/panel/cliente/cotizaciones/{$cotizacion->id}/pagar-tarjeta", [
                ...$this->datosTarjetaTokenizada('Carlos Particular'),
                'tarjeta_exp_mes' => 12,
                'tarjeta_exp_anio' => now()->year + 3,
            ])
            ->assertSessionHas('success');
        $this->assertDatabaseCount('pagos_cotizacion', 1);
        $this->assertSame($notificacionesAntes + 4, Notificacione::count());
        Mail::assertSent(PagoConfirmadoMail::class, 4);
        Http::assertSentCount(1);

        // 7. Una vez pagada, el administrador puede aprobar internamente y crear el servicio
        $emp = Empleado::create([
            'empresa_id' => $this->empresa->id,
            'nombre' => 'Operador Uno',
            'apellido_paterno' => 'Prueba',
            'apellido_materno' => 'Test',
            'sexo' => 'M',
            'curp' => 'OPUN900101HDFRRN09',
            'fecha_nacimiento' => '1990-01-01',
            'telefono' => '5511223344',
            'puesto' => 'operador',
        ]);
        $op = Operadore::create([
            'empresa_id' => $this->empresa->id,
            'empleado_id' => $emp->id,
            'numero_licencia' => 'LIC-999',
            'tipo_licencia' => 'Federal',
            'fecha_expedicion' => '2025-01-01',
            'fecha_vigencia' => '2028-01-01',
            'disponible' => true,
        ]);
        $uni = Unidade::create([
            'empresa_id' => $this->empresa->id,
            'numero_economico' => 'UNI-99',
            'marca' => 'Ford',
            'modelo' => '2025',
            'placas' => 'PAGO123',
            'tipo' => 'Plataforma',
            'activo' => true,
        ]);

        $this->actingAs($this->admin)
            ->post("/panel/cotizaciones/{$cotizacion->id}/aprobar", [
                'operador_id' => $op->id,
                'unidad_id' => $uni->id,
            ])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success');

        $this->assertSame('aprobado', $cotizacion->fresh()->estatus);
        $this->assertDatabaseHas('servicios', ['cotizacion_id' => $cotizacion->id]);
    }

    public function test_cliente_con_aseguradora_aprueba_sin_cobro_con_tarjeta(): void
    {
        $aseguradora = Aseguradora::create([
            'empresa_id' => $this->empresa->id,
            'nombre' => 'GNP Seguros',
        ]);

        $clienteUser = Usuario::factory()->create([
            'empresa_id' => $this->empresa->id,
            'rol' => 'cliente',
        ]);

        $clienteAsegurado = Cliente::create([
            'empresa_id' => $this->empresa->id,
            'usuario_id' => $clienteUser->id,
            'aseguradora_id' => $aseguradora->id,
            'nombre' => 'Ana Asegurada',
            'tipo_cliente' => 'persona_fisica',
            'nacionalidad' => 'Mexicana',
        ]);

        $this->actingAs($this->admin)->post('/panel/cotizaciones', [
            'cliente_id' => $clienteAsegurado->id,
            'tipo_servicio_id' => $this->servicioCat->id,
            'origen_direccion' => 'Origen A',
            'destino_direccion' => 'Destino B',
            'distancia_km' => 10,
            'costo_banderazo' => 500,
            'km_incluidos' => 10,
            'costo_km' => 25,
            'costo_total' => 580,
        ])->assertSessionHasNoErrors();

        $cotizacion = Cotizacione::firstOrFail();
        $this->assertFalse((bool) $cotizacion->requiere_pago_tarjeta);
        $this->assertSame('no_requerido', $cotizacion->estatus_pago);

        $this->actingAs($clienteUser)
            ->post("/panel/cliente/cotizaciones/{$cotizacion->id}/aprobar")
            ->assertSessionHas('success');

        $this->assertNotNull($cotizacion->fresh()->cliente_aprobada_at);

        // Un cliente con aseguradora también puede optar por pagar con tarjeta si lo desea
        $this->configurarMercadoPago();
        $this->actingAs($clienteUser)
            ->post("/panel/cliente/cotizaciones/{$cotizacion->id}/pagar-tarjeta", [
                ...$this->datosTarjetaTokenizada('Ana Asegurada'),
                'tarjeta_exp_mes' => 12,
                'tarjeta_exp_anio' => now()->year + 2,
            ])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success');

        $this->assertSame('pagado', $cotizacion->fresh()->estatus_pago);
    }

    public function test_cobro_con_mercadopago_orders_api_y_consulta_por_roles(): void
    {
        $this->configurarMercadoPago();

        $clienteUser = Usuario::factory()->create(['empresa_id' => $this->empresa->id, 'rol' => 'cliente']);
        $cliente = Cliente::create([
            'empresa_id' => $this->empresa->id,
            'usuario_id' => $clienteUser->id,
            'nombre' => 'Lucia MP',
            'email' => 'lucia@cliente.test',
            'tipo_cliente' => 'persona_fisica',
            'nacionalidad' => 'Mexicana',
        ]);

        $cot = Cotizacione::create([
            'empresa_id' => $this->empresa->id,
            'cliente_id' => $cliente->id,
            'tipo_servicio_id' => $this->servicioCat->id,
            'usuario_creador_id' => $this->admin->id,
            'folio' => 'COT-00777',
            'origen_direccion' => 'Origen MP',
            'destino_direccion' => 'Destino MP',
            'distancia_km' => 12,
            'costo_banderazo' => 500,
            'costo_km' => 25,
            'costo_total' => 638.00,
            'estatus' => 'pendiente',
            'requiere_pago_tarjeta' => true,
            'estatus_pago' => 'pendiente',
        ]);

        $this->actingAs($clienteUser)
            ->post("/panel/cliente/cotizaciones/{$cot->id}/pagar-tarjeta", [
                ...$this->datosTarjetaTokenizada('Lucia MP', '3704', 'Mastercard', 'master'),
                'tarjeta_exp_mes' => 11,
                'tarjeta_exp_anio' => now()->year + 2,
            ])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success');

        Http::assertSent(function ($request) {
            return $request->url() === 'https://api.mercadopago.com/v1/orders'
                && $request->hasHeader('X-Idempotency-Key')
                && $request->hasHeader('Authorization', 'Bearer TEST-ACCESS-TOKEN-123')
                && ($request['type'] ?? null) === 'online'
                && ($request['processing_mode'] ?? null) === 'automatic'
                && ($request['transactions']['payments'][0]['payment_method']['token'] ?? null) === 'tok_mp_1234567890abcdef'
                && ($request['transactions']['payments'][0]['payment_method']['installments'] ?? null) === 1;
        });

        $pago = PagoCotizacion::where('cotizacion_id', $cot->id)->firstOrFail();
        $this->assertSame('mercadopago_orders', $pago->pasarela);
        $this->assertSame('PAY01MP777666', $pago->pasarela_transaccion_id);
        $this->assertSame('3704', $pago->tarjeta_ultimos4);
        $this->assertSame('Mastercard', $pago->tarjeta_marca);

        // Superadmin, admin y cotizador pueden consultar /panel/pagos; operador y cliente reciben 403
        $superadmin = Usuario::factory()->superadmin()->create(['empresa_id' => $this->empresa->id]);
        $cotizador = Usuario::factory()->create(['empresa_id' => $this->empresa->id, 'rol' => 'cotizador']);
        $operador = Usuario::factory()->create(['empresa_id' => $this->empresa->id, 'rol' => 'operador']);

        $this->actingAs($superadmin)->get('/panel/pagos')->assertOk();
        $this->actingAs($this->admin)->get('/panel/pagos')->assertOk();
        $this->actingAs($cotizador)->get('/panel/pagos')->assertOk();
        $this->actingAs($operador)->get('/panel/pagos')->assertForbidden();
        $this->actingAs($clienteUser)->get('/panel/pagos')->assertForbidden();
    }

    public function test_rechazo_notifica_solo_al_cliente_y_no_duplica_el_aviso_al_reintentar(): void
    {
        $this->crearIntegracionMercadoPago();
        Http::fake([
            'https://api.mercadopago.com/v1/orders' => Http::response([
                'id' => 'ORD-RECHAZADA',
                'status' => 'rejected',
                'transactions' => [
                    'payments' => [[
                        'id' => 'PAY-RECHAZADO',
                        'status' => 'rejected',
                        'status_detail' => 'cc_rejected_insufficient_amount',
                    ]],
                ],
            ], 201),
        ]);

        [$clienteUser, $cotizacion] = $this->crearCotizacionDeCliente('COT-RECHAZO');
        $superadmin = Usuario::factory()->superadmin()->create(['empresa_id' => $this->empresa->id]);
        $cotizador = Usuario::factory()->create(['empresa_id' => $this->empresa->id, 'rol' => 'cotizador']);
        $datos = [
            ...$this->datosTarjetaTokenizada('Cliente Rechazo'),
            'tarjeta_exp_mes' => 12,
            'tarjeta_exp_anio' => now()->year + 2,
        ];

        $this->actingAs($clienteUser)
            ->post("/panel/cliente/cotizaciones/{$cotizacion->id}/pagar-tarjeta", $datos)
            ->assertSessionHasErrors('tarjeta');

        $this->assertDatabaseCount('notificaciones', 1);
        $this->assertDatabaseHas('notificaciones', [
            'usuario_id' => $clienteUser->id,
            'canal' => 'sistema_push',
        ]);
        $this->assertDatabaseMissing('notificaciones', ['usuario_id' => $this->admin->id]);
        $this->assertDatabaseMissing('notificaciones', ['usuario_id' => $superadmin->id]);
        $this->assertDatabaseMissing('notificaciones', ['usuario_id' => $cotizador->id]);
        $this->assertDatabaseHas('pagos_cotizacion_intentos', ['estado' => 'rechazado']);
        $this->assertDatabaseCount('pagos_cotizacion', 0);

        $this->actingAs($clienteUser)
            ->post("/panel/cliente/cotizaciones/{$cotizacion->id}/pagar-tarjeta", $datos)
            ->assertSessionHasErrors('tarjeta');

        $this->assertDatabaseCount('notificaciones', 1);
        Http::assertSentCount(1);
    }

    public function test_timeout_reintenta_payload_y_clave_del_proveedor_sin_generar_otro_cargo(): void
    {
        $this->crearIntegracionMercadoPago();
        $solicitudes = [];
        Http::fake(function ($request) use (&$solicitudes) {
            $solicitudes[] = [
                'idempotency_key' => implode('', $request->header('X-Idempotency-Key')),
                'payload' => $request->data(),
            ];
            if (count($solicitudes) === 1) {
                throw new ConnectionException('timeout simulado');
            }

            return Http::response([
                'id' => 'ORD-REINTENTO',
                'status' => 'processed',
                'transactions' => ['payments' => [[
                    'id' => 'PAY-REINTENTO',
                    'status' => 'processed',
                    'status_detail' => 'accredited',
                ]]],
            ], 201);
        });

        [$clienteUser, $cotizacion] = $this->crearCotizacionDeCliente('COT-REINTENTO');
        $datos = [
            ...$this->datosTarjetaTokenizada('Cliente Reintento'),
            'tarjeta_exp_mes' => 12,
            'tarjeta_exp_anio' => now()->year + 2,
        ];

        $this->actingAs($clienteUser)
            ->post("/panel/cliente/cotizaciones/{$cotizacion->id}/pagar-tarjeta", $datos)
            ->assertSessionHasErrors('tarjeta');
        $this->assertDatabaseHas('pagos_cotizacion_intentos', ['estado' => 'incierto']);

        $this->actingAs($clienteUser)
            ->post("/panel/cliente/cotizaciones/{$cotizacion->id}/pagar-tarjeta", $datos)
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success');

        $this->assertCount(2, $solicitudes);
        $this->assertSame($solicitudes[0]['idempotency_key'], $solicitudes[1]['idempotency_key']);
        $this->assertSame($solicitudes[0]['payload'], $solicitudes[1]['payload']);
        $this->assertDatabaseCount('pagos_cotizacion', 1);
        $this->assertDatabaseHas('pagos_cotizacion_intentos', ['estado' => 'aprobado']);
    }

    public function test_credenciales_de_mercado_pago_deben_corresponder_al_modo_seleccionado(): void
    {
        EmpresaIntegracione::create([
            'empresa_id' => $this->empresa->id,
            'proveedor' => 'mercadopago',
            'api_key' => 'APP_USR-LIVE-TOKEN',
            'configuracion_json' => [
                'public_key' => 'TEST-PUBLIC-KEY',
                'modo' => 'pruebas',
            ],
            'activo' => true,
        ]);

        $config = \App\Services\PagoTarjetaService::obtenerConfigMercadoPago($this->empresa->id);

        $this->assertFalse($config['activo']);
        $this->assertFalse($config['ambiente_valido']);
        $this->assertNull($config['public_key']);
        $this->assertNull($config['access_token']);
    }

    private function configurarMercadoPago(): void
    {
        $this->crearIntegracionMercadoPago();

        Http::fake([
            'https://api.mercadopago.com/v1/orders' => Http::response([
                'id' => 'ORD01MP999888',
                'status' => 'processed',
                'transactions' => [
                    'payments' => [
                        [
                            'id' => 'PAY01MP777666',
                            'status' => 'processed',
                            'status_detail' => 'accredited',
                        ],
                    ],
                ],
            ], 201),
        ]);
    }

    private function crearIntegracionMercadoPago(): void
    {
        EmpresaIntegracione::create([
            'empresa_id' => $this->empresa->id,
            'proveedor' => 'mercadopago',
            'api_key' => 'TEST-ACCESS-TOKEN-123',
            'configuracion_json' => [
                'public_key' => 'TEST-PUBLIC-KEY-456',
                'descripcion' => 'Mercado Pago',
                'modo' => 'pruebas',
            ],
            'activo' => true,
        ]);
    }

    private function datosTarjetaTokenizada(
        string $titular,
        string $ultimos4 = '4242',
        string $marca = 'Visa',
        string $metodo = 'visa'
    ): array {
        return [
            'tarjeta_titular' => $titular,
            'tarjeta_ultimos4' => $ultimos4,
            'tarjeta_marca' => $marca,
            'mp_token' => 'tok_mp_1234567890abcdef',
            'mp_idempotency_key' => 'c9bf9e57-1685-4c89-bafb-ff5af830be8a',
            'mp_payment_method_id' => $metodo,
            'mp_payment_type_id' => 'credit_card',
            'mp_installments' => 1,
        ];
    }

    private function crearCotizacionDeCliente(string $folio): array
    {
        $clienteUser = Usuario::factory()->create(['empresa_id' => $this->empresa->id, 'rol' => 'cliente']);
        $cliente = Cliente::create([
            'empresa_id' => $this->empresa->id,
            'usuario_id' => $clienteUser->id,
            'nombre' => 'Cliente de prueba',
            'email' => 'cliente@prueba.test',
            'tipo_cliente' => 'persona_fisica',
            'nacionalidad' => 'Mexicana',
        ]);
        $cotizacion = Cotizacione::create([
            'empresa_id' => $this->empresa->id,
            'cliente_id' => $cliente->id,
            'tipo_servicio_id' => $this->servicioCat->id,
            'usuario_creador_id' => $this->admin->id,
            'folio' => $folio,
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

        return [$clienteUser, $cotizacion];
    }
}

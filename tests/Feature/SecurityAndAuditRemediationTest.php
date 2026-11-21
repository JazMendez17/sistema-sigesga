<?php

namespace Tests\Feature;

use App\Models\Aseguradora;
use App\Models\AutorizacionesCancelacione;
use App\Models\CalificacionesServicio;
use App\Models\CatalogoServicio;
use App\Models\Cliente;
use App\Models\Convenio;
use App\Models\ConvenioConceptoAdicional;
use App\Models\ConvenioManiobrasEspeciale;
use App\Models\ConvenioSla;
use App\Models\ConvenioTarifa;
use App\Models\Cotizacione;
use App\Models\Empleado;
use App\Models\Empresa;
use App\Models\Factura;
use App\Models\Notificacione;
use App\Models\Operadore;
use App\Models\Servicio;
use App\Models\TarifasEmpresa;
use App\Models\Unidade;
use App\Models\Usuario;
use App\Services\FacturaCalculator;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SecurityAndAuditRemediationTest extends TestCase
{
    use DatabaseMigrations;

    private Empresa $empresa1;
    private Empresa $empresa2;
    private Usuario $superadmin;
    private Usuario $admin1;
    private Usuario $admin2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->empresa1 = Empresa::create([
            'nombre' => 'Grúas Alpha S.A.',
            'siglas' => 'ALPHA',
            'email_contacto' => 'alpha@sigesga.test',
        ]);

        $this->empresa2 = Empresa::create([
            'nombre' => 'Grúas Beta S.A.',
            'siglas' => 'BETA',
            'email_contacto' => 'beta@sigesga.test',
        ]);

        $this->superadmin = Usuario::factory()->superadmin()->create([
            'empresa_id' => $this->empresa1->id,
            'email' => 'super@alpha.test',
        ]);

        $this->admin1 = Usuario::factory()->create([
            'empresa_id' => $this->empresa1->id,
            'rol' => 'admin',
            'email' => 'admin@alpha.test',
        ]);

        $this->admin2 = Usuario::factory()->create([
            'empresa_id' => $this->empresa2->id,
            'rol' => 'admin',
            'email' => 'admin@beta.test',
        ]);
    }

    public function test_superadmin_can_create_edit_and_physically_delete_admin_without_leaking_secrets_in_audit(): void
    {
        $this->actingAs($this->superadmin);

        // Crear un usuario admin
        $this->post('/panel/usuarios', [
            'name' => 'Admin Temporal',
            'email' => 'admin.temp@alpha.test',
            'password' => 'Segura123!',
            'rol' => 'admin',
            'telefono' => '5511223344',
        ])->assertRedirect(route('panel.usuarios.index'))
            ->assertSessionHasNoErrors();

        $nuevoAdmin = Usuario::where('email', 'admin.temp@alpha.test')->first();
        $this->assertNotNull($nuevoAdmin);
        $this->assertSame('admin', $nuevoAdmin->rol);

        // Editar el admin
        $this->put("/panel/usuarios/{$nuevoAdmin->id}", [
            'name' => 'Admin Editado',
            'email' => 'admin.temp@alpha.test',
            'rol' => 'admin',
            'cuenta_bloqueada' => true,
        ])->assertSessionHasNoErrors();

        $nuevoAdmin->refresh();
        $this->assertTrue((bool) $nuevoAdmin->cuenta_bloqueada);
        $this->assertTrue((bool) $nuevoAdmin->bloqueo_administrativo);

        // Borrado FÍSICO del admin
        $this->delete("/panel/usuarios/{$nuevoAdmin->id}")
            ->assertRedirect(route('panel.usuarios.index'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseMissing('usuarios', ['id' => $nuevoAdmin->id]);

        // La auditoría respalda el borrado SIN secretos sensibles
        $audit = DB::table('auditoria_eliminaciones')
            ->where('modelo', 'usuarios')
            ->where('registro_id', $nuevoAdmin->id)
            ->first();
        $this->assertNotNull($audit);
        $datosAudit = json_decode($audit->datos, true);
        $this->assertArrayNotHasKey('password', $datosAudit);
        $this->assertArrayNotHasKey('codigo_desbloqueo', $datosAudit);
        $this->assertArrayNotHasKey('remember_token', $datosAudit);

        // Como fue borrado físico, se puede reutilizar el mismo correo sin conflicto UNIQUE
        $this->post('/panel/usuarios', [
            'name' => 'Admin Reemplazo',
            'email' => 'admin.temp@alpha.test',
            'password' => 'Segura123!',
            'rol' => 'admin',
        ])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('usuarios', ['email' => 'admin.temp@alpha.test']);
    }

    public function test_regular_admin_cannot_create_edit_or_delete_admin_or_superadmin(): void
    {
        $this->actingAs($this->admin1);

        // No puede crear rol admin ni superadmin
        $this->post('/panel/usuarios', [
            'name' => 'Intento Admin',
            'email' => 'intento@alpha.test',
            'password' => 'Segura123!',
            'rol' => 'admin',
        ])->assertSessionHasErrors('rol');

        $this->post('/panel/usuarios', [
            'name' => 'Intento Super',
            'email' => 'intento.super@alpha.test',
            'password' => 'Segura123!',
            'rol' => 'superadmin',
        ])->assertSessionHasErrors('rol');

        // No puede editar ni eliminar al superadmin
        $this->get("/panel/usuarios/{$this->superadmin->id}/edit")->assertForbidden();
        $this->put("/panel/usuarios/{$this->superadmin->id}/rol", ['rol' => 'operador'])->assertForbidden();
        $this->delete("/panel/usuarios/{$this->superadmin->id}")->assertForbidden();

        // No puede eliminar a otro admin
        $otroAdmin = Usuario::factory()->create(['empresa_id' => $this->empresa1->id, 'rol' => 'admin']);
        $this->delete("/panel/usuarios/{$otroAdmin->id}")->assertForbidden();
        $this->assertDatabaseHas('usuarios', ['id' => $otroAdmin->id]);
    }

    public function test_last_superadmin_cannot_be_deleted_or_demoted(): void
    {
        $this->actingAs($this->superadmin);

        $this->delete("/panel/usuarios/{$this->superadmin->id}")
            ->assertRedirect()
            ->assertSessionHas('error');
        $this->assertDatabaseHas('usuarios', ['id' => $this->superadmin->id]);

        $this->put("/panel/usuarios/{$this->superadmin->id}/rol", ['rol' => 'admin'])
            ->assertRedirect()
            ->assertSessionHas('error');
        $this->assertSame('superadmin', $this->superadmin->fresh()->rol);

        // El rol superadmin es único: no se puede crear otro ni promover a otro usuario
        $this->post('/panel/usuarios', [
            'name' => 'Otro Super',
            'email' => 'otro.super@alpha.test',
            'password' => 'Segura123!',
            'rol' => 'superadmin',
        ])->assertSessionHasErrors('rol');

        $this->put("/panel/usuarios/{$this->admin1->id}/rol", ['rol' => 'superadmin'])
            ->assertSessionHasErrors('rol');
        $this->assertSame('admin', $this->admin1->fresh()->rol);

        // El superadmin aún puede actualizar sus propios datos manteniendo su rol único
        $this->put("/panel/usuarios/{$this->superadmin->id}", [
            'name' => 'Super Admin Actualizado',
            'email' => $this->superadmin->email,
            'rol' => 'superadmin',
        ])->assertSessionHasNoErrors();
        $this->assertSame('Super Admin Actualizado', $this->superadmin->fresh()->name);
    }

    public function test_multi_tenant_idor_protection_on_all_catalog_and_agreement_controllers(): void
    {
        $aseguradora = Aseguradora::create([
            'empresa_id' => $this->empresa1->id,
            'nombre' => 'Aseguradora Alpha',
        ]);
        $servicioCat = CatalogoServicio::create([
            'empresa_id' => $this->empresa1->id,
            'nombre' => 'Plataforma Alpha',
            'activo' => true,
        ]);
        $convenio = Convenio::create([
            'empresa_id' => $this->empresa1->id,
            'aseguradora_id' => $aseguradora->id,
            'tipo_servicio_id' => $servicioCat->id,
            'nombre_convenio_poliza' => 'Convenio Alpha',
            'tipo_ruta' => 'local',
            'tipo_cobertura' => 'Amplia',
            'alcance_geografico' => 'CDMX',
            'costo_banderazo' => 800,
            'costo_km' => 30,
            'estatus' => 'vigente',
        ]);
        $tarifa = ConvenioTarifa::create([
            'convenio_id' => $convenio->id,
            'servicio_id' => $servicioCat->id,
            'servicio' => 'Plataforma Alpha',
            'banderazo' => 800,
            'km_incluidos' => 10,
            'costo_km_extra' => 30,
        ]);
        $maniobra = ConvenioManiobrasEspeciale::create([
            'convenio_id' => $convenio->id,
            'concepto' => 'Sótano',
            'costo' => 500,
        ]);
        $concepto = ConvenioConceptoAdicional::create([
            'convenio_id' => $convenio->id,
            'cubre_casetas' => true,
        ]);
        $sla = ConvenioSla::create([
            'convenio_id' => $convenio->id,
            'tiempo_max_respuesta_urbano_min' => 30,
            'tiempo_max_respuesta_carretera_min' => 60,
        ]);

        // Admin de Empresa 2 intenta acceder/borrar recursos de Empresa 1 -> 404
        $this->actingAs($this->admin2);

        $this->get("/panel/convenio-tarifas/{$tarifa->id}/edit")->assertNotFound();
        $this->delete("/panel/convenio-tarifas/{$tarifa->id}")->assertNotFound();

        $this->get("/panel/servicios-especiales/{$maniobra->id}/edit")->assertNotFound();
        $this->delete("/panel/servicios-especiales/{$maniobra->id}")->assertNotFound();

        $this->get("/panel/conceptos-adicionales/{$concepto->id}/edit")->assertNotFound();
        $this->delete("/panel/conceptos-adicionales/{$concepto->id}")->assertNotFound();

        $this->get("/panel/penalizaciones/{$sla->id}/edit")->assertNotFound();
        $this->delete("/panel/penalizaciones/{$sla->id}")->assertNotFound();

        $this->get("/panel/tipos-servicio/{$servicioCat->id}/edit")->assertNotFound();
        $this->delete("/panel/tipos-servicio/{$servicioCat->id}")->assertNotFound();

        // Todos los registros de Empresa 1 siguen intactos
        $this->assertDatabaseHas('convenio_tarifas', ['id' => $tarifa->id]);
        $this->assertDatabaseHas('convenio_maniobras_especiales', ['id' => $maniobra->id]);
        $this->assertDatabaseHas('convenio_conceptos_adicionales', ['id' => $concepto->id]);
        $this->assertDatabaseHas('convenio_sla', ['id' => $sla->id]);
        $this->assertDatabaseHas('catalogo_servicios', ['id' => $servicioCat->id]);
    }

    public function test_role_isolation_and_no_null_cliente_leak(): void
    {
        $operadorUser = Usuario::factory()->create([
            'empresa_id' => $this->empresa1->id,
            'rol' => 'operador',
        ]);
        $clienteSinPerfil = Usuario::factory()->create([
            'empresa_id' => $this->empresa1->id,
            'rol' => 'cliente',
        ]);

        $servicioCat = CatalogoServicio::create([
            'empresa_id' => $this->empresa1->id,
            'nombre' => 'Arrastre',
            'activo' => true,
        ]);
        $clienteReal = Cliente::create([
            'empresa_id' => $this->empresa1->id,
            'nombre' => 'Cliente Real',
            'tipo_cliente' => 'persona_fisica',
            'nacionalidad' => 'Mexicana',
        ]);
        $cot = Cotizacione::create([
            'empresa_id' => $this->empresa1->id,
            'cliente_id' => $clienteReal->id,
            'tipo_servicio_id' => $servicioCat->id,
            'usuario_creador_id' => $this->admin1->id,
            'folio' => 'COT-00001',
            'origen_direccion' => 'Origen',
            'destino_direccion' => 'Destino',
            'distancia_km' => 15,
            'costo_banderazo' => 500,
            'costo_km' => 20,
            'costo_total' => 800,
            'estatus' => 'aprobado',
        ]);
        $empleado = Empleado::create([
            'empresa_id' => $this->empresa1->id,
            'nombre' => 'Op',
            'apellido_paterno' => 'Uno',
            'apellido_materno' => 'Test',
            'sexo' => 'M',
            'curp' => 'OPUN900101HDFRRN01',
            'fecha_nacimiento' => '1990-01-01',
            'telefono' => '5511112222',
            'puesto' => 'operador',
        ]);
        $operador = Operadore::create([
            'empresa_id' => $this->empresa1->id,
            'empleado_id' => $empleado->id,
            'numero_licencia' => 'LIC-001',
            'tipo_licencia' => 'Federal',
            'fecha_expedicion' => '2025-01-01',
            'fecha_vigencia' => '2028-01-01',
            'disponible' => true,
        ]);
        $unidad = Unidade::create([
            'empresa_id' => $this->empresa1->id,
            'numero_economico' => 'G-01',
            'marca' => 'Ford',
            'modelo' => '2024',
            'placas' => 'XYZ1234',
            'tipo' => 'Plataforma',
            'activo' => true,
        ]);
        $servicio = Servicio::create([
            'empresa_id' => $this->empresa1->id,
            'cotizacion_id' => $cot->id,
            'operador_id' => $operador->id,
            'unidad_id' => $unidad->id,
            'estado' => 'finalizado',
            'costo_final_real' => 800,
        ]);
        $factura = Factura::create([
            'empresa_id' => $this->empresa1->id,
            'servicio_id' => $servicio->id,
            'cliente_id' => $clienteReal->id,
            'folio_factura' => 'FAC-00001',
            'subtotal' => 689.66,
            'iva' => 110.34,
            'total' => 800,
            'estatus' => 'vigente',
            'correo_envio_factura' => 'real@alpha.test',
        ]);

        // Operador bloqueado en rutas de cliente y descarga de PDF de factura
        $this->actingAs($operadorUser);
        $this->get("/panel/cliente/cotizaciones/{$cot->id}")->assertForbidden();
        $this->get("/panel/cliente/facturacion/{$factura->id}")->assertForbidden();
        $this->get("/panel/evaluar-servicio/{$servicio->id}")->assertForbidden();
        $this->get("/panel/facturas/{$factura->id}/pdf")->assertForbidden();

        // Cliente sin registro en tabla clientes NO ve cotizaciones/facturas ajenas
        $this->actingAs($clienteSinPerfil);
        $this->get('/panel/mis-cotizaciones')->assertOk();
        $this->get('/panel/mis-facturas')->assertOk();
        $this->get('/panel/mis-evaluaciones')->assertOk();
        $this->get('/panel/dashboard')->assertOk();
        $this->get("/panel/evaluar-servicio/{$servicio->id}")->assertNotFound();
    }

    public function test_security_headers_mandatory_password_change_and_admin_lockout(): void
    {
        // Cabeceras de seguridad presentes
        $response = $this->get('/');
        $response->assertOk();
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');

        // debe_cambiar_password fuerza redirección a /cambiar-password
        $userCambio = Usuario::factory()->create([
            'empresa_id' => $this->empresa1->id,
            'rol' => 'operador',
            'debe_cambiar_password' => true,
        ]);
        $this->actingAs($userCambio)
            ->get('/panel/dashboard')
            ->assertRedirect(route('password.cambiar'));

        // Usuario bloqueado administrativamente NO puede auto-desbloquearse
        auth()->logout();
        $userBloqueado = Usuario::factory()->create([
            'empresa_id' => $this->empresa1->id,
            'rol' => 'cotizador',
            'cuenta_bloqueada' => true,
            'bloqueo_administrativo' => true,
            'codigo_desbloqueo' => '123456',
            'codigo_desbloqueo_expira' => now()->addMinutes(15),
        ]);
        $this->post('/unlock', [
            'email' => $userBloqueado->email,
            'codigo' => '123456',
        ])->assertSessionHasErrors('email');
        $this->assertTrue((bool) $userBloqueado->fresh()->cuenta_bloqueada);
    }

    public function test_factura_calculator_odometer_and_zona_especial_fallback(): void
    {
        $cot = new Cotizacione([
            'costo_banderazo' => 500,
            'costo_km' => 20,
            'km_incluidos' => 10,
            'distancia_km' => 15,
            'cargo_zona_especial' => 250,
            'descuento_pct' => 0,
            'costo_aprox_casetas' => 100,
            'incluye_peajes' => false,
        ]);

        // Odómetro: salida 10000, término 10025 => 25 km recorridos (15 km excedentes * 20 = 300)
        $servicio = new Servicio([
            'kms_salida' => 10000,
            'kms_termino_servicio' => 10025,
            'kms_cobrados_reales' => null,
            'cargo_zona_especial' => null,
        ]);
        $servicio->setRelation('cotizacion', $cot);

        $calc = (new FacturaCalculator())->calcular($servicio);

        $this->assertSame(25.0, $calc['km']);
        $this->assertSame(15.0, $calc['km_excedentes']);
        $this->assertSame(20.0, $calc['costo_km']);
        $this->assertSame(250.0, $calc['cargo_zona_especial']);
        $this->assertSame(1050.0, $calc['subtotal']);
        $this->assertSame(168.0, $calc['iva']);
        $this->assertSame(1318.0, $calc['total']);
    }

    public function test_tarifas_reportes_cancelacion_notificaciones_and_landing_flows(): void
    {
        $this->actingAs($this->superadmin);

        // 1. TarifasEmpresa guarda todos los campos extendidos
        $cat = CatalogoServicio::create([
            'empresa_id' => $this->empresa1->id,
            'nombre' => 'Grúa Pesada',
            'activo' => true,
        ]);
        $this->post('/panel/tarifas-propias', [
            'nombre_tarifa' => 'Tarifa Nocturna',
            'tipo_servicio_id' => $cat->id,
            'tipo_ruta' => 'foraneo',
            'costo_banderazo' => 1000,
            'costo_km' => 40,
            'km_incluidos' => 15,
            'tarifa_nocturna_recargo_pct' => 25,
            'descuento_pct' => 10,
            'activo' => true,
        ])->assertSessionHasNoErrors();

        $tarifaPropia = TarifasEmpresa::where('nombre_tarifa', 'Tarifa Nocturna')->first();
        $this->assertNotNull($tarifaPropia);
        $this->assertEquals(25.0, (float) $tarifaPropia->tarifa_nocturna_recargo_pct);
        $this->assertEquals(10.0, (float) $tarifaPropia->descuento_pct);

        // 2. Notificaciones: marcar como leída y borrado físico
        $notif = Notificacione::create([
            'empresa_id' => $this->empresa1->id,
            'usuario_id' => $this->superadmin->id,
            'canal' => 'sistema_push',
            'mensaje' => 'Notificación de prueba',
            'estado' => 'pendiente',
        ]);
        $this->post("/panel/notificaciones/{$notif->id}/marcar-leida")->assertSessionHasNoErrors();
        $this->assertSame('leido', $notif->fresh()->estado);
        $this->delete("/panel/notificaciones/{$notif->id}")->assertSessionHasNoErrors();
        $this->assertDatabaseMissing('notificaciones', ['id' => $notif->id]);

        // 3. Reporte de calificaciones no lanza 500 y AutorizacionesCancelacion restaura estado_previo
        $cliente = Cliente::create([
            'empresa_id' => $this->empresa1->id,
            'nombre' => 'Cliente Eval',
            'tipo_cliente' => 'persona_fisica',
            'nacionalidad' => 'Mexicana',
        ]);
        $cot = Cotizacione::create([
            'empresa_id' => $this->empresa1->id,
            'cliente_id' => $cliente->id,
            'tipo_servicio_id' => $cat->id,
            'usuario_creador_id' => $this->superadmin->id,
            'folio' => 'COT-00099',
            'origen_direccion' => 'A',
            'destino_direccion' => 'B',
            'distancia_km' => 10,
            'costo_banderazo' => 500,
            'costo_km' => 20,
            'costo_total' => 580,
            'estatus' => 'aprobado',
        ]);
        $emp = Empleado::create([
            'empresa_id' => $this->empresa1->id,
            'nombre' => 'Op2',
            'apellido_paterno' => 'Test',
            'apellido_materno' => 'Dos',
            'sexo' => 'M',
            'curp' => 'OPDO900101HDFRRN02',
            'fecha_nacimiento' => '1990-01-01',
            'telefono' => '5522223333',
            'puesto' => 'operador',
        ]);
        $op = Operadore::create([
            'empresa_id' => $this->empresa1->id,
            'empleado_id' => $emp->id,
            'numero_licencia' => 'LIC-002',
            'tipo_licencia' => 'Federal',
            'fecha_expedicion' => '2025-01-01',
            'fecha_vigencia' => '2028-01-01',
            'disponible' => false,
        ]);
        $uni = Unidade::create([
            'empresa_id' => $this->empresa1->id,
            'numero_economico' => 'G-02',
            'marca' => 'Ford',
            'modelo' => '2024',
            'placas' => 'ABC9999',
            'tipo' => 'Plataforma',
            'activo' => true,
        ]);
        $svc = Servicio::create([
            'empresa_id' => $this->empresa1->id,
            'cotizacion_id' => $cot->id,
            'operador_id' => $op->id,
            'unidad_id' => $uni->id,
            'estado' => 'solicitud_cancelacion',
            'costo_final_real' => 580,
        ]);
        CalificacionesServicio::create([
            'servicio_id' => $svc->id,
            'cliente_id' => $cliente->id,
            'estrellas' => 5,
            'comentario' => 'Excelente servicio',
        ]);
        $this->post('/panel/reportes/calificaciones', [
            'fecha_inicio' => now()->subDay()->toDateString(),
            'fecha_fin' => now()->addDay()->toDateString(),
        ])->assertRedirect()->assertSessionHas('reporte');

        $auth = AutorizacionesCancelacione::create([
            'servicio_id' => $svc->id,
            'usuario_solicitante_id' => $this->superadmin->id,
            'motivo_cancelacion' => 'Prueba rechazo',
            'tipo_incidencia' => 'otro',
            'fecha_solicitud' => now(),
            'estado_previo' => 'en_sitio_origen',
            'estatus' => 'pendiente',
        ]);
        $this->post("/panel/autorizaciones-cancelacion/{$auth->id}/rechazar")->assertSessionHasNoErrors();
        $this->assertSame('rechazada', $auth->fresh()->estatus);
        $this->assertSame('en_sitio_origen', $svc->fresh()->estado);

        // 4. Solicitar y rastrear desde Landing (sin exponer PII)
        $this->post('/solicitar', [
            'nombre' => 'Visitante',
            'telefono' => '5599998888',
            'ubicacion' => 'Periférico Sur Km 12',
            'tipo_servicio' => 'Grúa Ligera',
            'descripcion' => 'Vehículo varado',
        ])->assertRedirect()->assertSessionHas('success');

        $folio = 'SVC-' . str_pad((string) $svc->id, 5, '0', STR_PAD_LEFT);
        $this->get("/rastrear?folio={$folio}")->assertOk();

        // La columna eliminado ya NO existe (100% borrado físico)
        $this->assertFalse(Schema::hasColumn('usuarios', 'eliminado'));
        $this->assertFalse(Schema::hasColumn('clientes', 'eliminado'));
    }

    public function test_database_seeder_runs_cleanly_with_foreign_keys_enabled_and_creates_superadmin(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseHas('usuarios', [
            'email' => 'superadmin@sigesga.com',
            'rol' => 'superadmin',
        ]);
        $this->assertDatabaseHas('usuarios', [
            'email' => 'admin@sigesga.com',
            'rol' => 'admin',
        ]);
    }
}

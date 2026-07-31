<?php

namespace Tests\Feature;

use App\Models\Aseguradora;
use App\Models\CatalogoServicio;
use App\Models\Cliente;
use App\Models\Convenio;
use App\Models\Cotizacione;
use App\Models\Empleado;
use App\Models\Oficina;
use App\Models\Operadore;
use App\Models\Servicio;
use App\Models\TarifasEmpresa;
use App\Models\Unidade;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class PanelSmokeTest extends TestCase
{
    use DatabaseMigrations;

    private Usuario $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = Usuario::factory()->create();
    }

    public function test_all_module_index_pages_load(): void
    {
        $modules = [
            'panel/dashboard',
            'panel/empleados',
            'panel/empleados/create',
            'panel/oficinas',
            'panel/oficinas/create',
            'panel/operadores',
            'panel/operadores/create',
            'panel/unidades',
            'panel/unidades/create',
            'panel/mantenimientos',
            'panel/mantenimientos/create',
            'panel/clientes',
            'panel/clientes/create',
            'panel/convenios',
            'panel/convenios/create',
            'panel/cotizaciones',
            'panel/cotizaciones/create',
            'panel/servicios',
            'panel/servicios/create',
            'panel/aseguradoras',
            'panel/aseguradoras/create',
            'panel/tarifas-propias',
            'panel/tarifas-propias/create',
            'panel/tipos-servicio',
            'panel/usuarios',
            'panel/usuarios/create',
            'panel/configuracion',
            'panel/integraciones',
            'panel/reportes',
            'panel/notificaciones',
            'panel/facturacion',
            'panel/mi-perfil',
        ];

        foreach ($modules as $url) {
            $response = $this->actingAs($this->user)->get($url);
            $response->assertStatus(200);
        }
    }

    public function test_employee_crud(): void
    {
        $this->actingAs($this->user);

        $response = $this->post('/panel/empleados', [
            'nombre' => 'Juan',
            'apellido_paterno' => 'Pérez',
            'apellido_materno' => 'López',
            'sexo' => 'M',
            'curp' => 'PELJ900101HDFRRN01',
            'fecha_nacimiento' => '1990-01-01',
            'telefono' => '5512345678',
            'puesto' => 'Operador',
            'oficina_id' => null,
        ]);
        $response->assertSessionHasNoErrors();

        $empleado = Empleado::first();
        $this->assertNotNull($empleado);

        $this->get("/panel/empleados/{$empleado->id}")->assertStatus(200);
        $this->get("/panel/empleados/{$empleado->id}/edit")->assertStatus(200);

        $this->put("/panel/empleados/{$empleado->id}", [
            'nombre' => 'Juan',
            'apellido_paterno' => 'Pérez',
            'telefono' => '5598765432',
            'puesto' => 'Operador Senior',
        ])->assertSessionHasNoErrors();

        $this->delete("/panel/empleados/{$empleado->id}")->assertSessionHasNoErrors();
    }

    public function test_aseguradora_crud(): void
    {
        $this->actingAs($this->user);

        $this->post('/panel/aseguradoras', [
            'empresa_id' => 1,
            'nombre' => 'Seguros Prueba',
            'telefono' => '5512345678',
            'email' => 'contacto@segurosprueba.com',
        ])->assertSessionHasNoErrors();

        $aseguradora = Aseguradora::where('nombre', 'Seguros Prueba')->first();
        $this->assertNotNull($aseguradora);

        $this->get("/panel/aseguradoras/{$aseguradora->id}")->assertStatus(200);
        $this->get("/panel/aseguradoras/{$aseguradora->id}/edit")->assertStatus(200);

        $this->put("/panel/aseguradoras/{$aseguradora->id}", [
            'nombre' => 'Seguros Prueba',
            'telefono' => '5598765432',
        ])->assertSessionHasNoErrors();

        $this->delete("/panel/aseguradoras/{$aseguradora->id}")->assertSessionHasNoErrors();
    }

    public function test_cliente_crud(): void
    {
        $this->actingAs($this->user);

        $this->post('/panel/clientes', [
            'empresa_id' => 1,
            'nombre' => 'Cliente Prueba',
            'email' => 'cliente@prueba.com',
            'telefono' => '5512345678',
        ])->assertSessionHasNoErrors();

        $cliente = Cliente::where('nombre', 'Cliente Prueba')->first();
        $this->assertNotNull($cliente);

        $this->get("/panel/clientes/{$cliente->id}")->assertStatus(200);
        $this->get("/panel/clientes/{$cliente->id}/edit")->assertStatus(200);
    }

    public function test_unidad_crud(): void
    {
        $this->actingAs($this->user);

        $this->post('/panel/unidades', [
            'empresa_id' => 1,
            'numero_economico' => 'UNI-001',
            'marca' => 'Ford',
            'modelo' => '2024',
            'tipo' => 'Grua Ligera',
            'serie' => 'SERIE123456',
            'placas' => 'ABC-1234',
            'numero_motor' => 'MOTOR123',
        ])->assertSessionHasNoErrors();

        $unidad = Unidade::where('numero_economico', 'UNI-001')->first();
        $this->assertNotNull($unidad);

        $this->get("/panel/unidades/{$unidad->id}")->assertStatus(200);
        $this->get("/panel/unidades/{$unidad->id}/edit")->assertStatus(200);
    }
}

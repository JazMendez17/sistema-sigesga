<?php

// Agrega la columna `eliminado` a las tablas del sistema.
// Los registros nunca se borran de la BD: al eliminarlos se marcan con
// `eliminado = true` y quedan ocultos del sistema (scope Ocultable).

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $tablas = [
        'empresas',
        'empleados',
        'usuarios',
        'unidades',
        'oficinas',
        'operadores',
        'catalogo_servicios',
        'aseguradoras',
        'aseguradora_contactos',
        'clientes',
        'convenios',
        'tarifas_empresa',
        'servicios',
        'cotizaciones',
        'facturas',
        'notificaciones',
        'convenio_tarifas',
        'convenio_conceptos_adicionales',
        'convenio_maniobras_especiales',
        'convenio_sla',
        'unidad_mantenimientos',
        'empresa_valores',
        'empresa_servicios',
    ];

    public function up(): void
    {
        foreach ($this->tablas as $tabla) {
            Schema::table($tabla, function (Blueprint $table) {
                $table->boolean('eliminado')->default(false);
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tablas as $tabla) {
            Schema::table($tabla, function (Blueprint $table) {
                $table->dropColumn('eliminado');
            });
        }
    }
};
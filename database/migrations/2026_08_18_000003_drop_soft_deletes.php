<?php

// Elimina el borrado lógico (soft deletes) del esquema y crea la tabla de
// auditoría de eliminaciones. Por política (legales/auditoría) los borrados
// son físicos en las tablas operativas, pero cada registro eliminado queda
// respaldado en `auditoria_eliminaciones` para su trazabilidad.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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
    ];

    public function up(): void
    {
        Schema::create('auditoria_eliminaciones', function (Blueprint $table) {
            $table->id();
            $table->string('modelo', 100)->index();
            $table->unsignedBigInteger('registro_id')->nullable()->index();
            $table->json('datos');
            $table->unsignedBigInteger('usuario_id')->nullable();
            $table->timestamp('created_at')->nullable();
        });

        foreach ($this->tablas as $tabla) {
            $eliminados = DB::table($tabla)->whereNotNull('deleted_at')->get();

            foreach ($eliminados as $fila) {
                DB::table('auditoria_eliminaciones')->insert([
                    'modelo' => $tabla,
                    'registro_id' => $fila->id ?? null,
                    'datos' => json_encode((array) $fila, JSON_UNESCAPED_UNICODE),
                    'usuario_id' => null,
                    'created_at' => now(),
                ]);
            }

            DB::table($tabla)->whereNotNull('deleted_at')->delete();

            Schema::table($tabla, function (Blueprint $table) {
                $table->dropColumn('deleted_at');
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tablas as $tabla) {
            Schema::table($tabla, function (Blueprint $table) {
                $table->softDeletes();
            });
        }
    }
};
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Seeder para estandarizar el catálogo de servicios a solo 4 registros oficiales
class CatalogoServicioSeeder extends Seeder
{
    public function run(): void
    {
        // Desactivar restricciones de llaves foráneas temporalmente
        Schema::disableForeignKeyConstraints();

        // Mapa de reasignación: todos los IDs antiguos se mapean al ID estandarizado
        $this->reassignForeignKeys();

        // Limpiar tabla completamente
        DB::table('catalogo_servicios')->truncate();

        // Insertar los 4 registros estandarizados
        $servicios = [
            ['id' => 1, 'empresa_id' => 1, 'nombre' => 'Arrastre con gancho',              'requiere_maniobra' => false, 'activo' => true, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'empresa_id' => 1, 'nombre' => 'Arrastre en plataforma hidráulica', 'requiere_maniobra' => false, 'activo' => true, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'empresa_id' => 1, 'nombre' => 'Asistencia vial',                   'requiere_maniobra' => false, 'activo' => true, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 4, 'empresa_id' => 1, 'nombre' => 'Rescate',                           'requiere_maniobra' => true,  'activo' => true, 'created_at' => now(), 'updated_at' => now()],
        ];

        DB::table('catalogo_servicios')->insert($servicios);

        // Reactivar restricciones de llaves foráneas
        Schema::enableForeignKeyConstraints();

        $this->command->info('Catálogo de servicios estandarizado: 4 registros oficiales.');
    }

    protected function reassignForeignKeys(): void
    {
        // Mapa de nombres a IDs estandarizados
        $map = [
            'Arrastre con gancho' => 1,
            'Arrastre en plataforma hidráulica' => 2,
            'Arrastre en plataforma' => 2,
            'Asistencia vial básica' => 3,
            'Asistencia vial' => 3,
            'Rescate' => 4,
            'Rescate / Salvlamento especial' => 4,
            'Rescate / Salvamento especial' => 4,
            'Servicio de Grúa Ligera' => 1,
            'Servicio de Grúa Pesada' => 2,
        ];

        $servicios = DB::table('catalogo_servicios')->get();

        foreach ($servicios as $servicio) {
            $nombre = $servicio->nombre;
            $oldId = $servicio->id;
            $newId = $map[$nombre] ?? null;

            if (!$newId) continue;
            if ($oldId == $newId) continue;

            // Reasignar llaves foráneas en tablas dependientes
            $this->updateForeignKey('cotizaciones', 'tipo_servicio_id', $oldId, $newId);
            $this->updateForeignKey('convenios', 'tipo_servicio_id', $oldId, $newId);
            $this->updateForeignKey('convenio_tarifas', 'servicio_id', $oldId, $newId);
            $this->updateForeignKey('tarifas_empresa', 'tipo_servicio_id', $oldId, $newId);
        }

        // Las tarifas que usaban la versión soft-deleted (id 1,2,3) ya fueron reasignadas.
        // Ahora todos los registros deben apuntar a IDs del 1 al 4.
    }

    protected function updateForeignKey(string $table, string $column, int $oldId, int $newId): void
    {
        if (!Schema::hasColumn($table, $column)) return;

        DB::table($table)
            ->where($column, $oldId)
            ->update([$column => $newId]);
    }
}

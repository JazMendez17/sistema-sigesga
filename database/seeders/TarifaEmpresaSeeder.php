<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

// Seeder para inyectar 8 tarifas propias (4 servicios x 2 rutas)
class TarifaEmpresaSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        $tarifas = [
            // Arrastre con gancho (servicio 1)
            [1, 'Tarifa Pública Local',   'local',   550.00, 18.00, 30.00, false, true],
            [1, 'Tarifa Pública Foránea', 'foraneo', 800.00, 22.00, 30.00, true,  true],
            // Arrastre en plataforma hidráulica (servicio 2)
            [2, 'Tarifa Pública Local',   'local',   720.00, 22.00, 30.00, false, true],
            [2, 'Tarifa Pública Foránea', 'foraneo', 1050.00, 26.00, 30.00, true,  true],
            // Asistencia vial (servicio 3)
            [3, 'Tarifa Pública Local',   'local',   430.00, 0.00,  0.00,  false, true],
            [3, 'Tarifa Pública Foránea', 'foraneo', 640.00, 18.00, 15.00, true,  true],
            // Rescate (servicio 4)
            [4, 'Tarifa Pública Local',   'local',   1200.00, 28.00, 25.00, false, true],
            [4, 'Tarifa Pública Foránea', 'foraneo', 1650.00, 35.00, 25.00, true,  true],
        ];

        foreach ($tarifas as $t) {
            DB::table('tarifas_empresa')->updateOrInsert(
                ['tipo_servicio_id' => $t[0], 'tipo_ruta' => $t[2]],
                [
                    'empresa_id' => DB::table('empresas')->value('id') ?? 1,
                    'nombre_tarifa' => $t[1],
                    'costo_banderazo' => $t[3],
                    'costo_km' => $t[4],
                    'km_incluidos' => $t[5],
                    'cubre_casetas_peaje' => $t[6],
                    'activo' => $t[7],
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->command->info('TarifaEmpresaSeeder: 8 tarifas propias sincronizadas correctamente.');
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

// Seeder para inyectar los SLA / Penalizaciones de los 7 convenios
class ConvenioSlaSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        $slas = [
            [1, 30, 75,  '24/7',            '3% de descuento sobre el servicio por cada 15 min de retraso', 'Asignación Directa por Cabina'],
            [2, 40, 90,  '24/7',            '5% de descuento sobre el servicio por cada 15 min de retraso', 'Por Cercanía GPS'],
            [3, 35, 85,  '24/7',            '4% de descuento sobre el servicio por cada 15 min de retraso', 'Vía Sistema Automático'],
            [4, 45, 95,  'Lun-Dom 6:00-24:00', '2% de descuento sobre el servicio por cada 15 min de retraso', 'Asignación Directa por Cabina'],
            [5, 50, 100, 'Lun-Dom 6:00-24:00', '2% de descuento sobre el servicio por cada 15 min de retraso', 'Manual vía Telefónica'],
            [6, 25, 70,  '24/7',            '6% de descuento sobre el servicio por cada 15 min de retraso', 'Por Cercanía GPS'],
            [7, 35, 80,  '24/7',            '4% de descuento sobre el servicio por cada 15 min de retraso', 'Vía Cabina de Siniestros'],
        ];

        foreach ($slas as $s) {
            DB::table('convenio_sla')->updateOrInsert(
                ['convenio_id' => $s[0]],
                [
                    'tiempo_max_respuesta_urbano_min' => $s[1],
                    'tiempo_max_respuesta_carretera_min' => $s[2],
                    'disponibilidad' => $s[3],
                    'penalizacion_incumplimiento' => $s[4],
                    'protocolo_asignacion' => $s[5],
                ]
            );
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->command->info('ConvenioSlaSeeder: 7 registros SLA sincronizados correctamente.');
    }
}

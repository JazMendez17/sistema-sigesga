<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

// Seeder de conceptos adicionales para los 7 convenios (casetas, estancia, resguardo)
class ConvenioConceptoAdicionalSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        $conceptos = [
            [1, true,  'Incluido en tarifa',       175.00, 1, 230.00, false],
            [2, false, 'Reembolso contra factura', 195.00, 2, 260.00, true],
            [3, true,  'Reembolso contra factura', 205.00, 2, 280.00, false],
            [4, false, 'Reembolso contra factura', 165.00, 1, 220.00, false],
            [5, false, 'Reembolso contra factura', 140.00, 3, 190.00, true],
            [6, true,  'Incluido en tarifa',       225.00, 1, 310.00, false],
            [7, true,  'Reembolso contra factura', 210.00, 2, 290.00, false],
        ];

        foreach ($conceptos as $c) {
            DB::table('convenio_conceptos_adicionales')->updateOrInsert(
                ['convenio_id' => $c[0]],
                [
                    'cubre_casetas' => $c[1],
                    'forma_pago_casetas' => $c[2],
                    'costo_estadia_dia' => $c[3],
                    'dias_gracia_estadia' => $c[4],
                    'costo_resguardo_nocturno' => $c[5],
                    'genera_cargo_cliente_final' => $c[6],
                ]
            );
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->command->info('Conceptos adicionales: 7 registros sincronizados.');
    }
}

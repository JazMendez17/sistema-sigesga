<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

// Seeder para estandarizar los 7 convenios del sistema con datos completos
class ConvenioSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        $convenios = [
            [
                'id' => 1, 'aseguradora_id' => 1, 'codigo_convenio' => 'CNV-2026-QUA-01',
                'nombre_convenio_poliza' => 'Convenio Quálitas',
                'fecha_inicio' => '2026-01-01', 'fecha_fin' => '2026-12-31',
                'renovacion_automatica' => true, 'exclusivo' => false,
                'dias_credito' => 15, 'periodicidad_corte' => 'semanal',
                'requiere_folio_cfdi' => true, 'iva_incluido' => false,
                'tope_credito' => 90000.00, 'aviso_previo_terminacion_dias' => 30,
            ],
            [
                'id' => 2, 'aseguradora_id' => 2, 'codigo_convenio' => 'CNV-2026-GNP-02',
                'nombre_convenio_poliza' => 'Convenio GNP',
                'fecha_inicio' => '2026-02-01', 'fecha_fin' => '2027-01-31',
                'renovacion_automatica' => false, 'exclusivo' => true,
                'dias_credito' => 30, 'periodicidad_corte' => 'quincenal',
                'requiere_folio_cfdi' => true, 'iva_incluido' => true,
                'tope_credito' => 60000.00, 'aviso_previo_terminacion_dias' => 45,
            ],
            [
                'id' => 3, 'aseguradora_id' => 3, 'codigo_convenio' => 'CNV-2026-AXA-03',
                'nombre_convenio_poliza' => 'Convenio AXA',
                'fecha_inicio' => '2026-01-15', 'fecha_fin' => '2026-12-31',
                'renovacion_automatica' => true, 'exclusivo' => false,
                'dias_credito' => 30, 'periodicidad_corte' => 'mensual',
                'requiere_folio_cfdi' => true, 'iva_incluido' => false,
                'tope_credito' => 120000.00, 'aviso_previo_terminacion_dias' => 60,
            ],
            [
                'id' => 4, 'aseguradora_id' => 4, 'codigo_convenio' => 'CNV-2026-MAP-04',
                'nombre_convenio_poliza' => 'Convenio Mapfre',
                'fecha_inicio' => '2026-03-01', 'fecha_fin' => '2027-02-28',
                'renovacion_automatica' => false, 'exclusivo' => false,
                'dias_credito' => 15, 'periodicidad_corte' => 'semanal',
                'requiere_folio_cfdi' => true, 'iva_incluido' => false,
                'tope_credito' => 50000.00, 'aviso_previo_terminacion_dias' => 30,
            ],
            [
                'id' => 5, 'aseguradora_id' => 5, 'codigo_convenio' => 'CNV-2026-ATL-05',
                'nombre_convenio_poliza' => 'Convenio Atlas',
                'fecha_inicio' => '2026-01-01', 'fecha_fin' => '2026-06-30',
                'renovacion_automatica' => false, 'exclusivo' => false,
                'dias_credito' => 45, 'periodicidad_corte' => 'mensual',
                'requiere_folio_cfdi' => true, 'iva_incluido' => false,
                'tope_credito' => 35000.00, 'aviso_previo_terminacion_dias' => 30,
            ],
            [
                'id' => 6, 'aseguradora_id' => 6, 'codigo_convenio' => 'CNV-2026-BBV-06',
                'nombre_convenio_poliza' => 'Convenio BBVA',
                'fecha_inicio' => '2026-04-01', 'fecha_fin' => '2027-03-31',
                'renovacion_automatica' => true, 'exclusivo' => true,
                'dias_credito' => 20, 'periodicidad_corte' => 'quincenal',
                'requiere_folio_cfdi' => true, 'iva_incluido' => true,
                'tope_credito' => 150000.00, 'aviso_previo_terminacion_dias' => 60,
            ],
            [
                'id' => 7, 'aseguradora_id' => 7, 'codigo_convenio' => 'CNV-2026-ALZ-07',
                'nombre_convenio_poliza' => 'Convenio Alianza',
                'fecha_inicio' => '2026-01-01', 'fecha_fin' => '2026-12-31',
                'renovacion_automatica' => false, 'exclusivo' => false,
                'dias_credito' => 30, 'periodicidad_corte' => 'mensual',
                'requiere_folio_cfdi' => true, 'iva_incluido' => false,
                'tope_credito' => 100000.00, 'aviso_previo_terminacion_dias' => 45,
            ],
        ];

        foreach ($convenios as $data) {
            $id = $data['id'];
            unset($data['id']);

            DB::table('convenios')->updateOrInsert(
                ['id' => $id],
                array_merge($data, ['empresa_id' => 1, 'updated_at' => now(), 'created_at' => $data['created_at'] ?? now()])
            );
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->command->info('Convenios estandarizados: 7 registros actualizados correctamente.');
    }
}

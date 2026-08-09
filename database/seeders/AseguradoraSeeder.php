<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

// Seeder para estandarizar las 7 aseguradoras y sus contactos por departamento
class AseguradoraSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        $empresaId = DB::table('empresas')->value('id') ?? 1;

        $aseguradoras = [
            [
                'id' => 1, 'nombre' => 'Quálitas Compañía de Seguros, S.A.B. de C.V.',
                'nombre_comercial' => 'Quálitas', 'rfc' => 'QCS931209T18', 'telefono' => '8008002880',
                'contactos' => [
                    ['departamento' => 'siniestros', 'nombre_contacto' => 'Lic. Carlos Mendoza', 'telefono' => '5557237900', 'email' => 'atencion.siniestros@qualitas.com.mx'],
                    ['departamento' => 'asistencia_vial', 'nombre_contacto' => 'Ing. Roberto Gómez', 'telefono' => '5557237915', 'email' => 'cabina.gruas@qualitas.com.mx'],
                ],
            ],
            [
                'id' => 2, 'nombre' => 'Grupo Nacional Provincial, S.A.B.',
                'nombre_comercial' => 'GNP Seguros', 'rfc' => 'GNP9211244R0', 'telefono' => '5552279000',
                'contactos' => [
                    ['departamento' => 'siniestros', 'nombre_contacto' => 'Dra. Sofía Ramírez', 'telefono' => '5552273131', 'email' => 'cabina.mantenimiento@gnp.com.mx'],
                    ['departamento' => 'asistencia_vial', 'nombre_contacto' => 'Lic. Fernando Ríos', 'telefono' => '5552273140', 'email' => 'proveedores.gruas@gnp.com.mx'],
                ],
            ],
            [
                'id' => 3, 'nombre' => 'AXA Seguros, S.A. de C.V.',
                'nombre_comercial' => 'AXA', 'rfc' => 'ASE931116231', 'telefono' => '8009001292',
                'contactos' => [
                    ['departamento' => 'siniestros', 'nombre_contacto' => 'Lic. Alejandro Silva', 'telefono' => '5551691000', 'email' => 'siniestros.vial@axa.com.mx'],
                    ['departamento' => 'asistencia_vial', 'nombre_contacto' => 'Ing. Mariana Torres', 'telefono' => '5551691050', 'email' => 'proveedores.asistencia@axa.com.mx'],
                ],
            ],
            [
                'id' => 4, 'nombre' => 'MAPFRE México, S.A.',
                'nombre_comercial' => 'MAPFRE', 'rfc' => 'MME920427848', 'telefono' => '8008498400',
                'contactos' => [
                    ['departamento' => 'siniestros', 'nombre_contacto' => 'Lic. Gabriel Castro', 'telefono' => '5552307000', 'email' => 'cabina.mapfre@mapfre.com.mx'],
                    ['departamento' => 'asistencia_vial', 'nombre_contacto' => 'Lic. Valeria Núñez', 'telefono' => '5552307080', 'email' => 'gestion.gruas@mapfre.com.mx'],
                ],
            ],
            [
                'id' => 5, 'nombre' => 'Seguros Atlas, S.A.',
                'nombre_comercial' => 'Atlas', 'rfc' => 'SAT8411202H8', 'telefono' => '8008490000',
                'contactos' => [
                    ['departamento' => 'siniestros', 'nombre_contacto' => 'Lic. Jorge Morales', 'telefono' => '5591775000', 'email' => 'siniestros.cabina@segurosatlas.com.mx'],
                    ['departamento' => 'asistencia_vial', 'nombre_contacto' => 'Ing. Patricia Vega', 'telefono' => '5591775020', 'email' => 'asistencia.vial@segurosatlas.com.mx'],
                ],
            ],
            [
                'id' => 6, 'nombre' => 'BBVA Seguros México, S.A. de C.V.',
                'nombre_comercial' => 'BBVA Seguros', 'rfc' => 'BSM0003181W2', 'telefono' => '8008743683',
                'contactos' => [
                    ['departamento' => 'siniestros', 'nombre_contacto' => 'Lic. Ricardo Herrera', 'telefono' => '5511020000', 'email' => 'reportes.siniestros@bbva.com'],
                    ['departamento' => 'asistencia_vial', 'nombre_contacto' => 'Lic. Claudia Méndez', 'telefono' => '5511020050', 'email' => 'red.proveedores@bbva.com'],
                ],
            ],
            [
                'id' => 7, 'nombre' => 'Allianz México, S.A. Compañía de Seguros',
                'nombre_comercial' => 'Alianza', 'rfc' => 'AME6512026A8', 'telefono' => '8001111400',
                'contactos' => [
                    ['departamento' => 'siniestros', 'nombre_contacto' => 'Lic. Daniel Ortega', 'telefono' => '5552013000', 'email' => 'cabina.allianz@allianz.com.mx'],
                    ['departamento' => 'asistencia_vial', 'nombre_contacto' => 'Ing. Mónica Delgado', 'telefono' => '5552013040', 'email' => 'asistencia.gruas@allianz.com.mx'],
                ],
            ],
        ];

        foreach ($aseguradoras as $aseg) {
            $id = $aseg['id'];
            $contactos = $aseg['contactos'];
            unset($aseg['id'], $aseg['contactos']);

            DB::table('aseguradoras')->updateOrInsert(
                ['id' => $id],
                array_merge($aseg, ['empresa_id' => $empresaId, 'updated_at' => now(), 'created_at' => now()])
            );

            // Sincronizar contactos: eliminar existentes y crear los nuevos
            DB::table('aseguradora_contactos')->where('aseguradora_id', $id)->delete();
            foreach ($contactos as $c) {
                DB::table('aseguradora_contactos')->insert(array_merge($c, [
                    'aseguradora_id' => $id,
                    'activo' => true,
                ]));
            }
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->command->info('Aseguradoras: 7 registros con 14 contactos sincronizados correctamente.');
    }
}

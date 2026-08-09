<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

// Seeder de 5 clientes de prueba con direcciones y aseguradoras vinculadas
class ClienteSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        $empresaId = DB::table('empresas')->value('id') ?? 1;

        $clientes = [
            [
                'nombre' => 'Juan Carlos', 'apellido_paterno' => 'Morales', 'apellido_materno' => 'Delgado',
                'tipo_cliente' => 'persona_fisica', 'sexo' => 'M', 'curp' => 'MODJ850615HDFRRL01',
                'fecha_nacimiento' => '1985-06-15', 'telefono' => '5512345678', 'email' => 'juan.morales@gmail.com',
                'folio_ine' => 'INE12984920', 'nacionalidad' => 'Mexicana',
                'aseguradora_id' => 1, 'numero_poliza' => 'POL-QUA-98213', 'tipo_cobertura_poliza' => 'Cobertura Amplia',
                'direccion' => ['calle' => 'Av. Insurgentes Sur', 'numero_exterior' => '452', 'colonia' => 'Roma Norte',
                    'codigo_postal' => '06700', 'municipio_alcaldia' => 'Cuauhtémoc', 'ciudad' => 'CDMX',
                    'estado' => 'Ciudad de México', 'pais' => 'México'],
            ],
            [
                'nombre' => 'María Elena', 'apellido_paterno' => 'Hernández', 'apellido_materno' => 'Gómez',
                'tipo_cliente' => 'persona_fisica', 'sexo' => 'F', 'curp' => 'HEGM920310MDFRRN04',
                'fecha_nacimiento' => '1992-03-10', 'telefono' => '5598765432', 'email' => 'maria.hernandez@hotmail.com',
                'folio_ine' => 'INE99238411', 'nacionalidad' => 'Mexicana',
                'aseguradora_id' => 2, 'numero_poliza' => 'POL-GNP-55412', 'tipo_cobertura_poliza' => 'Cobertura Limitada',
                'direccion' => ['calle' => 'Benito Juárez', 'numero_exterior' => '12', 'colonia' => 'Centro',
                    'codigo_postal' => '50000', 'municipio_alcaldia' => 'Toluca', 'ciudad' => 'Toluca',
                    'estado' => 'Estado de México', 'pais' => 'México'],
            ],
            [
                'nombre' => 'Roberto', 'apellido_paterno' => 'Alarcón', 'apellido_materno' => 'Trejo',
                'tipo_cliente' => 'persona_fisica', 'sexo' => 'M', 'curp' => 'AATR781120HDFRNS09',
                'fecha_nacimiento' => '1978-11-20', 'telefono' => '5544332211', 'email' => 'roberto.alarcon@outlook.com',
                'folio_ine' => 'INE44321098', 'nacionalidad' => 'Mexicana',
                'aseguradora_id' => 3, 'numero_poliza' => 'POL-AXA-11209', 'tipo_cobertura_poliza' => 'Cobertura Amplia',
                'direccion' => ['calle' => 'Av. Paseo de la Reforma', 'numero_exterior' => '105', 'numero_interior' => '4',
                    'colonia' => 'Juárez', 'codigo_postal' => '06600', 'municipio_alcaldia' => 'Cuauhtémoc',
                    'ciudad' => 'CDMX', 'estado' => 'Ciudad de México', 'pais' => 'México'],
            ],
            [
                'nombre' => 'Patricia', 'apellido_paterno' => 'Torres', 'apellido_materno' => 'Méndez',
                'tipo_cliente' => 'persona_fisica', 'sexo' => 'F', 'curp' => 'TOMP880405MDFRRN02',
                'fecha_nacimiento' => '1988-04-05', 'telefono' => '5566778899', 'email' => 'patricia.torres@yahoo.com',
                'folio_ine' => 'INE88123094', 'nacionalidad' => 'Mexicana',
                'aseguradora_id' => null, 'numero_poliza' => null, 'tipo_cobertura_poliza' => 'Particular',
                'direccion' => ['calle' => 'José María Morelos', 'numero_exterior' => '88', 'colonia' => 'San Ángel',
                    'codigo_postal' => '01000', 'municipio_alcaldia' => 'Álvaro Obregón', 'ciudad' => 'CDMX',
                    'estado' => 'Ciudad de México', 'pais' => 'México'],
            ],
            [
                'nombre' => 'Transmisiones y Logística S.A. de C.V.', 'apellido_paterno' => null, 'apellido_materno' => null,
                'tipo_cliente' => 'persona_moral', 'sexo' => null, 'curp' => null, 'fecha_nacimiento' => null,
                'telefono' => '5511223344', 'email' => 'contacto@translogistica.com',
                'contacto_enlace' => 'Ing. Fernando Castro', 'nacionalidad' => 'Mexicana',
                'aseguradora_id' => 6, 'numero_poliza' => 'POL-BBV-88321', 'tipo_cobertura_poliza' => 'Flotilla Comercial',
                'direccion' => ['calle' => 'Calzada de Tlalpan', 'numero_exterior' => '1200', 'colonia' => 'Portales Sur',
                    'codigo_postal' => '03300', 'municipio_alcaldia' => 'Benito Juárez', 'ciudad' => 'CDMX',
                    'estado' => 'Ciudad de México', 'pais' => 'México'],
            ],
        ];

        foreach ($clientes as $c) {
            $direccion = $c['direccion'];
            unset($c['direccion']);

            $dirId = DB::table('direcciones')->insertGetId(array_merge($direccion, ['created_at' => now(), 'updated_at' => now()]));

            DB::table('clientes')->updateOrInsert(
                ['curp' => $c['curp']],
                array_merge($c, [
                    'empresa_id' => $empresaId,
                    'direccion_id' => $dirId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->command->info('ClienteSeeder: 5 clientes con direcciones sincronizados correctamente.');
    }
}

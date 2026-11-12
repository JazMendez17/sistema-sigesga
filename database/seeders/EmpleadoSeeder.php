<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EmpleadoSeeder extends Seeder
{
    public function run(): void
    {
        $empresaId = DB::table('empresas')->first()->id;
        $oficinaId = DB::table('oficinas')->first()->id;

        $empleados = [
            [
                'empresa_id' => $empresaId,
                'oficina_id' => $oficinaId,
                'nombre' => 'Administrador',
                'apellido_paterno' => 'Admin',
                'apellido_materno' => 'Admin',
                'sexo' => 'M',
                'curp' => 'PELJ900101HDFRRN01',
                'fecha_nacimiento' => Carbon::create(1990, 1, 1)->toDateString(),
                'telefono' => '55-1234-5678',
                'correo' => 'admin@sigesga.com',
                'folio_ine' => 'INE-001',
                'nacionalidad' => 'Mexicana',
                'puesto' => 'admin',
                'sueldo_diario' => 800.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'empresa_id' => $empresaId,
                'oficina_id' => $oficinaId,
                'nombre' => 'Cotizador',
                'apellido_paterno' => 'Sistema',
                'apellido_materno' => null,
                'sexo' => 'M',
                'curp' => 'COSY900101HDFRRN00',
                'fecha_nacimiento' => Carbon::create(1990, 1, 1)->toDateString(),
                'telefono' => '55-1111-2222',
                'correo' => 'cotizador@sigesga.com',
                'folio_ine' => 'INE-004',
                'nacionalidad' => 'Mexicana',
                'puesto' => 'cotizador',
                'sueldo_diario' => 600.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'empresa_id' => $empresaId,
                'oficina_id' => $oficinaId,
                'nombre' => 'Roberto',
                'apellido_paterno' => 'Méndez',
                'apellido_materno' => 'Sánchez',
                'sexo' => 'M',
                'curp' => 'MESR880813HDFRBN03',
                'fecha_nacimiento' => Carbon::create(1988, 8, 13)->toDateString(),
                'telefono' => '55-3456-7890',
                'correo' => 'operador@sigesga.com',
                'folio_ine' => 'INE-003',
                'nacionalidad' => 'Mexicana',
                'puesto' => 'operador',
                'sueldo_diario' => 500.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($empleados as $data) {
            $exists = DB::table('empleados')
                ->where('empresa_id', $empresaId)
                ->where('puesto', $data['puesto'])
                ->first();

            if (!$exists) {
                DB::table('empleados')->insert($data);
            }
        }
    }
}

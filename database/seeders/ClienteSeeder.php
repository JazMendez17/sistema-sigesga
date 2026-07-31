<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ClienteSeeder extends Seeder
{
    public function run(): void
    {
        $empresaId = DB::table('empresas')->first()->id;
        $direccionId = DB::table('direcciones')->skip(1)->first()->id;

        DB::table('clientes')->insert([
            [
                'empresa_id' => $empresaId,
                'usuario_id' => null,
                'aseguradora_id' => null,
                'tipo_cliente' => 'persona_fisica',
                'nombre' => 'María',
                'apellido_paterno' => 'García',
                'apellido_materno' => 'Torres',
                'sexo' => 'F',
                'curp' => 'GATM850520MDFRRS02',
                'fecha_nacimiento' => Carbon::create(1985, 5, 20)->toDateString(),
                'direccion_id' => $direccionId,
                'telefono' => '55-2345-6789',
                'telefono_local' => null,
                'email' => 'maria.garcia@email.com',
                'folio_ine' => 'INE-002',
                'nacionalidad' => 'Mexicana',
                'contacto_enlace' => null,
                'numero_poliza' => null,
                'tipo_cobertura_poliza' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'empresa_id' => $empresaId,
                'usuario_id' => null,
                'aseguradora_id' => null,
                'tipo_cliente' => 'persona_moral',
                'nombre' => 'Transportes del Valle',
                'apellido_paterno' => null,
                'apellido_materno' => null,
                'sexo' => null,
                'curp' => null,
                'fecha_nacimiento' => null,
                'direccion_id' => null,
                'telefono' => '55-9876-5432',
                'telefono_local' => null,
                'email' => 'contacto@transportesdelvalle.com',
                'folio_ine' => null,
                'nacionalidad' => 'Mexicana',
                'contacto_enlace' => 'Carlos López',
                'numero_poliza' => 'POL-2026-001',
                'tipo_cobertura_poliza' => 'Cobertura Amplia',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}

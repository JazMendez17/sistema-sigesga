<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OficinaSeeder extends Seeder
{
    public function run(): void
    {
        $empresaId = DB::table('empresas')->value('id');
        $direccionId = DB::table('direcciones')->value('id');
        if (!$empresaId) {
            return;
        }

        DB::table('oficinas')->updateOrInsert(
            ['empresa_id' => $empresaId, 'nombre' => 'Oficina Central'],
            [
                'direccion_id' => $direccionId,
                'telefono' => '55-1234-5678',
                'email' => 'central@gevsigesga.com',
                'encargado' => 'Juan Carlos Pérez López',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
}

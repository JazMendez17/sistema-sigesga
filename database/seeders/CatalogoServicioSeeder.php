<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CatalogoServicioSeeder extends Seeder
{
    public function run(): void
    {
        $empresaId = DB::table('empresas')->first()->id;

        DB::table('catalogo_servicios')->insert([
            [
                'empresa_id' => $empresaId,
                'nombre' => 'Servicio de Grúa Ligera',
                'requiere_maniobra' => false,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'empresa_id' => $empresaId,
                'nombre' => 'Servicio de Grúa Pesada',
                'requiere_maniobra' => true,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'empresa_id' => $empresaId,
                'nombre' => 'Arrastre y Salvamento',
                'requiere_maniobra' => true,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}

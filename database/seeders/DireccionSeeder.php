<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DireccionSeeder extends Seeder
{
    public function run(): void
    {
        $direcciones = [
            [
                'calle' => 'Av. Paseo de la Reforma',
                'numero_exterior' => '222',
                'numero_interior' => 'Piso 8',
                'colonia' => 'Juárez',
                'codigo_postal' => '06600',
                'municipio_alcaldia' => 'Cuauhtémoc',
                'ciudad' => 'Ciudad de México',
                'estado' => 'Ciudad de México',
                'pais' => 'México',
                'referencias' => 'Entre Insurgentes y Bucareli. Edificio corporativo.',
            ],
            [
                'calle' => 'Calzada de Tlalpan',
                'numero_exterior' => '4567',
                'numero_interior' => null,
                'colonia' => 'Portales',
                'codigo_postal' => '03300',
                'municipio_alcaldia' => 'Benito Juárez',
                'ciudad' => 'Ciudad de México',
                'estado' => 'Ciudad de México',
                'pais' => 'México',
                'referencias' => 'Cerca del metro Portales. Estacionamiento privado.',
            ],
        ];

        foreach ($direcciones as $dir) {
            DB::table('direcciones')->updateOrInsert(
                ['calle' => $dir['calle'], 'numero_exterior' => $dir['numero_exterior'], 'codigo_postal' => $dir['codigo_postal']],
                array_merge($dir, ['created_at' => now(), 'updated_at' => now()])
            );
        }
    }
}

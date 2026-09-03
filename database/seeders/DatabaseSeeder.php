<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            DireccionSeeder::class,
            EmpresaSeeder::class,
            OficinaSeeder::class,
            EmpleadoSeeder::class,
            UsuarioSeeder::class,
            OperadoreSeeder::class,
            CatalogoServicioSeeder::class,
            ClienteSeeder::class,
            AseguradoraSeeder::class,
            ConvenioSeeder::class,
            ConvenioTarifaSeeder::class,
        ]);
    }
}

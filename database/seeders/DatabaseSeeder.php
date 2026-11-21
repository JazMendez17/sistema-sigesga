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
            AseguradoraSeeder::class,
            ClienteSeeder::class,
            ConvenioSeeder::class,
            ConvenioTarifaSeeder::class,
            ConvenioConceptoAdicionalSeeder::class,
            ConvenioManiobraEspecialSeeder::class,
            ConvenioSlaSeeder::class,
            TarifaEmpresaSeeder::class,
            DemoSigesgaSeeder::class,
        ]);
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

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
        ]);

        $empresa = DB::table('empresas')->where('nombre', 'Grúas y Equipos del Valle, S.A. de C.V.')->first();

        if ($empresa) {
            DB::table('empresa_modulo_colores')
                ->where('empresa_id', $empresa->id)
                ->whereIn('modulo', ['servicios', 'servicios(landing)'])
                ->update(['modulo' => 'Servicios']);
        }
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class OperadoreSeeder extends Seeder
{
    public function run(): void
    {
        $empresaId = DB::table('empresas')->first()->id;

        $empleadoOperador = DB::table('empleados')
            ->where('empresa_id', $empresaId)
            ->where('puesto', 'Operador de Grúa')
            ->first();

        if (!$empleadoOperador) {
            return;
        }

        $exists = DB::table('operadores')
            ->where('empresa_id', $empresaId)
            ->where('empleado_id', $empleadoOperador->id)
            ->first();

        if ($exists) {
            return;
        }

        DB::table('operadores')->insert([
            'empresa_id' => $empresaId,
            'empleado_id' => $empleadoOperador->id,
            'tipo_licencia' => 'C',
            'numero_licencia' => 'LIC-OP-001',
            'fecha_expedicion' => Carbon::create(2024, 1, 15)->toDateString(),
            'fecha_vigencia' => Carbon::create(2028, 1, 15)->toDateString(),
            'disponible' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}

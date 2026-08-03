<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsuarioSeeder extends Seeder
{
    public function run(): void
    {
        $empresaId = DB::table('empresas')->first()->id;
        $password = Hash::make('12345678');

        $usuarios = [
            [
                'empleado_puesto' => 'Administrador',
                'name' => 'administrador admin admin',
                'email' => 'admin@sigesga.com',
                'rol' => 'admin',
            ],
            [
                'empleado_puesto' => 'Cotizador',
                'name' => 'Cotizador Sistema',
                'email' => 'cotizador@sigesga.com',
                'rol' => 'cotizador',
            ],
            [
                'empleado_puesto' => 'Operador de Grúa',
                'name' => 'Roberto Méndez',
                'email' => 'operador@sigesga.com',
                'rol' => 'operador',
            ],
            [
                'empleado_puesto' => 'Cliente',
                'name' => 'Cliente Demo',
                'email' => 'cliente@sigesga.com',
                'rol' => 'cliente',
            ],
        ];

        foreach ($usuarios as $data) {
            $existing = DB::table('usuarios')->where('email', $data['email'])->first();
            if ($existing) {
                continue;
            }

            $empleado = DB::table('empleados')
                ->where('empresa_id', $empresaId)
                ->where('puesto', $data['empleado_puesto'])
                ->first();

            DB::table('usuarios')->insert([
                'empresa_id' => $empresaId,
                'empleado_id' => $empleado?->id,
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $password,
                'password_reset_token' => null,
                'password_reset_expires_at' => null,
                'rol' => $data['rol'],
                'intentos_fallidos' => 0,
                'cuenta_bloqueada' => false,
                'bloqueada_en' => null,
                'desbloqueada_por' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}

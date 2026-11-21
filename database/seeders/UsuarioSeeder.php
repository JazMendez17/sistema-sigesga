<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsuarioSeeder extends Seeder
{
    public function run(): void
    {
        $empresaId = DB::table('empresas')->value('id');
        if (!$empresaId) {
            return;
        }

        $password = Hash::make('12345678');

        $usuarios = [
            [
                'empleado_puesto' => null,
                'name' => 'Super Administrador SIGESGA',
                'email' => 'superadmin@sigesga.com',
                'rol' => 'superadmin',
            ],
            [
                'empleado_puesto' => 'admin',
                'name' => 'Administrador Admin Admin',
                'email' => 'admin@sigesga.com',
                'rol' => 'admin',
            ],
            [
                'empleado_puesto' => 'cotizador',
                'name' => 'Cotizador Sistema',
                'email' => 'cotizador@sigesga.com',
                'rol' => 'cotizador',
            ],
            [
                'empleado_puesto' => 'operador',
                'name' => 'Roberto Méndez',
                'email' => 'operador@sigesga.com',
                'rol' => 'operador',
            ],
            [
                'empleado_puesto' => null,
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

            $empleadoId = null;
            if ($data['empleado_puesto']) {
                $empleado = DB::table('empleados')
                    ->where('empresa_id', $empresaId)
                    ->where('puesto', $data['empleado_puesto'])
                    ->whereNotIn('id', function ($query) {
                        $query->select('empleado_id')
                            ->from('usuarios')
                            ->whereNotNull('empleado_id');
                    })
                    ->first();
                $empleadoId = $empleado?->id;
            }

            DB::table('usuarios')->insert([
                'empresa_id' => $empresaId,
                'empleado_id' => $empleadoId,
                'name' => $data['name'],
                'email' => $data['email'],
                'email_verified_at' => now(),
                'password' => $password,
                'password_reset_token' => null,
                'password_reset_expires_at' => null,
                'rol' => $data['rol'],
                'intentos_fallidos' => 0,
                'cuenta_bloqueada' => false,
                'bloqueo_administrativo' => false,
                'bloqueada_en' => null,
                'desbloqueada_por' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}

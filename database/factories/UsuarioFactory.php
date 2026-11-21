<?php

namespace Database\Factories;

use App\Models\Empresa;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UsuarioFactory extends Factory
{
    protected $model = Usuario::class;

    protected static ?string $password;

    public function definition(): array
    {
        return [
            'empresa_id' => fn () => Empresa::query()->value('id') ?? Empresa::create([
                'nombre' => 'Empresa Demo',
                'siglas' => 'DEMO',
                'email_contacto' => 'empresa@sigesga.com',
                'telefono_contacto' => '5512345678',
            ])->id,
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'rol' => 'admin',
            'remember_token' => Str::random(10),
        ];
    }

    public function superadmin(): static
    {
        return $this->state(fn (array $attributes) => [
            'rol' => 'superadmin',
        ]);
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}

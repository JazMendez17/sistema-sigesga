<?php

namespace App\Http\Middleware;

use App\Models\Empresa;
use App\Models\EmpresaModuloColore;
use App\Models\Notificacione;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        $user = $request->user();
        $empresa = null;
        $moduloColores = [];

        if ($user) {
            $user->load('empresa');
            $empresa = $user->empresa;
        }

        if (!$empresa) {
            $empresa = Empresa::first();
        }

        if ($empresa) {
            $moduloColores = EmpresaModuloColore::where('empresa_id', $empresa->id)
                ->pluck('color', 'modulo')
                ->toArray();
        }

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'rol' => $user->rol,
                    'empresa_id' => $user->empresa_id,
                    'empleado_id' => $user->empleado_id,
                    'foto' => $user->foto,
                ] : null,
            ],
            'empresa' => $empresa ? [
                'id' => $empresa->id,
                'nombre' => $empresa->nombre,
                'siglas' => $empresa->siglas,
                'slogan' => $empresa->slogan,
                'logo' => $empresa->logo,
                'imagen_fondo' => $empresa->imagen_fondo,
                'texto_derechos' => $empresa->texto_derechos,
                'color_primario' => $empresa->color_primario ?? '#4F46E5',
                'color_secundario' => $empresa->color_secundario ?? '#7C3AED',
                'color_fondo' => $empresa->color_fondo ?? '#E8EDF2',
                'color_texto' => $empresa->color_texto ?? '#1F2937',
                'tipografia' => $empresa->tipografia ?? 'Roboto',
                'modo_oscuro' => $empresa->modo_oscuro ?? false,
                'telefono_contacto' => $empresa->telefono_contacto,
                'email_contacto' => $empresa->email_contacto,
                'modulo_colores' => $moduloColores,
            ] : null,
            'flash' => [
                'success' => $request->session()->get('success'),
                'error' => $request->session()->get('error'),
                'uploaded_path' => $request->session()->get('uploaded_path'),
                'reporte' => $request->session()->get('reporte'),
            ],
            'unreadNotifications' => $user ? Notificacione::where('empresa_id', $user->empresa_id)
                ->when(!in_array($user->rol, ['admin', 'cotizador']), fn($q) => $q->where('usuario_id', $user->id))
                ->where('estado', '!=', 'leido')
                ->count() : 0,
        ];
    }
}

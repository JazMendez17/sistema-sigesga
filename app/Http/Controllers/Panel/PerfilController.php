<?php

// Controlador de perfil de usuario

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\Empleado;
use App\Http\Requests\Panel\UpdatePasswordRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class PerfilController extends Controller
{
    // Mostrar perfil del usuario autenticado
    public function index()
    {
        $user = auth()->user()->load('empresa');

        $data = [
            'usuario' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'telefono' => $user->telefono,
                'foto' => $user->foto,
                'rol' => $user->rol,
                'empresa' => $user->empresa?->nombre ?? 'Sin empresa',
            ],
        ];

        if ($user->empleado_id) {
            $empleado = Empleado::with('direccion')->find($user->empleado_id);
            if ($empleado) {
                $data['empleado'] = [
                    'nombre_completo' => trim($empleado->nombre . ' ' . $empleado->apellido_paterno . ' ' . $empleado->apellido_materno),
                    'nombre' => $empleado->nombre,
                    'apellido_paterno' => $empleado->apellido_paterno,
                    'apellido_materno' => $empleado->apellido_materno,
                    'sexo' => $empleado->sexo,
                    'curp' => $empleado->curp,
                    'fecha_nacimiento' => $empleado->fecha_nacimiento ? (is_string($empleado->fecha_nacimiento) ? \Carbon\Carbon::parse($empleado->fecha_nacimiento)->format('d/m/Y') : $empleado->fecha_nacimiento->format('d/m/Y')) : null,
                    'telefono' => $empleado->telefono,
                    'telefono_local' => $empleado->telefono_local,
                    'email' => $empleado->correo,
                    'folio_ine' => $empleado->folio_ine,
                    'nacionalidad' => $empleado->nacionalidad,
                    'puesto' => $empleado->puesto,
                    'direccion' => $empleado->direccion ? trim($empleado->direccion->calle . ' ' . $empleado->direccion->colonia . ', ' . $empleado->direccion->ciudad) : null,
                    'calle' => $empleado->direccion?->calle,
                    'numero_exterior' => $empleado->direccion?->numero_exterior,
                    'numero_interior' => $empleado->direccion?->numero_interior,
                    'colonia' => $empleado->direccion?->colonia,
                    'codigo_postal' => $empleado->direccion?->codigo_postal,
                    'localidad' => $empleado->direccion?->ciudad,
                    'municipio_alcaldia' => $empleado->direccion?->municipio_alcaldia,
                    'estado' => $empleado->direccion?->estado,
                    'pais' => $empleado->direccion?->pais,
                    'referencias' => $empleado->direccion?->referencias,
                ];
            }
        }

        if ($user->rol === 'cliente') {
            $cliente = Cliente::with('direccion', 'aseguradora')->where('usuario_id', $user->id)->first();
            if ($cliente) {
                $data['cliente'] = [
                    'nombre_completo' => trim($cliente->nombre . ' ' . $cliente->apellido_paterno . ' ' . $cliente->apellido_materno),
                    'nombre' => $cliente->nombre,
                    'apellido_paterno' => $cliente->apellido_paterno,
                    'apellido_materno' => $cliente->apellido_materno,
                    'tipo_cliente' => $cliente->tipo_cliente === 'persona_moral' ? 'Persona Moral' : 'Persona Física',
                    'sexo' => $cliente->sexo,
                    'curp' => $cliente->curp,
                    'fecha_nacimiento' => $cliente->fecha_nacimiento ? (is_string($cliente->fecha_nacimiento) ? \Carbon\Carbon::parse($cliente->fecha_nacimiento)->format('d/m/Y') : $cliente->fecha_nacimiento->format('d/m/Y')) : null,
                    'telefono' => $cliente->telefono,
                    'telefono_local' => $cliente->telefono_local,
                    'email' => $cliente->email,
                    'folio_ine' => $cliente->folio_ine,
                    'nacionalidad' => $cliente->nacionalidad,
                    'contacto_enlace' => $cliente->contacto_enlace,
                    'numero_poliza' => $cliente->numero_poliza,
                    'tipo_cobertura_poliza' => $cliente->tipo_cobertura_poliza,
                    'aseguradora' => $cliente->aseguradora?->nombre ?? null,
                    'direccion' => $cliente->direccion ? trim($cliente->direccion->calle . ' ' . $cliente->direccion->colonia . ', ' . $cliente->direccion->ciudad) : null,
                ];
            }
        }

        return Inertia::render('Panel/MiPerfil/Index', $data);
    }

    // Actualizar número de teléfono
    public function updateTelefono(Request $request)
    {
        $validated = $request->validate([
            'telefono' => 'required|string|max:20|regex:/^[\d\s\-\(\)\+]+$/',
        ]);

        auth()->user()->update($validated);

        return back()->with('success', 'Teléfono actualizado correctamente');
    }

    // Cambiar contraseña del usuario
    public function updatePassword(UpdatePasswordRequest $request)
    {
        $user = auth()->user();

        if (!Hash::check($request->actual, $user->password)) {
            return back()->withErrors(['actual' => 'La contraseña actual no es correcta'])->with('error', 'La contraseña actual no es correcta');
        }

        $user->update([
            'password' => $request->nueva,
        ]);

        return back()->with('success', 'Contraseña actualizada correctamente');
    }

    // Actualizar foto de perfil
    public function updateFoto(Request $request)
    {
        $request->validate([
            'foto' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $user = auth()->user();

        if ($user->foto) {
            Storage::disk('public')->delete($user->foto);
        }

        $path = $request->file('foto')->store('fotos', 'public');
        $user->update(['foto' => $path]);

        return back()->with('success', 'Foto de perfil actualizada correctamente');
    }

    // Eliminar foto de perfil
    public function destroyFoto()
    {
        $user = auth()->user();

        if ($user->foto) {
            Storage::disk('public')->delete($user->foto);
            $user->update(['foto' => null]);
        }

        return back()->with('success', 'Foto de perfil eliminada correctamente');
    }
}

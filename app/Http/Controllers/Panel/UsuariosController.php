<?php

// Controlador de usuarios del sistema

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Support\Auditoria;
use App\Models\Usuario;
use App\Models\Empleado;
use App\Http\Requests\Panel\StoreUsuarioRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class UsuariosController extends Controller
{
    // Lista de usuarios
    public function index()
    {
        $user = Auth::user();
        $empresaId = $user->empresa_id;

        $usuarios = Usuario::with('empleado')
            ->where('empresa_id', $empresaId)
            ->latest()
            ->get()
            ->map(fn ($u) => [
                'id' => $u->id,
                'nombre' => $u->name ?? '—',
                'email' => $u->email ?? '—',
                'rol' => $u->rol ?? '—',
                'intentos_fallidos' => $u->intentos_fallidos ?? 0,
                'bloqueado' => $u->cuenta_bloqueada ?? false,
            ]);

        return Inertia::render('Panel/Usuarios/Index', [
            'usuarios' => $usuarios,
        ]);
    }

    // Formulario para crear usuario
    public function create()
    {
        $user = Auth::user();
        $empresaId = $user->empresa_id;

        return Inertia::render('Panel/Usuarios/Create', [
            'empleados' => Empleado::where('empresa_id', $empresaId)->get(['id', 'nombre', 'apellido_paterno', 'apellido_materno']),
        ]);
    }

    // Guardar usuario en base de datos
    public function store(StoreUsuarioRequest $request)
    {
        $user = Auth::user();

        $data = $request->validated();
        $data['empresa_id'] = $user->empresa_id;

        Usuario::create($data);

        return redirect()->route('panel.usuarios.index')
            ->with('success', 'Usuario creado correctamente');
    }

    // Ver detalle de usuario
    public function show($id)
    {
        $usuario = Usuario::with('empleado')->where('empresa_id', auth()->user()->empresa_id)->findOrFail($id);

        return Inertia::render('Panel/Usuarios/Show', [
            'usuario' => [
                'id' => $usuario->id,
                'name' => $usuario->name,
                'email' => $usuario->email,
                'rol' => $usuario->rol,
                'telefono' => $usuario->telefono,
                'foto' => $usuario->foto,
                'intentos_fallidos' => $usuario->intentos_fallidos,
                'cuenta_bloqueada' => $usuario->cuenta_bloqueada,
                'bloqueada_en' => $usuario->bloqueada_en?->format('d/m/Y H:i'),
                'empleado' => $usuario->empleado ? [
                    'nombre_completo' => trim($usuario->empleado->nombre . ' ' . $usuario->empleado->apellido_paterno . ' ' . $usuario->empleado->apellido_materno),
                    'puesto' => $usuario->empleado->puesto,
                    'telefono' => $usuario->empleado->telefono,
                    'correo' => $usuario->empleado->correo,
                ] : null,
            ],
        ]);
    }

    // Formulario para editar usuario
    public function edit($id)
    {
        $user = Auth::user();
        $empresaId = $user->empresa_id;

        return Inertia::render('Panel/Usuarios/Create', [
            'usuario' => Usuario::where('empresa_id', auth()->user()->empresa_id)->findOrFail($id),
            'empleados' => Empleado::where('empresa_id', $empresaId)->get(['id', 'nombre', 'apellido_paterno', 'apellido_materno']),
        ]);
    }

    // Actualizar datos del usuario
    public function update(StoreUsuarioRequest $request, $id)
    {
        $usuario = Usuario::where('empresa_id', auth()->user()->empresa_id)->findOrFail($id);

        $data = $request->validated();

        if (!$request->filled('password')) {
            unset($data['password']);
        }

        if ($request->has('cuenta_bloqueada')) {
            $data['cuenta_bloqueada'] = $request->boolean('cuenta_bloqueada');
        } else {
            unset($data['cuenta_bloqueada']);
        }

        $usuario->update($data);

        return redirect()->route('panel.usuarios.index')
            ->with('success', 'Usuario actualizado correctamente');
    }

    // Cambiar el rol de un usuario (acción parcial)
    public function cambiarRol(Request $request, $id)
    {
        $request->validate([
            'rol' => 'required|in:admin,cotizador,operador,cliente',
        ]);

        $usuario = Usuario::where('empresa_id', auth()->user()->empresa_id)->findOrFail($id);

        if ($usuario->id === Auth::id()) {
            return back()->with('error', 'No puedes cambiar tu propio rol.');
        }

        $usuario->update(['rol' => $request->rol]);

        return back()->with('success', 'Rol actualizado correctamente');
    }

    // Desbloquear la cuenta de un usuario (acción parcial)
    public function desbloquear($id)
    {
        $usuario = Usuario::where('empresa_id', auth()->user()->empresa_id)->findOrFail($id);

        $usuario->update([
            'cuenta_bloqueada' => false,
            'intentos_fallidos' => 0,
            'codigo_desbloqueo' => null,
            'codigo_desbloqueo_expira' => null,
        ]);

        return back()->with('success', 'Cuenta desbloqueada correctamente');
    }

    // Eliminar usuario
    public function destroy($id)
    {
        $usuario = Usuario::where('empresa_id', auth()->user()->empresa_id)->findOrFail($id);

        if ($usuario->id === Auth::id()) {
            return redirect()->back()->with('error', 'No puedes eliminar tu propio usuario.');
        }

        if ($usuario->email === 'admin@sigesga.com') {
            return redirect()->back()->with('error', 'El administrador principal del sistema no puede ser eliminado.');
        }

        Auditoria::registrar($usuario);

        $usuario->delete();

        return redirect()->route('panel.usuarios.index')
            ->with('success', 'Usuario eliminado correctamente');
    }
}

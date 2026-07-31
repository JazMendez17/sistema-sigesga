<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use App\Models\Empleado;
use App\Http\Requests\Panel\StoreUsuarioRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class UsuariosController extends Controller
{
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

    public function create()
    {
        $user = Auth::user();
        $empresaId = $user->empresa_id;

        return Inertia::render('Panel/Usuarios/Create', [
            'empleados' => Empleado::where('empresa_id', $empresaId)->get(['id', 'nombre', 'apellido_paterno']),
        ]);
    }

    public function store(StoreUsuarioRequest $request)
    {
        $user = Auth::user();

        $data = $request->validated();
        $data['empresa_id'] = $user->empresa_id;
        $data['password'] = Hash::make($data['password']);

        Usuario::create($data);

        return redirect()->route('panel.usuarios.index')
            ->with('success', 'Usuario creado correctamente');
    }

    public function edit($id)
    {
        return Inertia::render('Panel/Usuarios/Create', [
            'usuario' => Usuario::where('empresa_id', auth()->user()->empresa_id)->findOrFail($id),
        ]);
    }

    public function update(StoreUsuarioRequest $request, $id)
    {
        $usuario = Usuario::where('empresa_id', auth()->user()->empresa_id)->findOrFail($id);

        $data = $request->validated();

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        } else {
            unset($data['password']);
        }

        $data['cuenta_bloqueada'] = $request->boolean('cuenta_bloqueada');
        $usuario->update($data);

        return redirect()->route('panel.usuarios.index')
            ->with('success', 'Usuario actualizado correctamente');
    }

    public function destroy($id)
    {
        $usuario = Usuario::where('empresa_id', auth()->user()->empresa_id)->findOrFail($id);

        if ($usuario->id === Auth::id()) {
            return redirect()->back()->with('error', 'No puedes eliminar tu propio usuario.');
        }

        $usuario->delete();

        return redirect()->route('panel.usuarios.index')
            ->with('success', 'Usuario eliminado correctamente');
    }
}

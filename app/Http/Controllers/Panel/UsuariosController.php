<?php

// Controlador de usuarios del sistema con soporte de Super Administrador y borrado físico real.
// Solo el `superadmin` puede crear, editar, cambiar rol, bloquear/desbloquear o eliminar cuentas `admin` y `superadmin`.

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Http\Requests\Panel\StoreUsuarioRequest;
use App\Models\AutorizacionesCancelacione;
use App\Models\Cliente;
use App\Models\Cotizacione;
use App\Models\Empleado;
use App\Models\Usuario;
use App\Support\Auditoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class UsuariosController extends Controller
{
    private function baseQuery()
    {
        $user = Auth::user();
        $query = Usuario::query();

        if (!$user->isSuperAdmin()) {
            $query->where('empresa_id', $user->empresa_id);
        }

        return $query;
    }

    private function asegurarPermisoGestion(Usuario $objetivo): void
    {
        $actor = Auth::user();

        if ($actor->isSuperAdmin()) {
            return;
        }

        if ($objetivo->empresa_id !== $actor->empresa_id) {
            abort(403, 'No tienes permisos para administrar usuarios de otra empresa.');
        }

        if (in_array($objetivo->rol, ['superadmin', 'admin'], true)) {
            abort(403, 'Solo el Super Administrador puede administrar cuentas de administrador.');
        }
    }

    private function rolesPermitidos(Usuario $actor): array
    {
        return $actor->isSuperAdmin()
            ? ['admin', 'cotizador', 'operador', 'cliente']
            : ['cotizador', 'operador', 'cliente'];
    }

    private function rolesParaFormulario(Usuario $actor): array
    {
        $etiquetas = [
            'admin' => 'Administrador',
            'cotizador' => 'Cotizador',
            'operador' => 'Operador',
            'cliente' => 'Cliente',
        ];

        return array_map(
            fn ($rol) => ['value' => $rol, 'label' => $etiquetas[$rol] ?? ucfirst($rol)],
            $this->rolesPermitidos($actor)
        );
    }

    public function index()
    {
        $actor = Auth::user();

        $usuarios = $this->baseQuery()
            ->with('empleado')
            ->latest()
            ->get()
            ->map(fn ($u) => [
                'id' => $u->id,
                'nombre' => $u->name ?? '—',
                'email' => $u->email ?? '—',
                'rol' => $u->rol ?? '—',
                'intentos_fallidos' => $u->intentos_fallidos ?? 0,
                'bloqueado' => (bool) ($u->cuenta_bloqueada ?? false),
                'bloqueo_administrativo' => (bool) ($u->bloqueo_administrativo ?? false),
                'puede_gestionar' => $actor->isSuperAdmin() || !in_array($u->rol, ['superadmin', 'admin'], true),
                'puede_cambiar_rol' => $u->rol !== 'superadmin' && ($actor->isSuperAdmin() || $u->rol !== 'admin'),
            ]);

        return Inertia::render('Panel/Usuarios/Index', [
            'usuarios' => $usuarios,
            'roles' => $this->rolesPermitidos($actor),
            'esSuperAdmin' => $actor->isSuperAdmin(),
        ]);
    }

    public function create()
    {
        $user = Auth::user();
        $empleadosQuery = Empleado::query();
        if (!$user->isSuperAdmin()) {
            $empleadosQuery->where('empresa_id', $user->empresa_id);
        }

        return Inertia::render('Panel/Usuarios/Create', [
            'empleados' => $empleadosQuery->get(['id', 'nombre', 'apellido_paterno', 'apellido_materno']),
            'roles' => $this->rolesParaFormulario($user),
            'esSuperAdmin' => $user->isSuperAdmin(),
        ]);
    }

    public function store(StoreUsuarioRequest $request)
    {
        $actor = Auth::user();
        $data = $request->validated();

        if ($data['rol'] === 'superadmin') {
            abort(403, 'El rol de Super Administrador es único y no se puede asignar a nuevos usuarios.');
        }

        if (!$actor->isSuperAdmin() && in_array($data['rol'], ['superadmin', 'admin'], true)) {
            abort(403, 'Solo el Super Administrador puede crear cuentas de administrador.');
        }

        $data['empresa_id'] = $actor->empresa_id;
        $data['email_verified_at'] = now();

        Usuario::create($data);

        return redirect()->route('panel.usuarios.index')
            ->with('success', 'Usuario creado correctamente');
    }

    public function show($id)
    {
        $usuario = $this->baseQuery()->with('empleado')->findOrFail($id);

        return Inertia::render('Panel/Usuarios/Show', [
            'usuario' => [
                'id' => $usuario->id,
                'name' => $usuario->name,
                'email' => $usuario->email,
                'rol' => $usuario->rol,
                'telefono' => $usuario->telefono,
                'foto' => $usuario->foto,
                'intentos_fallidos' => $usuario->intentos_fallidos,
                'cuenta_bloqueada' => (bool) $usuario->cuenta_bloqueada,
                'bloqueo_administrativo' => (bool) $usuario->bloqueo_administrativo,
                'bloqueada_en' => $usuario->bloqueada_en?->format('d/m/Y H:i'),
                'empleado' => $usuario->empleado ? [
                    'nombre_completo' => trim($usuario->empleado->nombre . ' ' . $usuario->empleado->apellido_paterno . ' ' . $usuario->empleado->apellido_materno),
                    'puesto' => $usuario->empleado->puesto,
                    'telefono' => $usuario->empleado->telefono,
                    'correo' => $usuario->empleado->correo,
                ] : null,
            ],
            'esSuperAdmin' => Auth::user()->isSuperAdmin(),
        ]);
    }

    public function edit($id)
    {
        $actor = Auth::user();
        $usuario = $this->baseQuery()->findOrFail($id);
        $this->asegurarPermisoGestion($usuario);

        $empleadosQuery = Empleado::query();
        if (!$actor->isSuperAdmin()) {
            $empleadosQuery->where('empresa_id', $actor->empresa_id);
        }

        return Inertia::render('Panel/Usuarios/Create', [
            'usuario' => [
                'id' => $usuario->id,
                'name' => $usuario->name,
                'email' => $usuario->email,
                'rol' => $usuario->rol,
                'empleado_id' => $usuario->empleado_id,
                'telefono' => $usuario->telefono,
                'cuenta_bloqueada' => (bool) $usuario->cuenta_bloqueada,
            ],
            'empleados' => $empleadosQuery->get(['id', 'nombre', 'apellido_paterno', 'apellido_materno']),
            'roles' => $this->rolesParaFormulario($actor),
            'esSuperAdmin' => $actor->isSuperAdmin(),
        ]);
    }

    public function update(StoreUsuarioRequest $request, $id)
    {
        $actor = Auth::user();
        $usuario = $this->baseQuery()->findOrFail($id);
        $this->asegurarPermisoGestion($usuario);

        $data = $request->validated();

        if (!$actor->isSuperAdmin() && in_array($data['rol'], ['superadmin', 'admin'], true)) {
            abort(403, 'Solo el Super Administrador puede asignar roles de administrador.');
        }

        if (!$usuario->isSuperAdmin() && $data['rol'] === 'superadmin') {
            return back()->with('error', 'El rol de Super Administrador es único y no se puede asignar a otros usuarios.');
        }

        if ($usuario->isSuperAdmin() && $data['rol'] !== 'superadmin') {
            return back()->with('error', 'El rol del Super Administrador no se puede modificar.');
        }

        if ($usuario->id === $actor->id && $data['rol'] !== $usuario->rol) {
            return back()->with('error', 'No puedes cambiar tu propio rol.');
        }

        if (!$request->filled('password')) {
            unset($data['password']);
        }

        if ($request->has('cuenta_bloqueada')) {
            $bloquear = $request->boolean('cuenta_bloqueada');
            if ($usuario->id === $actor->id && $bloquear) {
                return back()->with('error', 'No puedes bloquear tu propia cuenta.');
            }
            if ($usuario->isSuperAdmin() && $bloquear) {
                return back()->with('error', 'No se puede bloquear una cuenta de Super Administrador.');
            }
            $data['cuenta_bloqueada'] = $bloquear;
            $data['bloqueo_administrativo'] = $bloquear;
            if ($bloquear) {
                $data['bloqueada_en'] = now();
                $data['codigo_desbloqueo'] = null;
                $data['codigo_desbloqueo_expira'] = null;
            } else {
                $data['intentos_fallidos'] = 0;
                $data['desbloqueada_por'] = $actor->id;
            }
        } else {
            unset($data['cuenta_bloqueada']);
        }

        $usuario->update($data);

        return redirect()->route('panel.usuarios.index')
            ->with('success', 'Usuario actualizado correctamente');
    }

    public function cambiarRol(Request $request, $id)
    {
        $actor = Auth::user();
        $rolesPermitidos = $actor->isSuperAdmin()
            ? 'admin,cotizador,operador,cliente'
            : 'cotizador,operador,cliente';

        $request->validate([
            'rol' => "required|in:{$rolesPermitidos}",
        ]);

        $usuario = $this->baseQuery()->findOrFail($id);
        $this->asegurarPermisoGestion($usuario);

        if ($usuario->isSuperAdmin()) {
            return back()->with('error', 'El rol del Super Administrador no se puede modificar.');
        }

        if ($usuario->id === $actor->id) {
            return back()->with('error', 'No puedes cambiar tu propio rol.');
        }

        if ($usuario->isSuperAdmin() && $request->rol !== 'superadmin' && Usuario::where('rol', 'superadmin')->count() <= 1) {
            return back()->with('error', 'Debe existir al menos un Super Administrador en el sistema.');
        }

        $usuario->update(['rol' => $request->rol]);

        return back()->with('success', 'Rol actualizado correctamente');
    }

    public function desbloquear($id)
    {
        $actor = Auth::user();
        $usuario = $this->baseQuery()->findOrFail($id);
        $this->asegurarPermisoGestion($usuario);

        $usuario->update([
            'cuenta_bloqueada' => false,
            'bloqueo_administrativo' => false,
            'intentos_fallidos' => 0,
            'codigo_desbloqueo' => null,
            'codigo_desbloqueo_expira' => null,
            'desbloqueada_por' => $actor->id,
        ]);

        return back()->with('success', 'Cuenta desbloqueada correctamente');
    }

    // Eliminación FÍSICA real del usuario
    public function destroy($id)
    {
        $actor = Auth::user();
        $usuario = $this->baseQuery()->findOrFail($id);
        $this->asegurarPermisoGestion($usuario);

        if ($usuario->id === $actor->id) {
            return redirect()->back()->with('error', 'No puedes eliminar tu propio usuario.');
        }

        if ($usuario->isSuperAdmin() && Usuario::where('rol', 'superadmin')->count() <= 1) {
            return redirect()->back()->with('error', 'No se puede eliminar al último Super Administrador del sistema.');
        }

        DB::transaction(function () use ($usuario) {
            Auditoria::registrar($usuario);

            // Desvincular referencias que podrían bloquear el borrado físico sin perder historial comercial
            Cliente::where('usuario_id', $usuario->id)->update(['usuario_id' => null]);
            Cotizacione::where('usuario_creador_id', $usuario->id)->update(['usuario_creador_id' => null]);
            AutorizacionesCancelacione::where('usuario_solicitante_id', $usuario->id)->update(['usuario_solicitante_id' => null]);
            AutorizacionesCancelacione::where('usuario_resolutor_id', $usuario->id)->update(['usuario_resolutor_id' => null]);
            Usuario::where('desbloqueada_por', $usuario->id)->update(['desbloqueada_por' => null]);

            $usuario->delete();
        });

        return redirect()->route('panel.usuarios.index')
            ->with('success', 'Usuario eliminado físicamente de forma correcta.');
    }
}

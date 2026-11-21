<?php

// Controlador de empleados (coalescing dirección NOT NULL, transacciones, superadmin-safe y borrado físico)

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Http\Requests\Panel\StoreEmpleadoRequest;
use App\Models\Direccion;
use App\Models\Empleado;
use App\Models\Oficina;
use App\Models\Servicio;
use App\Models\Unidade;
use App\Models\Usuario;
use App\Support\Auditoria;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class EmpleadosController extends Controller
{
    protected function mapearRol(string $puesto): string
    {
        $puesto = mb_strtolower(trim($puesto));

        if (str_contains($puesto, 'operador')) {
            return 'operador';
        }

        if (str_contains($puesto, 'cotizador') || str_contains($puesto, 'cotización') || str_contains($puesto, 'ventas')) {
            return 'cotizador';
        }

        if (str_contains($puesto, 'admin') || str_contains($puesto, 'gerente') || str_contains($puesto, 'director') || str_contains($puesto, 'dueño') || str_contains($puesto, 'dueno')) {
            // Solo un superadmin puede crear usuarios con rol admin; si crea un admin normal, se asigna cotizador
            return Auth::user()?->isSuperAdmin() ? 'admin' : 'cotizador';
        }

        return 'cotizador';
    }

    /** Verifica si la dirección trae algún campo real además del país por defecto. */
    private function tieneDatosDireccion(?array $dir): bool
    {
        if (empty($dir)) {
            return false;
        }
        $sinPais = array_diff_key($dir, ['pais' => true]);

        return !empty(array_filter($sinPais, fn ($v) => $v !== null && $v !== ''));
    }

    private function normalizarDireccion(array $dir): array
    {
        return [
            'calle' => $dir['calle'] ?? '',
            'numero_exterior' => $dir['numero_exterior'] ?? '',
            'numero_interior' => $dir['numero_interior'] ?? null,
            'colonia' => $dir['colonia'] ?? '',
            'codigo_postal' => $dir['codigo_postal'] ?? '',
            'municipio_alcaldia' => $dir['municipio_alcaldia'] ?? '',
            'ciudad' => $dir['ciudad'] ?? '',
            'estado' => $dir['estado'] ?? '',
            'pais' => $dir['pais'] ?? 'México',
            'referencias' => $dir['referencias'] ?? null,
        ];
    }

    public function index()
    {
        $empresaId = Auth::user()->empresa_id;

        $empleados = Empleado::with('oficina')
            ->where('empresa_id', $empresaId)
            ->latest()
            ->get()
            ->map(fn ($e) => [
                'id' => $e->id,
                'nombre_completo' => trim($e->nombre . ' ' . ($e->apellido_paterno ?? '') . ' ' . ($e->apellido_materno ?? '')),
                'curp' => $e->curp ?? '—',
                'correo' => $e->correo ?? '—',
                'telefono' => $e->telefono ?? '—',
                'puesto' => $e->puesto ?? '—',
                'oficina' => $e->oficina?->nombre ?? '—',
            ]);

        return Inertia::render('Panel/Empleados/Index', [
            'empleados' => $empleados,
        ]);
    }

    public function create()
    {
        $empresaId = Auth::user()->empresa_id;

        return Inertia::render('Panel/Empleados/Create', [
            'oficinas' => Oficina::where('empresa_id', $empresaId)->get(['id', 'nombre']),
        ]);
    }

    public function store(StoreEmpleadoRequest $request)
    {
        $user = Auth::user();
        $data = $request->validated();
        $data['empresa_id'] = $user->empresa_id;
        $data['nacionalidad'] = $data['nacionalidad'] ?? 'Mexicana';
        $data['sueldo_diario'] = $data['sueldo_diario'] ?? 0;

        $dirInput = $data['direccion'] ?? null;
        unset($data['direccion']);

        $usuarioCreado = null;

        DB::transaction(function () use ($user, &$data, $dirInput, &$usuarioCreado) {
            if ($this->tieneDatosDireccion($dirInput)) {
                $direccion = Direccion::create($this->normalizarDireccion($dirInput));
                $data['direccion_id'] = $direccion->id;
            }

            $empleado = Empleado::create($data);

            if (!empty($empleado->correo) && !empty($empleado->puesto) && !Usuario::where('email', $empleado->correo)->exists()) {
                $usuarioCreado = Usuario::create([
                    'empresa_id' => $user->empresa_id,
                    'empleado_id' => $empleado->id,
                    'name' => trim($empleado->nombre . ' ' . ($empleado->apellido_paterno ?? '') . ' ' . ($empleado->apellido_materno ?? '')),
                    'email' => $empleado->correo,
                    'email_verified_at' => now(),
                    'password' => 'Empleado123.',
                    'debe_cambiar_password' => true,
                    'rol' => $this->mapearRol($empleado->puesto),
                ]);
            }
        });

        $mensaje = 'Empleado creado correctamente.';
        if ($usuarioCreado) {
            $mensaje .= ' Usuario: ' . $usuarioCreado->email . ' | Contraseña temporal: Empleado123. (deberá cambiarla al iniciar sesión)';
        }

        return redirect()->route('panel.empleados.index')->with('success', $mensaje);
    }

    public function show($id)
    {
        return Inertia::render('Panel/Empleados/Show', [
            'empleado' => Empleado::with('oficina', 'direccion')->where('empresa_id', auth()->user()->empresa_id)->findOrFail($id),
        ]);
    }

    public function edit($id)
    {
        $empresaId = Auth::user()->empresa_id;

        return Inertia::render('Panel/Empleados/Create', [
            'empleado' => Empleado::with('direccion')->where('empresa_id', $empresaId)->findOrFail($id),
            'oficinas' => Oficina::where('empresa_id', $empresaId)->get(['id', 'nombre']),
        ]);
    }

    public function update(StoreEmpleadoRequest $request, $id)
    {
        $empleado = Empleado::with(['direccion', 'usuario'])->where('empresa_id', auth()->user()->empresa_id)->findOrFail($id);
        $data = $request->validated();
        $data['nacionalidad'] = $data['nacionalidad'] ?? $empleado->nacionalidad ?? 'Mexicana';
        $data['sueldo_diario'] = $data['sueldo_diario'] ?? $empleado->sueldo_diario ?? 0;

        $dirInput = $data['direccion'] ?? null;
        unset($data['direccion']);

        DB::transaction(function () use ($empleado, $data, $dirInput) {
            if ($this->tieneDatosDireccion($dirInput)) {
                $payload = $this->normalizarDireccion($dirInput);
                if ($empleado->direccion_id && $empleado->direccion) {
                    $empleado->direccion->update($payload);
                } else {
                    $direccion = Direccion::create($payload);
                    $data['direccion_id'] = $direccion->id;
                }
            }

            $empleado->update($data);

            if ($empleado->usuario) {
                $sync = [
                    'name' => trim($empleado->nombre . ' ' . ($empleado->apellido_paterno ?? '') . ' ' . ($empleado->apellido_materno ?? '')),
                ];
                if (!empty($empleado->correo) && !Usuario::where('email', $empleado->correo)->where('id', '!=', $empleado->usuario->id)->exists()) {
                    $sync['email'] = $empleado->correo;
                }
                $empleado->usuario->update($sync);
            }
        });

        return redirect()->route('panel.empleados.index')->with('success', 'Empleado actualizado correctamente');
    }

    // Borrado físico real del empleado (respetando seguridad de superadmin y servicios activos)
    public function destroy($id)
    {
        $empleado = Empleado::with(['operador', 'usuario', 'direccion'])
            ->where('empresa_id', auth()->user()->empresa_id)
            ->findOrFail($id);

        if ($empleado->usuario && in_array($empleado->usuario->rol, ['superadmin', 'admin'], true) && !Auth::user()->isSuperAdmin()) {
            abort(403, 'Solo el Super Administrador puede eliminar a un empleado vinculado a una cuenta de administrador.');
        }

        if ($empleado->operador && Servicio::where('operador_id', $empleado->operador->id)->exists()) {
            return back()->with('error', 'No se puede eliminar este empleado porque su operador tiene servicios registrados en el historial.');
        }

        DB::transaction(function () use ($empleado) {
            $operador = $empleado->operador;
            $usuario = $empleado->usuario;
            $direccion = $empleado->direccion;

            if ($operador) {
                Unidade::where('operador_asignado_id', $operador->id)->update(['operador_asignado_id' => null]);
                Auditoria::registrar($operador);
                $operador->delete();
            }

            if ($usuario && $usuario->id !== Auth::id()) {
                Auditoria::registrar($usuario);
                $usuario->delete();
            }

            Auditoria::registrar($empleado);
            $empleado->delete();
            $direccion?->delete();
        });

        return redirect()->route('panel.empleados.index')
            ->with('success', 'Empleado eliminado físicamente de forma correcta.');
    }
}

<?php

// Controlador de operadores (alertas Carbon 3 int, fechas Y-m-d y borrado físico)

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Http\Requests\Panel\StoreOperadorRequest;
use App\Models\Empleado;
use App\Models\Notificacione;
use App\Models\Operadore;
use App\Models\Servicio;
use App\Models\Unidade;
use App\Models\Usuario;
use App\Support\Auditoria;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class OperadoresController extends Controller
{
    public function index()
    {
        $empresaId = Auth::user()->empresa_id;

        $operadores = Operadore::with('empleado')
            ->where('empresa_id', $empresaId)
            ->latest()
            ->get()
            ->map(fn ($o) => [
                'id' => $o->id,
                'nombre' => $o->empleado ? trim(($o->empleado->nombre ?? '') . ' ' . ($o->empleado->apellido_paterno ?? '') . ' ' . ($o->empleado->apellido_materno ?? '')) : '—',
                'tipo_licencia' => $o->tipo_licencia ?? '—',
                'numero_licencia' => $o->numero_licencia ?? '—',
                'fecha_expedicion' => $o->fecha_expedicion ? Carbon::parse($o->fecha_expedicion)->format('Y-m-d') : '—',
                'fecha_vigencia' => $o->fecha_vigencia ? Carbon::parse($o->fecha_vigencia)->format('Y-m-d') : '—',
                'disponible' => (bool) ($o->disponible ?? true),
            ]);

        // Filtrar ANTES de limitar y redondear a entero para Carbon 3
        $hoy = now()->startOfDay();
        $licenciasProximas = $operadores
            ->filter(fn ($o) => $o['fecha_vigencia'] !== '—')
            ->map(fn ($o) => [
                'nombre' => $o['nombre'],
                'licencia' => $o['numero_licencia'],
                'vigencia' => $o['fecha_vigencia'],
                'dias' => (int) $hoy->diffInDays(Carbon::parse($o['fecha_vigencia'])->startOfDay(), false),
            ])
            ->filter(fn ($l) => $l['dias'] <= 30)
            ->sortBy('dias')
            ->take(10)
            ->values();

        return Inertia::render('Panel/Operadores/Index', [
            'operadores' => $operadores,
            'licenciasProximas' => $licenciasProximas,
        ]);
    }

    public function create()
    {
        $empresaId = Auth::user()->empresa_id;

        return Inertia::render('Panel/Operadores/Create', [
            'empleados' => Empleado::where('empresa_id', $empresaId)
                ->where(function ($q) {
                    $q->where('puesto', 'LIKE', '%operador%')
                        ->orWhere('puesto', 'LIKE', '%Operador%');
                })
                ->get(['id', 'nombre', 'apellido_paterno', 'apellido_materno']),
        ]);
    }

    public function store(StoreOperadorRequest $request)
    {
        $data = $request->validated();
        $data['empresa_id'] = Auth::user()->empresa_id;
        $data['disponible'] = $request->has('disponible') ? $request->boolean('disponible') : true;

        Operadore::create($data);

        return redirect()->route('panel.operadores.index')
            ->with('success', 'Operador creado correctamente');
    }

    public function show($id)
    {
        $operador = Operadore::with('empleado', 'unidad')
            ->where('empresa_id', auth()->user()->empresa_id)
            ->findOrFail($id);

        $opArr = $operador->toArray();
        if ($operador->fecha_expedicion) {
            $opArr['fecha_expedicion'] = Carbon::parse($operador->fecha_expedicion)->format('d/m/Y');
        }
        if ($operador->fecha_vigencia) {
            $opArr['fecha_vigencia'] = Carbon::parse($operador->fecha_vigencia)->format('d/m/Y');
        }

        return Inertia::render('Panel/Operadores/Show', [
            'operador' => $opArr,
        ]);
    }

    public function edit($id)
    {
        $empresaId = Auth::user()->empresa_id;
        $operador = Operadore::where('empresa_id', $empresaId)->findOrFail($id);

        $opArr = $operador->toArray();
        if ($operador->fecha_expedicion) {
            $opArr['fecha_expedicion'] = Carbon::parse($operador->fecha_expedicion)->format('Y-m-d');
        }
        if ($operador->fecha_vigencia) {
            $opArr['fecha_vigencia'] = Carbon::parse($operador->fecha_vigencia)->format('Y-m-d');
        }

        return Inertia::render('Panel/Operadores/Create', [
            'operador' => $opArr,
            'empleados' => Empleado::where('empresa_id', $empresaId)
                ->where(function ($q) {
                    $q->where('puesto', 'LIKE', '%operador%')
                        ->orWhere('puesto', 'LIKE', '%Operador%');
                })
                ->get(['id', 'nombre', 'apellido_paterno', 'apellido_materno']),
        ]);
    }

    public function update(StoreOperadorRequest $request, $id)
    {
        $operador = Operadore::where('empresa_id', auth()->user()->empresa_id)->findOrFail($id);

        $data = $request->validated();
        $data['disponible'] = $request->boolean('disponible');
        $operador->update($data);

        return redirect()->route('panel.operadores.index')
            ->with('success', 'Operador actualizado correctamente');
    }

    public function cambiarDisponibilidad(Request $request)
    {
        $request->validate([
            'disponible' => 'required|boolean',
        ]);

        $user = auth()->user();
        if (!$user->empleado_id) {
            return back()->with('error', 'No tienes un perfil de operador asignado.');
        }

        $operador = Operadore::with('empleado')
            ->where('empresa_id', $user->empresa_id)
            ->where('empleado_id', $user->empleado_id)
            ->first();

        if (!$operador) {
            return back()->with('error', 'No tienes un perfil de operador asignado.');
        }

        $operador->update(['disponible' => $request->boolean('disponible')]);

        $estado = $request->boolean('disponible') ? 'disponible' : 'no disponible';
        Notificacione::create([
            'empresa_id' => $operador->empresa_id,
            'usuario_id' => $user->id,
            'mensaje' => "Tu disponibilidad fue actualizada: {$estado}.",
            'canal' => 'sistema_push',
            'estado' => 'pendiente',
        ]);

        Usuario::where('empresa_id', $operador->empresa_id)
            ->whereIn('rol', ['superadmin', 'admin', 'cotizador'])
            ->where('id', '!=', $user->id)
            ->each(function ($usuario) use ($operador, $estado) {
                Notificacione::create([
                    'empresa_id' => $operador->empresa_id,
                    'usuario_id' => $usuario->id,
                    'mensaje' => "El operador {$operador->empleado?->nombre} ahora está {$estado}.",
                    'canal' => 'sistema_push',
                    'estado' => 'pendiente',
                ]);
            });

        return back()->with('success', $request->boolean('disponible')
            ? 'Ahora estás disponible para nuevos servicios.'
            : 'Disponibilidad desactivada.');
    }

    // Borrado físico real del operador (liberando unidades y protegiendo historial de servicios)
    public function destroy($id)
    {
        $operador = Operadore::where('empresa_id', auth()->user()->empresa_id)->findOrFail($id);

        if (Servicio::where('operador_id', $operador->id)->exists()) {
            return back()->with('error', 'No se puede eliminar este operador porque tiene servicios registrados en el historial.');
        }

        DB::transaction(function () use ($operador) {
            Unidade::where('operador_asignado_id', $operador->id)->update(['operador_asignado_id' => null]);
            Auditoria::registrar($operador);
            $operador->delete();
        });

        return redirect()->route('panel.operadores.index')
            ->with('success', 'Operador eliminado físicamente de forma correcta.');
    }
}

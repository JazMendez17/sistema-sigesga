<?php

// Controlador de operadores

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Support\Auditoria;
use App\Models\Operadore;
use App\Models\Empleado;
use App\Http\Requests\Panel\StoreOperadorRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class OperadoresController extends Controller
{
    // Lista de operadores con alertas de licencias
    public function index()
    {
        $user = Auth::user();
        $empresaId = $user->empresa_id;

        $operadores = Operadore::with('empleado')
            ->where('empresa_id', $empresaId)
            ->latest()
            ->get()
            ->map(fn ($o) => [
                'id' => $o->id,
                'nombre' => $o->empleado ? trim(($o->empleado->nombre ?? '') . ' ' . ($o->empleado->apellido_paterno ?? '') . ' ' . ($o->empleado->apellido_materno ?? '')) : '—',
                'tipo_licencia' => $o->tipo_licencia ?? '—',
                'numero_licencia' => $o->numero_licencia ?? '—',
                'fecha_expedicion' => $o->fecha_expedicion ? \Carbon\Carbon::parse($o->fecha_expedicion)->format('Y-m-d') : '—',
                'fecha_vigencia' => $o->fecha_vigencia ? \Carbon\Carbon::parse($o->fecha_vigencia)->format('Y-m-d') : '—',
                'disponible' => $o->disponible ?? true,
            ]);

        $licenciasProximas = $operadores->filter(fn ($o) => $o['fecha_vigencia'] !== '—')->take(10)->map(fn ($o) => [
            'nombre' => $o['nombre'],
            'licencia' => $o['numero_licencia'],
            'vigencia' => $o['fecha_vigencia'],
            'dias' => now()->diffInDays(\Carbon\Carbon::parse($o['fecha_vigencia']), false) ?? 0,
        ])->filter(fn ($l) => $l['dias'] > 0 && $l['dias'] <= 30)->values();

        return Inertia::render('Panel/Operadores/Index', [
            'operadores' => $operadores,
            'licenciasProximas' => $licenciasProximas,
        ]);
    }

    // Formulario para crear operador
    public function create()
    {
        $user = Auth::user();
        $empresaId = $user->empresa_id;

        return Inertia::render('Panel/Operadores/Create', [
            'empleados' => Empleado::where('empresa_id', $empresaId)
                ->where(function ($q) {
                    $q->where('puesto', 'LIKE', '%operador%')
                      ->orWhere('puesto', 'LIKE', '%Operador%');
                })
                ->get(['id', 'nombre', 'apellido_paterno', 'apellido_materno']),
        ]);
    }

    // Guardar operador en base de datos
    public function store(StoreOperadorRequest $request)
    {
        $user = Auth::user();

        $data = $request->validated();
        $data['empresa_id'] = $user->empresa_id;
        $data['disponible'] = $request->boolean('disponible');

        Operadore::create($data);

        return redirect()->route('panel.operadores.index')
            ->with('success', 'Operador creado correctamente');
    }

    // Ver detalle de operador
    public function show($id)
    {
        return Inertia::render('Panel/Operadores/Show', [
            'operador' => Operadore::with('empleado', 'unidad')->where('empresa_id', auth()->user()->empresa_id)->findOrFail($id),
        ]);
    }

    // Formulario para editar operador
    public function edit($id)
    {
        $user = Auth::user();
        $empresaId = $user->empresa_id;

        return Inertia::render('Panel/Operadores/Create', [
            'operador' => Operadore::where('empresa_id', auth()->user()->empresa_id)->findOrFail($id),
            'empleados' => Empleado::where('empresa_id', $empresaId)
                ->where(function ($q) {
                    $q->where('puesto', 'LIKE', '%operador%')
                      ->orWhere('puesto', 'LIKE', '%Operador%');
                })
                ->get(['id', 'nombre', 'apellido_paterno', 'apellido_materno']),
        ]);
    }

    // Actualizar datos del operador
    public function update(StoreOperadorRequest $request, $id)
    {
        $operador = Operadore::where('empresa_id', auth()->user()->empresa_id)->findOrFail($id);

        $data = $request->validated();
        $data['disponible'] = $request->boolean('disponible');
        $operador->update($data);

        return redirect()->route('panel.operadores.index')
            ->with('success', 'Operador actualizado correctamente');
    }

    // Cambiar disponibilidad del operador autenticado (panel del operador)
    public function cambiarDisponibilidad(Request $request)
    {
        $request->validate([
            'disponible' => 'required|boolean',
        ]);

        $operador = Operadore::where('empresa_id', auth()->user()->empresa_id)
            ->where('empleado_id', auth()->user()->empleado_id)
            ->first();

        if (!$operador) {
            return back()->with('error', 'No tienes un perfil de operador asignado.');
        }

        $operador->update(['disponible' => $request->boolean('disponible')]);

        return back()->with('success', $request->boolean('disponible')
            ? 'Ahora estás disponible para nuevos servicios.'
            : 'Disponibilidad desactivada.');
    }

    // Eliminar operador
    public function destroy($id)
    {
        $operadore = Operadore::where('empresa_id', auth()->user()->empresa_id)->findOrFail($id);

        if ($operadore->servicios()->whereIn('estado', ['asignado', 'inicio_servicio', 'en_sitio_origen', 'salida_destino', 'en_destino'])->count() > 0) {
            return redirect()->back()->with('error', 'No se puede eliminar el operador porque tiene servicios activos asignados.');
        }

        Auditoria::registrar($operadore);

        $operadore->update(['eliminado' => true]);

        return redirect()->route('panel.operadores.index')
            ->with('success', 'Operador eliminado correctamente');
    }
}

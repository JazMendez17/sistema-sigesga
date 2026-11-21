<?php

// Controlador de unidades (eager loading sin N+1 y borrado físico real)

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Http\Requests\Panel\StoreUnidadRequest;
use App\Models\Empleado;
use App\Models\Oficina;
use App\Models\Servicio;
use App\Models\Unidade;
use App\Support\Auditoria;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class UnidadesController extends Controller
{
    public function index()
    {
        $empresaId = Auth::user()->empresa_id;

        $unidades = Unidade::with('operadorAsignado.empleado')
            ->where('empresa_id', $empresaId)
            ->latest()
            ->get()
            ->map(fn ($u) => [
                'id' => $u->id,
                'marca' => $u->marca ?? '—',
                'tipo' => $u->tipo ?? '—',
                'modelo' => $u->modelo ?? '—',
                'placas' => $u->placas ?? '—',
                'numero_economico' => $u->numero_economico ?? '—',
                'seguro_vencimiento' => $u->seguro_vencimiento ?? '—',
                'estado_emplacado' => $u->estado_emplacado ?? '—',
                'activo' => (bool) ($u->activo ?? true),
                'oficina_id' => $u->oficina_id,
                'operador_asignado_id' => $u->operador_asignado_id,
                'operador' => $u->operadorAsignado?->empleado?->nombre ?? '—',
            ]);

        return Inertia::render('Panel/Unidades/Index', [
            'unidades' => $unidades,
        ]);
    }

    public function create()
    {
        $empresaId = Auth::user()->empresa_id;

        return Inertia::render('Panel/Unidades/Create', [
            'operadores' => Empleado::with('operador')
                ->where('empresa_id', $empresaId)
                ->where('puesto', 'like', '%operador%')
                ->get()
                ->map(fn ($e) => [
                    'id' => $e->operador?->id ?? null,
                    'empleado_id' => $e->id,
                    'nombre_operador' => trim($e->nombre . ' ' . ($e->apellido_paterno ?? '') . ' ' . ($e->apellido_materno ?? '')),
                    'disponible' => (bool) ($e->operador?->disponible ?? true),
                ])
                ->filter(fn ($o) => $o['id'] !== null)
                ->values(),
            'oficinas' => Oficina::where('empresa_id', $empresaId)->get(['id', 'nombre']),
        ]);
    }

    public function store(StoreUnidadRequest $request)
    {
        $data = $request->validated();
        $data['empresa_id'] = Auth::user()->empresa_id;
        $data['activo'] = $request->has('activo') ? $request->boolean('activo') : true;

        Unidade::create($data);

        return redirect()->route('panel.unidades.index')
            ->with('success', 'Unidad creada correctamente');
    }

    public function show($id)
    {
        return Inertia::render('Panel/Unidades/Show', [
            'unidad' => Unidade::with('operadorAsignado.empleado', 'oficina')
                ->where('empresa_id', auth()->user()->empresa_id)
                ->findOrFail($id),
        ]);
    }

    public function edit($id)
    {
        $empresaId = Auth::user()->empresa_id;

        return Inertia::render('Panel/Unidades/Create', [
            'unidad' => Unidade::where('empresa_id', $empresaId)->findOrFail($id),
            'operadores' => Empleado::with('operador')
                ->where('empresa_id', $empresaId)
                ->where('puesto', 'like', '%operador%')
                ->get()
                ->map(fn ($e) => [
                    'id' => $e->operador?->id ?? null,
                    'empleado_id' => $e->id,
                    'nombre_operador' => trim($e->nombre . ' ' . ($e->apellido_paterno ?? '') . ' ' . ($e->apellido_materno ?? '')),
                    'disponible' => (bool) ($e->operador?->disponible ?? true),
                ])
                ->filter(fn ($o) => $o['id'] !== null)
                ->values(),
            'oficinas' => Oficina::where('empresa_id', $empresaId)->get(['id', 'nombre']),
        ]);
    }

    public function update(StoreUnidadRequest $request, $id)
    {
        $unidad = Unidade::where('empresa_id', auth()->user()->empresa_id)->findOrFail($id);

        $data = $request->validated();
        $data['activo'] = $request->boolean('activo');
        $unidad->update($data);

        return redirect()->route('panel.unidades.index')
            ->with('success', 'Unidad actualizada correctamente');
    }

    public function destroy($id)
    {
        $unidad = Unidade::where('empresa_id', auth()->user()->empresa_id)->findOrFail($id);

        if (Servicio::where('unidad_id', $unidad->id)->exists()) {
            return back()->with('error', 'No se puede eliminar esta unidad porque tiene servicios registrados en el historial. Desactívala en su lugar.');
        }

        DB::transaction(function () use ($unidad) {
            foreach ($unidad->unidadMantenimientos()->get() as $m) {
                Auditoria::registrar($m);
            }
            Auditoria::registrar($unidad);
            $unidad->delete();
        });

        return redirect()->route('panel.unidades.index')
            ->with('success', 'Unidad eliminada físicamente de forma correcta.');
    }
}

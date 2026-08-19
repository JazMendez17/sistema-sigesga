<?php

// Controlador de mantenimientos de unidades

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Support\Auditoria;
use App\Models\UnidadMantenimiento;
use App\Models\Unidade;
use App\Http\Requests\Panel\StoreMantenimientoRequest;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class MantenimientosController extends Controller
{
    // Lista de mantenimientos con alertas de vencimiento
    public function index()
    {
        $user = Auth::user();
        $empresaId = $user->empresa_id;

        $unidadIds = Unidade::where('empresa_id', $empresaId)->pluck('id');

        $mantenimientos = UnidadMantenimiento::with('unidad')
            ->whereIn('unidad_id', $unidadIds)
            ->orderBy('fecha', 'desc')
            ->get()
            ->map(fn ($m) => [
                'id' => $m->id,
                'unidad' => $m->unidad?->nombre ?? '—',
                'tipo' => $m->tipo ?? '—',
                'fecha' => $m->fecha,
                'kilometraje' => $m->kilometraje ?? '—',
                'costo' => (float) ($m->costo ?? 0),
                'proximo_mantenimiento_fecha' => $m->proximo_mantenimiento_fecha,
                'proximo_mantenimiento_km' => $m->proximo_mantenimiento_km,
                'observaciones' => $m->observaciones ?? '—',
            ]);

        $alertas = collect();
        if ($mantenimientos->isNotEmpty()) {
            $alertas = $mantenimientos->filter(fn ($m) => $m['proximo_mantenimiento_fecha'] !== null)->map(fn ($m) => [
                'unidad' => $m['unidad'],
                'tipo' => $m['tipo'],
                'vence' => $m['proximo_mantenimiento_fecha'],
                'dias' => now()->diffInDays(\Carbon\Carbon::parse($m['proximo_mantenimiento_fecha']), false) ?? 0,
            ])->filter(fn ($a) => $a['dias'] > 0 && $a['dias'] <= 30)->values();
        }

        return Inertia::render('Panel/Mantenimientos/Index', [
            'mantenimientos' => $mantenimientos,
            'unidades' => Unidade::where('empresa_id', $empresaId)->get(['id', 'placas', 'numero_economico']),
            'alertas' => $alertas,
        ]);
    }

    // Formulario para registrar mantenimiento
    public function create()
    {
        $user = Auth::user();
        $empresaId = $user->empresa_id;

        return Inertia::render('Panel/Mantenimientos/Create', [
            'unidades' => Unidade::where('empresa_id', $empresaId)->get(['id', 'placas', 'numero_economico']),
        ]);
    }

    // Guardar mantenimiento en base de datos
    public function store(StoreMantenimientoRequest $request)
    {
        $data = $request->validated();
        $data['empresa_id'] = auth()->user()->empresa_id;

        UnidadMantenimiento::create($data);

        return redirect()->route('panel.mantenimientos.index')
            ->with('success', 'Mantenimiento registrado correctamente');
    }

    // Ver detalle de mantenimiento
    public function show($id)
    {
        return Inertia::render('Panel/Mantenimientos/Show', [
            'mantenimiento' => UnidadMantenimiento::with('unidad')->whereHas('unidad', fn($q) => $q->where('empresa_id', auth()->user()->empresa_id))->findOrFail($id),
        ]);
    }

    // Formulario para editar mantenimiento
    public function edit($id)
    {
        $user = Auth::user();
        $empresaId = $user->empresa_id;

        return Inertia::render('Panel/Mantenimientos/Create', [
            'mantenimiento' => UnidadMantenimiento::with('unidad')->whereHas('unidad', fn($q) => $q->where('empresa_id', auth()->user()->empresa_id))->findOrFail($id),
            'unidades' => Unidade::where('empresa_id', $empresaId)->get(['id', 'placas', 'numero_economico']),
        ]);
    }

    // Actualizar datos del mantenimiento
    public function update(StoreMantenimientoRequest $request, $id)
    {
        $mantenimiento = UnidadMantenimiento::whereHas('unidad', fn($q) => $q->where('empresa_id', auth()->user()->empresa_id))->findOrFail($id);

        $data = $request->validated();

        $mantenimiento->update($data);

        return redirect()->route('panel.mantenimientos.index')
            ->with('success', 'Mantenimiento actualizado correctamente');
    }

    // Eliminar mantenimiento
    public function destroy($id)
    {
        $mantenimiento = UnidadMantenimiento::whereHas('unidad', fn($q) => $q->where('empresa_id', auth()->user()->empresa_id))->findOrFail($id);

        Auditoria::registrar($mantenimiento);

        $mantenimiento->delete();

        return redirect()->route('panel.mantenimientos.index')
            ->with('success', 'Mantenimiento eliminado correctamente');
    }
}

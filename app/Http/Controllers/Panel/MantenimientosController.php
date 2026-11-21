<?php

// Controlador de mantenimientos de unidades (Carbon 3 int y borrado físico real)

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Http\Requests\Panel\StoreMantenimientoRequest;
use App\Models\Unidade;
use App\Models\UnidadMantenimiento;
use App\Support\Auditoria;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class MantenimientosController extends Controller
{
    public function index()
    {
        $empresaId = Auth::user()->empresa_id;
        $unidadIds = Unidade::where('empresa_id', $empresaId)->pluck('id');

        $mantenimientos = UnidadMantenimiento::with('unidad')
            ->whereIn('unidad_id', $unidadIds)
            ->orderBy('fecha', 'desc')
            ->get()
            ->map(fn ($m) => [
                'id' => $m->id,
                'unidad' => $m->unidad ? trim(($m->unidad->numero_economico ?? '') . ' - ' . ($m->unidad->placas ?? '')) : '—',
                'tipo' => $m->tipo ?? '—',
                'fecha' => $m->fecha,
                'kilometraje' => $m->kilometraje ?? '—',
                'costo' => (float) ($m->costo ?? 0),
                'proximo_mantenimiento_fecha' => $m->proximo_mantenimiento_fecha,
                'proximo_mantenimiento_km' => $m->proximo_mantenimiento_km,
                'observaciones' => $m->observaciones ?? '—',
            ]);

        $hoy = now()->startOfDay();
        $alertas = $mantenimientos
            ->filter(fn ($m) => !empty($m['proximo_mantenimiento_fecha']))
            ->map(fn ($m) => [
                'unidad' => $m['unidad'],
                'tipo' => $m['tipo'],
                'vence' => $m['proximo_mantenimiento_fecha'],
                'dias' => (int) $hoy->diffInDays(Carbon::parse($m['proximo_mantenimiento_fecha'])->startOfDay(), false),
            ])
            ->filter(fn ($a) => $a['dias'] <= 30)
            ->sortBy('dias')
            ->values();

        return Inertia::render('Panel/Mantenimientos/Index', [
            'mantenimientos' => $mantenimientos,
            'unidades' => Unidade::where('empresa_id', $empresaId)->get(['id', 'placas', 'numero_economico']),
            'alertas' => $alertas,
        ]);
    }

    public function create()
    {
        $empresaId = Auth::user()->empresa_id;

        return Inertia::render('Panel/Mantenimientos/Create', [
            'unidades' => Unidade::where('empresa_id', $empresaId)->get(['id', 'placas', 'numero_economico']),
        ]);
    }

    public function store(StoreMantenimientoRequest $request)
    {
        $data = $request->validated();
        $data['empresa_id'] = auth()->user()->empresa_id;

        UnidadMantenimiento::create($data);

        return redirect()->route('panel.mantenimientos.index')
            ->with('success', 'Mantenimiento registrado correctamente');
    }

    public function show($id)
    {
        return Inertia::render('Panel/Mantenimientos/Show', [
            'mantenimiento' => UnidadMantenimiento::with('unidad')
                ->whereHas('unidad', fn ($q) => $q->where('empresa_id', auth()->user()->empresa_id))
                ->findOrFail($id),
        ]);
    }

    public function edit($id)
    {
        $empresaId = Auth::user()->empresa_id;

        return Inertia::render('Panel/Mantenimientos/Create', [
            'mantenimiento' => UnidadMantenimiento::with('unidad')
                ->whereHas('unidad', fn ($q) => $q->where('empresa_id', $empresaId))
                ->findOrFail($id),
            'unidades' => Unidade::where('empresa_id', $empresaId)->get(['id', 'placas', 'numero_economico']),
        ]);
    }

    public function update(StoreMantenimientoRequest $request, $id)
    {
        $mantenimiento = UnidadMantenimiento::whereHas('unidad', fn ($q) => $q->where('empresa_id', auth()->user()->empresa_id))
            ->findOrFail($id);

        $mantenimiento->update($request->validated());

        return redirect()->route('panel.mantenimientos.index')
            ->with('success', 'Mantenimiento actualizado correctamente');
    }

    public function destroy($id)
    {
        $mantenimiento = UnidadMantenimiento::whereHas('unidad', fn ($q) => $q->where('empresa_id', auth()->user()->empresa_id))
            ->findOrFail($id);

        DB::transaction(function () use ($mantenimiento) {
            Auditoria::registrar($mantenimiento);
            $mantenimiento->delete();
        });

        return redirect()->route('panel.mantenimientos.index')
            ->with('success', 'Mantenimiento eliminado físicamente de forma correcta.');
    }
}

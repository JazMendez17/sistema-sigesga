<?php

// Controlador de tarifas propias (borrado físico y defaults NOT NULL)

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Http\Requests\Panel\StoreTarifaPropiaRequest;
use App\Models\CatalogoServicio;
use App\Models\TarifasEmpresa;
use App\Support\Auditoria;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class TarifasPropiasController extends Controller
{
    public function index()
    {
        $empresaId = Auth::user()->empresa_id;

        $tarifas = TarifasEmpresa::with('tipoServicio')
            ->where('empresa_id', $empresaId)
            ->latest()
            ->get()
            ->map(fn ($t) => [
                'id' => $t->id,
                'nombre' => $t->nombre_tarifa ?? '—',
                'tipo_servicio' => $t->tipoServicio?->nombre ?? $t->servicio ?? '—',
                'ruta' => $t->tipo_ruta ?? '—',
                'banderazo' => (float) ($t->costo_banderazo ?? 0),
                'costo_km' => (float) ($t->costo_km_extra ?? $t->costo_km ?? 0),
                'activo' => (bool) ($t->activo ?? true),
            ]);

        return Inertia::render('Panel/TarifasPropias/Index', [
            'tarifas' => $tarifas,
        ]);
    }

    public function create()
    {
        $empresaId = Auth::user()->empresa_id;

        return Inertia::render('Panel/TarifasPropias/Create', [
            'tiposServicio' => CatalogoServicio::where('empresa_id', $empresaId)->get(['id', 'nombre']),
        ]);
    }

    public function store(StoreTarifaPropiaRequest $request)
    {
        $data = $request->validated();
        $data['empresa_id'] = Auth::user()->empresa_id;
        $data['km_incluidos'] = $data['km_incluidos'] ?? 0;
        $data['activo'] = $request->has('activo') ? $request->boolean('activo') : true;
        $data['cubre_casetas_peaje'] = $request->boolean('cubre_casetas_peaje');

        TarifasEmpresa::create($data);

        return redirect()->route('panel.tarifas-propias.index')
            ->with('success', 'Tarifa creada correctamente');
    }

    public function edit($id)
    {
        $empresaId = Auth::user()->empresa_id;

        return Inertia::render('Panel/TarifasPropias/Create', [
            'tarifa' => TarifasEmpresa::where('empresa_id', $empresaId)->findOrFail($id),
            'tiposServicio' => CatalogoServicio::where('empresa_id', $empresaId)->get(['id', 'nombre']),
        ]);
    }

    public function update(StoreTarifaPropiaRequest $request, $id)
    {
        $tarifa = TarifasEmpresa::where('empresa_id', auth()->user()->empresa_id)->findOrFail($id);

        $data = $request->validated();
        $data['km_incluidos'] = $data['km_incluidos'] ?? 0;
        $data['activo'] = $request->boolean('activo');
        $data['cubre_casetas_peaje'] = $request->boolean('cubre_casetas_peaje');
        $tarifa->update($data);

        return redirect()->route('panel.tarifas-propias.index')
            ->with('success', 'Tarifa actualizada correctamente');
    }

    public function destroy($id)
    {
        $tarifa = TarifasEmpresa::where('empresa_id', auth()->user()->empresa_id)->findOrFail($id);

        DB::transaction(function () use ($tarifa) {
            Auditoria::registrar($tarifa);
            $tarifa->delete();
        });

        return redirect()->route('panel.tarifas-propias.index')
            ->with('success', 'Tarifa eliminada físicamente de forma correcta.');
    }
}

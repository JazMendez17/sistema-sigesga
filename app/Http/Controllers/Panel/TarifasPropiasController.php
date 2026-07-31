<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\TarifasEmpresa;
use App\Models\CatalogoServicio;
use App\Http\Requests\Panel\StoreTarifaPropiaRequest;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class TarifasPropiasController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $empresaId = $user->empresa_id;

        $tarifas = TarifasEmpresa::with('tipoServicio')
            ->where('empresa_id', $empresaId)
            ->latest()
            ->get()
            ->map(fn ($t) => [
                'id' => $t->id,
                'nombre' => $t->nombre_tarifa ?? '—',
                'tipo_servicio' => $t->tipoServicio?->nombre ?? '—',
                'ruta' => $t->tipo_ruta ?? '—',
                'banderazo' => (float) ($t->costo_banderazo ?? 0),
                'costo_km' => (float) ($t->costo_km ?? 0),
                'activo' => $t->activo ?? true,
            ]);

        return Inertia::render('Panel/TarifasPropias/Index', [
            'tarifas' => $tarifas,
        ]);
    }

    public function create()
    {
        $user = Auth::user();
        $empresaId = $user->empresa_id;

        return Inertia::render('Panel/TarifasPropias/Create', [
            'tiposServicio' => CatalogoServicio::where('empresa_id', $empresaId)->get(['id', 'nombre']),
        ]);
    }

    public function store(StoreTarifaPropiaRequest $request)
    {
        $user = Auth::user();

        $data = $request->validated();
        $data['empresa_id'] = $user->empresa_id;
        $data['activo'] = $request->boolean('activo');
        $data['cubre_casetas_peaje'] = $request->boolean('cubre_casetas_peaje');

        TarifasEmpresa::create($data);

        return redirect()->route('panel.tarifas-propias.index')
            ->with('success', 'Tarifa creada correctamente');
    }

    public function edit($id)
    {
        $user = Auth::user();
        $empresaId = $user->empresa_id;

        return Inertia::render('Panel/TarifasPropias/Create', [
            'tarifa' => TarifasEmpresa::where('empresa_id', auth()->user()->empresa_id)->findOrFail($id),
            'tiposServicio' => CatalogoServicio::where('empresa_id', $empresaId)->get(['id', 'nombre']),
        ]);
    }

    public function update(StoreTarifaPropiaRequest $request, $id)
    {
        $tarifa = TarifasEmpresa::where('empresa_id', auth()->user()->empresa_id)->findOrFail($id);

        $data = $request->validated();
        $data['activo'] = $request->boolean('activo');
        $data['cubre_casetas_peaje'] = $request->boolean('cubre_casetas_peaje');
        $tarifa->update($data);

        return redirect()->route('panel.tarifas-propias.index')
            ->with('success', 'Tarifa actualizada correctamente');
    }

    public function destroy($id)
    {
        TarifasEmpresa::where('empresa_id', auth()->user()->empresa_id)->findOrFail($id)->delete();

        return redirect()->route('panel.tarifas-propias.index')
            ->with('success', 'Tarifa eliminada correctamente');
    }
}

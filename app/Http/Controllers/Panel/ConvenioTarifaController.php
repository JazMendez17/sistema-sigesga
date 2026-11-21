<?php

// Controlador para gestión de tarifas de convenio (multi-tenant estricto + borrado físico)

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Http\Requests\Panel\StoreConvenioTarifaRequest;
use App\Models\CatalogoServicio;
use App\Models\Convenio;
use App\Models\ConvenioTarifa;
use App\Support\Auditoria;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ConvenioTarifaController extends Controller
{
    public function index()
    {
        $empresaId = Auth::user()->empresa_id;

        $tarifas = ConvenioTarifa::with(['convenio.aseguradora', 'tipoServicio'])
            ->whereHas('convenio', fn ($q) => $q->where('empresa_id', $empresaId))
            ->latest()
            ->get()
            ->map(fn ($t) => [
                'id' => $t->id,
                'convenio' => $t->convenio?->nombre_convenio_poliza ?? '—',
                'aseguradora' => $t->convenio?->aseguradora?->nombre ?? '—',
                'servicio' => $t->servicio ?? $t->tipoServicio?->nombre ?? '—',
                'alcance' => $t->alcance ?? '—',
                'banderazo' => (float) $t->banderazo,
                'km_incluidos' => (int) $t->km_incluidos,
                'costo_km_extra' => (float) $t->costo_km_extra,
                'tarifa_nocturna_recargo_pct' => (float) $t->tarifa_nocturna_recargo_pct,
                'tarifa_domingo_festivo_recargo_pct' => (float) $t->tarifa_domingo_festivo_recargo_pct,
                'minutos_espera_incluidos' => (int) $t->minutos_espera_incluidos,
                'costo_espera_adicional_hora' => (float) $t->costo_espera_adicional_hora,
                'descuento_pct' => (float) $t->descuento_pct,
                'tipo_descuento' => $t->tipo_descuento ?? '—',
            ]);

        return Inertia::render('Panel/ConvenioTarifas/Index', [
            'tarifas' => $tarifas,
        ]);
    }

    public function create()
    {
        $empresaId = Auth::user()->empresa_id;

        return Inertia::render('Panel/ConvenioTarifas/Create', [
            'convenios' => Convenio::where('empresa_id', $empresaId)->get(['id', 'nombre_convenio_poliza', 'aseguradora_id']),
            'tiposServicio' => CatalogoServicio::where('empresa_id', $empresaId)->where('activo', true)->get(['id', 'nombre']),
        ]);
    }

    public function store(StoreConvenioTarifaRequest $request)
    {
        ConvenioTarifa::create($request->validated());

        return redirect()->route('panel.convenio-tarifas.index')
            ->with('success', 'Tarifa de convenio creada correctamente');
    }

    public function edit($id)
    {
        $empresaId = Auth::user()->empresa_id;

        $tarifa = ConvenioTarifa::whereHas('convenio', fn ($q) => $q->where('empresa_id', $empresaId))
            ->findOrFail($id);

        return Inertia::render('Panel/ConvenioTarifas/Create', [
            'tarifa' => $tarifa,
            'convenios' => Convenio::where('empresa_id', $empresaId)->get(['id', 'nombre_convenio_poliza', 'aseguradora_id']),
            'tiposServicio' => CatalogoServicio::where('empresa_id', $empresaId)->where('activo', true)->get(['id', 'nombre']),
        ]);
    }

    public function update(StoreConvenioTarifaRequest $request, $id)
    {
        $empresaId = Auth::user()->empresa_id;

        $tarifa = ConvenioTarifa::whereHas('convenio', fn ($q) => $q->where('empresa_id', $empresaId))
            ->findOrFail($id);

        $tarifa->update($request->validated());

        return redirect()->route('panel.convenio-tarifas.index')
            ->with('success', 'Tarifa de convenio actualizada correctamente');
    }

    public function destroy($id)
    {
        $empresaId = Auth::user()->empresa_id;

        $tarifa = ConvenioTarifa::whereHas('convenio', fn ($q) => $q->where('empresa_id', $empresaId))
            ->findOrFail($id);

        DB::transaction(function () use ($tarifa) {
            Auditoria::registrar($tarifa);
            $tarifa->delete();
        });

        return redirect()->route('panel.convenio-tarifas.index')
            ->with('success', 'Tarifa de convenio eliminada físicamente de forma correcta.');
    }
}

<?php

// Controlador para gestión de tarifas de convenio

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Support\Auditoria;
use App\Http\Requests\Panel\StoreConvenioTarifaRequest;
use App\Models\ConvenioTarifa;
use App\Models\Convenio;
use App\Models\CatalogoServicio;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ConvenioTarifaController extends Controller
{
    // Listado de tarifas de convenio
    public function index()
    {
        $user = Auth::user();
        $empresaId = $user->empresa_id;

        $tarifas = ConvenioTarifa::with(['convenio.aseguradora', 'tipoServicio'])
            ->whereHas('convenio', fn($q) => $q->where('empresa_id', $empresaId))
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

    // Formulario para crear tarifa
    public function create()
    {
        $user = Auth::user();
        $empresaId = $user->empresa_id;

        return Inertia::render('Panel/ConvenioTarifas/Create', [
            'convenios' => Convenio::where('empresa_id', $empresaId)->get(['id', 'nombre_convenio_poliza', 'aseguradora_id']),
            'tiposServicio' => CatalogoServicio::where('activo', true)->get(['id', 'nombre']),
        ]);
    }

    // Guardar tarifa en base de datos
    public function store(StoreConvenioTarifaRequest $request)
    {
        ConvenioTarifa::create($request->validated());

        return redirect()->route('panel.convenio-tarifas.index')
            ->with('success', 'Tarifa de convenio creada correctamente');
    }

    // Formulario para editar tarifa
    public function edit($id)
    {
        $user = Auth::user();
        $empresaId = $user->empresa_id;

        return Inertia::render('Panel/ConvenioTarifas/Create', [
            'tarifa' => ConvenioTarifa::findOrFail($id),
            'convenios' => Convenio::where('empresa_id', $empresaId)->get(['id', 'nombre_convenio_poliza', 'aseguradora_id']),
            'tiposServicio' => CatalogoServicio::where('activo', true)->get(['id', 'nombre']),
        ]);
    }

    // Actualizar datos de la tarifa
    public function update(StoreConvenioTarifaRequest $request, $id)
    {
        $tarifa = ConvenioTarifa::findOrFail($id);
        $tarifa->update($request->validated());

        return redirect()->route('panel.convenio-tarifas.index')
            ->with('success', 'Tarifa de convenio actualizada correctamente');
    }

    // Eliminar tarifa
    public function destroy($id)
    {
        $tarifa = ConvenioTarifa::findOrFail($id);

        Auditoria::registrar($tarifa);

        $tarifa->eliminar();

        return redirect()->route('panel.convenio-tarifas.index')
            ->with('success', 'Tarifa de convenio eliminada correctamente');
    }
}

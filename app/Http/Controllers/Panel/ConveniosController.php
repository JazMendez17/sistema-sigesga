<?php

// Controlador de convenios (borrado físico y defaults NOT NULL)

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Http\Requests\Panel\StoreConvenioRequest;
use App\Models\Aseguradora;
use App\Models\CatalogoServicio;
use App\Models\Convenio;
use App\Models\ConvenioTarifa;
use App\Services\ImportaConvenios;
use App\Support\Auditoria;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ConveniosController extends Controller
{
    public function index()
    {
        $empresaId = Auth::user()->empresa_id;

        $convenios = Convenio::with(['aseguradora', 'tipoServicio', 'convenioTarifas'])
            ->where('empresa_id', $empresaId)
            ->latest()
            ->get()
            ->map(fn ($c) => [
                'id' => $c->id,
                'codigo' => $c->codigo_convenio ?? '—',
                'nombre' => $c->nombre_convenio_poliza ?? '—',
                'aseguradora' => $c->aseguradora?->nombre ?? '—',
                'tipo_servicio' => $c->tipoServicio?->nombre ?? '—',
                'tipo_cobertura' => $c->tipo_cobertura ?? '—',
                'tiene_tarifas' => $c->convenioTarifas->isNotEmpty(),
                'fecha_inicio' => $c->fecha_inicio ?? '—',
                'fecha_fin' => $c->fecha_fin ?? '—',
                'estatus' => $c->estatus ?? 'vigente',
            ]);

        $tarifasGlobales = ConvenioTarifa::with('convenio')
            ->whereHas('convenio', fn ($q) => $q->where('empresa_id', $empresaId))
            ->get()
            ->map(fn ($t) => [
                'id' => $t->id,
                'convenio' => $t->convenio?->nombre_convenio_poliza ?? '—',
                'convenio_id' => $t->convenio_id,
                'servicio' => $t->servicio ?? '—',
                'alcance' => $t->alcance ?? '—',
                'banderazo' => (float) $t->banderazo,
                'km_incluidos' => $t->km_incluidos,
                'costo_km_extra' => (float) $t->costo_km_extra,
                'tarifa_nocturna_recargo_pct' => $t->tarifa_nocturna_recargo_pct,
                'tarifa_domingo_festivo_recargo_pct' => $t->tarifa_domingo_festivo_recargo_pct,
                'minutos_espera_incluidos' => $t->minutos_espera_incluidos,
                'costo_espera_adicional_hora' => (float) $t->costo_espera_adicional_hora,
                'descuento_pct' => $t->descuento_pct,
                'tipo_descuento' => $t->tipo_descuento ?? '—',
            ]);

        return Inertia::render('Panel/Convenios/Index', [
            'convenios' => $convenios,
            'tarifasGlobales' => $tarifasGlobales,
            'aseguradoras' => Aseguradora::where('empresa_id', $empresaId)->get(['id', 'nombre']),
            'tiposServicio' => CatalogoServicio::where('empresa_id', $empresaId)->get(['id', 'nombre']),
            'tiposCobertura' => Convenio::where('empresa_id', $empresaId)->whereNotNull('tipo_cobertura')->where('tipo_cobertura', '!=', '')->pluck('tipo_cobertura')->unique()->values(),
            'machote' => ['hojas' => ImportaConvenios::COLUMNAS],
        ]);
    }

    public function create()
    {
        $empresaId = Auth::user()->empresa_id;

        return Inertia::render('Panel/Convenios/Create', [
            'aseguradoras' => Aseguradora::where('empresa_id', $empresaId)->get(['id', 'nombre']),
        ]);
    }

    public function store(StoreConvenioRequest $request)
    {
        $data = $request->validated();
        $data['empresa_id'] = Auth::user()->empresa_id;
        $data['estatus'] = $data['estatus'] ?? 'vigente';
        $data['km_seguros_incluidos'] = $data['km_seguros_incluidos'] ?? 0;
        $data['cubre_casetas_peaje'] = $request->boolean('cubre_casetas_peaje');
        $data['renovacion_automatica'] = $request->boolean('renovacion_automatica');
        $data['exclusivo'] = $request->boolean('exclusivo');
        $data['requiere_folio_cfdi'] = $request->boolean('requiere_folio_cfdi');
        $data['iva_incluido'] = $request->boolean('iva_incluido');

        Convenio::create($data);

        return redirect()->route('panel.convenios.index')
            ->with('success', 'Convenio creado correctamente');
    }

    public function show($id)
    {
        $convenio = Convenio::with(['aseguradora'])
            ->where('empresa_id', auth()->user()->empresa_id)
            ->findOrFail($id);

        return Inertia::render('Panel/Convenios/Show', [
            'convenio' => [
                'id' => $convenio->id,
                'nombre' => $convenio->nombre_convenio_poliza ?? '—',
                'codigo_convenio' => $convenio->codigo_convenio ?? '—',
                'aseguradora' => $convenio->aseguradora?->nombre ?? '—',
                'fecha_inicio' => $convenio->fecha_inicio ?? '—',
                'fecha_fin' => $convenio->fecha_fin ?? '—',
                'estatus' => $convenio->estatus ?? '—',
                'renovacion_automatica' => (bool) $convenio->renovacion_automatica,
                'exclusivo' => (bool) $convenio->exclusivo,
                'dias_credito' => $convenio->dias_credito ?? '—',
                'periodicidad_corte' => $convenio->periodicidad_corte ?? '—',
                'aviso_previo_terminacion_dias' => $convenio->aviso_previo_terminacion_dias ?? '—',
                'requiere_folio_cfdi' => (bool) $convenio->requiere_folio_cfdi,
                'iva_incluido' => (bool) $convenio->iva_incluido,
                'tope_credito' => $convenio->tope_credito,
            ],
        ]);
    }

    public function edit($id)
    {
        $empresaId = Auth::user()->empresa_id;
        $convenio = Convenio::where('empresa_id', $empresaId)->findOrFail($id);

        return Inertia::render('Panel/Convenios/Create', [
            'convenio' => $convenio,
            'aseguradoras' => Aseguradora::where('empresa_id', $empresaId)->get(['id', 'nombre']),
            'tiposServicio' => CatalogoServicio::where('empresa_id', $empresaId)->get(['id', 'nombre']),
        ]);
    }

    public function update(StoreConvenioRequest $request, $id)
    {
        $convenio = Convenio::where('empresa_id', auth()->user()->empresa_id)->findOrFail($id);

        $data = $request->validated();
        $data['estatus'] = $data['estatus'] ?? $convenio->estatus ?? 'vigente';
        $data['cubre_casetas_peaje'] = $request->boolean('cubre_casetas_peaje');
        $data['renovacion_automatica'] = $request->boolean('renovacion_automatica');
        $data['exclusivo'] = $request->boolean('exclusivo');
        $data['requiere_folio_cfdi'] = $request->boolean('requiere_folio_cfdi');
        $data['iva_incluido'] = $request->boolean('iva_incluido');

        $convenio->update($data);

        return redirect()->route('panel.convenios.index')
            ->with('success', 'Convenio actualizado correctamente');
    }

    public function destroy($id)
    {
        $convenio = Convenio::where('empresa_id', auth()->user()->empresa_id)->findOrFail($id);

        DB::transaction(function () use ($convenio) {
            Auditoria::registrar($convenio);
            $convenio->delete();
        });

        return redirect()->route('panel.convenios.index')
            ->with('success', 'Convenio eliminado físicamente de forma correcta.');
    }
}

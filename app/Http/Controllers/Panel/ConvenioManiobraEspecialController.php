<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Http\Requests\Panel\StoreManiobraEspecialRequest;
use App\Models\Convenio;
use App\Models\ConvenioManiobrasEspeciale;
use App\Support\Auditoria;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

// Controlador para maniobras especiales por convenio (multi-tenant estricto + borrado físico)
class ConvenioManiobraEspecialController extends Controller
{
    public function index()
    {
        $empresaId = Auth::user()->empresa_id;

        $maniobras = ConvenioManiobrasEspeciale::with('convenio.aseguradora')
            ->whereHas('convenio', fn ($q) => $q->where('empresa_id', $empresaId))
            ->orderBy('id', 'desc')->get()
            ->map(fn ($m) => [
                'id' => $m->id,
                'convenio' => $m->convenio?->nombre_convenio_poliza ?? '—',
                'aseguradora' => $m->convenio?->aseguradora?->nombre ?? '—',
                'concepto' => $m->concepto,
                'aplica' => (bool) $m->aplica,
                'forma_cobro' => $m->forma_cobro ?? '—',
                'costo' => (float) $m->costo,
            ]);

        return Inertia::render('Panel/ServiciosEspeciales/Index', ['maniobras' => $maniobras]);
    }

    public function create()
    {
        return Inertia::render('Panel/ServiciosEspeciales/Create', [
            'convenios' => Convenio::where('empresa_id', auth()->user()->empresa_id)->get(['id', 'nombre_convenio_poliza']),
        ]);
    }

    public function store(StoreManiobraEspecialRequest $request)
    {
        $data = $request->validated();
        $data['aplica'] = $request->has('aplica') ? $request->boolean('aplica') : true;

        ConvenioManiobrasEspeciale::create($data);

        return redirect()->route('panel.servicios-especiales.index')->with('success', 'Maniobra especial creada.');
    }

    public function edit($id)
    {
        $empresaId = auth()->user()->empresa_id;
        $maniobra = ConvenioManiobrasEspeciale::whereHas('convenio', fn ($q) => $q->where('empresa_id', $empresaId))
            ->findOrFail($id);

        return Inertia::render('Panel/ServiciosEspeciales/Create', [
            'maniobra' => $maniobra,
            'convenios' => Convenio::where('empresa_id', $empresaId)->get(['id', 'nombre_convenio_poliza']),
        ]);
    }

    public function update(StoreManiobraEspecialRequest $request, $id)
    {
        $empresaId = auth()->user()->empresa_id;
        $maniobra = ConvenioManiobrasEspeciale::whereHas('convenio', fn ($q) => $q->where('empresa_id', $empresaId))
            ->findOrFail($id);

        $data = $request->validated();
        $data['aplica'] = $request->has('aplica') ? $request->boolean('aplica') : (bool) $maniobra->aplica;

        $maniobra->update($data);

        return redirect()->route('panel.servicios-especiales.index')->with('success', 'Maniobra especial actualizada.');
    }

    public function destroy($id)
    {
        $empresaId = auth()->user()->empresa_id;
        $maniobra = ConvenioManiobrasEspeciale::whereHas('convenio', fn ($q) => $q->where('empresa_id', $empresaId))
            ->findOrFail($id);

        DB::transaction(function () use ($maniobra) {
            Auditoria::registrar($maniobra);
            $maniobra->delete();
        });

        return redirect()->route('panel.servicios-especiales.index')->with('success', 'Maniobra especial eliminada físicamente.');
    }
}

<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Support\Auditoria;
use App\Http\Requests\Panel\StoreManiobraEspecialRequest;
use App\Models\ConvenioManiobrasEspeciale;
use App\Models\Convenio;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

// Controlador para la gestión de maniobras especiales por convenio
class ConvenioManiobraEspecialController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $empresaId = $user->empresa_id;

        $maniobras = ConvenioManiobrasEspeciale::with('convenio.aseguradora')
            ->whereHas('convenio', fn($q) => $q->where('empresa_id', $empresaId))
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
        ConvenioManiobrasEspeciale::create($request->validated());
        return redirect()->route('panel.servicios-especiales.index')->with('success', 'Maniobra especial creada.');
    }

    public function edit($id)
    {
        return Inertia::render('Panel/ServiciosEspeciales/Create', [
            'maniobra' => ConvenioManiobrasEspeciale::findOrFail($id),
            'convenios' => Convenio::where('empresa_id', auth()->user()->empresa_id)->get(['id', 'nombre_convenio_poliza']),
        ]);
    }

    public function update(StoreManiobraEspecialRequest $request, $id)
    {
        ConvenioManiobrasEspeciale::findOrFail($id)->update($request->validated());
        return redirect()->route('panel.servicios-especiales.index')->with('success', 'Maniobra especial actualizada.');
    }

    public function destroy($id)
    {
        $maniobra = ConvenioManiobrasEspeciale::findOrFail($id);

        Auditoria::registrar($maniobra);

        $maniobra->eliminar();
        return redirect()->route('panel.servicios-especiales.index')->with('success', 'Maniobra especial eliminada.');
    }
}

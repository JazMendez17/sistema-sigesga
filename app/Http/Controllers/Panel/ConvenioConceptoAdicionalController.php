<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Support\Auditoria;
use App\Models\ConvenioConceptoAdicional;
use App\Models\Convenio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

// Controlador para gestión de conceptos adicionales por convenio
class ConvenioConceptoAdicionalController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $empresaId = $user->empresa_id;

        $conceptos = ConvenioConceptoAdicional::with('convenio.aseguradora')
            ->whereHas('convenio', fn($q) => $q->where('empresa_id', $empresaId))
            ->get()
            ->map(fn ($c) => [
                'id' => $c->id,
                'convenio' => $c->convenio?->nombre_convenio_poliza ?? '—',
                'aseguradora' => $c->convenio?->aseguradora?->nombre ?? '—',
                'cubre_casetas' => (bool) $c->cubre_casetas,
                'forma_pago_casetas' => $c->forma_pago_casetas ?? '—',
                'costo_estadia_dia' => (float) $c->costo_estadia_dia,
                'dias_gracia_estadia' => (int) $c->dias_gracia_estadia,
                'costo_resguardo_nocturno' => (float) $c->costo_resguardo_nocturno,
                'genera_cargo_cliente_final' => (bool) $c->genera_cargo_cliente_final,
            ]);

        return Inertia::render('Panel/ConceptosAdicionales/Index', ['conceptos' => $conceptos]);
    }

    public function create()
    {
        return Inertia::render('Panel/ConceptosAdicionales/Create', [
            'convenios' => Convenio::where('empresa_id', auth()->user()->empresa_id)->get(['id', 'nombre_convenio_poliza']),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateRequest($request);
        ConvenioConceptoAdicional::create($data);
        return redirect()->route('panel.conceptos-adicionales.index')->with('success', 'Conceptos adicionales creados.');
    }

    public function edit($id)
    {
        return Inertia::render('Panel/ConceptosAdicionales/Create', [
            'concepto' => ConvenioConceptoAdicional::findOrFail($id),
            'convenios' => Convenio::where('empresa_id', auth()->user()->empresa_id)->get(['id', 'nombre_convenio_poliza']),
        ]);
    }

    public function update(Request $request, $id)
    {
        $data = $this->validateRequest($request, $id);
        ConvenioConceptoAdicional::findOrFail($id)->update($data);
        return redirect()->route('panel.conceptos-adicionales.index')->with('success', 'Conceptos adicionales actualizados.');
    }

    public function destroy($id)
    {
        $concepto = ConvenioConceptoAdicional::findOrFail($id);

        Auditoria::registrar($concepto);

        $concepto->eliminar();
        return redirect()->route('panel.conceptos-adicionales.index')->with('success', 'Conceptos adicionales eliminados.');
    }

    protected function validateRequest(Request $request, $id = null)
    {
        return $request->validate([
            'convenio_id' => 'required|exists:convenios,id|unique:convenio_conceptos_adicionales,convenio_id,' . $id,
            'cubre_casetas' => 'nullable|boolean',
            'forma_pago_casetas' => 'nullable|string|max:100',
            'costo_estadia_dia' => 'nullable|numeric|min:0',
            'dias_gracia_estadia' => 'nullable|integer|min:0',
            'costo_resguardo_nocturno' => 'nullable|numeric|min:0',
            'genera_cargo_cliente_final' => 'nullable|boolean',
        ], [
            'convenio_id.required' => 'El convenio es obligatorio.',
            'convenio_id.unique' => 'Este convenio ya tiene conceptos adicionales registrados.',
        ]);
    }
}

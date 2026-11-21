<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Convenio;
use App\Models\ConvenioConceptoAdicional;
use App\Support\Auditoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

// Controlador para gestión de conceptos adicionales por convenio (multi-tenant estricto + borrado físico)
class ConvenioConceptoAdicionalController extends Controller
{
    public function index()
    {
        $empresaId = Auth::user()->empresa_id;

        $conceptos = ConvenioConceptoAdicional::with('convenio.aseguradora')
            ->whereHas('convenio', fn ($q) => $q->where('empresa_id', $empresaId))
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
        $empresaId = auth()->user()->empresa_id;
        $concepto = ConvenioConceptoAdicional::whereHas('convenio', fn ($q) => $q->where('empresa_id', $empresaId))
            ->findOrFail($id);

        return Inertia::render('Panel/ConceptosAdicionales/Create', [
            'concepto' => $concepto,
            'convenios' => Convenio::where('empresa_id', $empresaId)->get(['id', 'nombre_convenio_poliza']),
        ]);
    }

    public function update(Request $request, $id)
    {
        $empresaId = auth()->user()->empresa_id;
        $concepto = ConvenioConceptoAdicional::whereHas('convenio', fn ($q) => $q->where('empresa_id', $empresaId))
            ->findOrFail($id);

        $data = $this->validateRequest($request, $id);
        $concepto->update($data);

        return redirect()->route('panel.conceptos-adicionales.index')->with('success', 'Conceptos adicionales actualizados.');
    }

    public function destroy($id)
    {
        $empresaId = auth()->user()->empresa_id;
        $concepto = ConvenioConceptoAdicional::whereHas('convenio', fn ($q) => $q->where('empresa_id', $empresaId))
            ->findOrFail($id);

        DB::transaction(function () use ($concepto) {
            Auditoria::registrar($concepto);
            $concepto->delete();
        });

        return redirect()->route('panel.conceptos-adicionales.index')->with('success', 'Conceptos adicionales eliminados físicamente.');
    }

    protected function validateRequest(Request $request, $id = null): array
    {
        $empresaId = auth()->user()->empresa_id;

        $data = $request->validate([
            'convenio_id' => [
                'required',
                Rule::exists('convenios', 'id')->where('empresa_id', $empresaId),
                Rule::unique('convenio_conceptos_adicionales', 'convenio_id')->ignore($id),
            ],
            'cubre_casetas' => 'nullable|boolean',
            'forma_pago_casetas' => 'nullable|string|max:100',
            'costo_estadia_dia' => 'nullable|numeric|min:0',
            'dias_gracia_estadia' => 'nullable|integer|min:0',
            'costo_resguardo_nocturno' => 'nullable|numeric|min:0',
            'genera_cargo_cliente_final' => 'nullable|boolean',
        ], [
            'convenio_id.required' => 'El convenio es obligatorio.',
            'convenio_id.exists' => 'El convenio seleccionado no pertenece a tu empresa.',
            'convenio_id.unique' => 'Este convenio ya tiene conceptos adicionales registrados.',
        ]);

        // Defaults seguros para columnas NOT NULL en BD
        $data['cubre_casetas'] = $request->boolean('cubre_casetas');
        $data['costo_estadia_dia'] = (float) ($data['costo_estadia_dia'] ?? 0);
        $data['dias_gracia_estadia'] = (int) ($data['dias_gracia_estadia'] ?? 0);
        $data['costo_resguardo_nocturno'] = (float) ($data['costo_resguardo_nocturno'] ?? 0);
        $data['genera_cargo_cliente_final'] = $request->boolean('genera_cargo_cliente_final');

        return $data;
    }
}

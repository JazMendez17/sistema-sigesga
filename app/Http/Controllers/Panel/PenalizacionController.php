<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Support\Auditoria;
use App\Models\ConvenioSla;
use App\Models\Convenio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

// Controlador para gestión de SLA / Penalizaciones por convenio
class PenalizacionController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $empresaId = $user->empresa_id;

        $slas = ConvenioSla::with('convenio.aseguradora')
            ->whereHas('convenio', fn($q) => $q->where('empresa_id', $empresaId))
            ->get()
            ->map(fn ($s) => [
                'id' => $s->id,
                'convenio' => $s->convenio?->nombre_convenio_poliza ?? '—',
                'aseguradora' => $s->convenio?->aseguradora?->nombre ?? '—',
                'tiempo_max_respuesta_urbano_min' => $s->tiempo_max_respuesta_urbano_min,
                'tiempo_max_respuesta_carretera_min' => $s->tiempo_max_respuesta_carretera_min,
                'disponibilidad' => $s->disponibilidad ?? '24/7',
                'protocolo_asignacion' => $s->protocolo_asignacion ?? '—',
                'penalizacion_incumplimiento' => $s->penalizacion_incumplimiento ?? '—',
            ]);

        return Inertia::render('Panel/Penalizaciones/Index', ['slas' => $slas]);
    }

    public function create()
    {
        return Inertia::render('Panel/Penalizaciones/Create', [
            'convenios' => Convenio::where('empresa_id', auth()->user()->empresa_id)->get(['id', 'nombre_convenio_poliza']),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateSla($request);
        ConvenioSla::create($data);
        return redirect()->route('panel.penalizaciones.index')->with('success', 'SLA registrado correctamente.');
    }

    public function edit($id)
    {
        return Inertia::render('Panel/Penalizaciones/Create', [
            'sla' => ConvenioSla::findOrFail($id),
            'convenios' => Convenio::where('empresa_id', auth()->user()->empresa_id)->get(['id', 'nombre_convenio_poliza']),
        ]);
    }

    public function update(Request $request, $id)
    {
        $data = $this->validateSla($request, $id);
        ConvenioSla::findOrFail($id)->update($data);
        return redirect()->route('panel.penalizaciones.index')->with('success', 'SLA actualizado correctamente.');
    }

    public function destroy($id)
    {
        $sla = ConvenioSla::findOrFail($id);

        Auditoria::registrar($sla);

        $sla->update(['eliminado' => true]);
        return redirect()->route('panel.penalizaciones.index')->with('success', 'SLA eliminado correctamente.');
    }

    protected function validateSla(Request $request, $id = null)
    {
        return $request->validate([
            'convenio_id' => 'required|exists:convenios,id|unique:convenio_sla,convenio_id,' . $id,
            'tiempo_max_respuesta_urbano_min' => 'required|integer|min:0',
            'tiempo_max_respuesta_carretera_min' => 'required|integer|min:0',
            'disponibilidad' => 'nullable|string|max:50',
            'penalizacion_incumplimiento' => 'nullable|string',
            'protocolo_asignacion' => 'nullable|string|max:150',
        ], [
            'convenio_id.required' => 'El convenio es obligatorio.',
            'convenio_id.unique' => 'Este convenio ya tiene un SLA registrado.',
            'tiempo_max_respuesta_urbano_min.required' => 'El tiempo urbano es obligatorio.',
            'tiempo_max_respuesta_carretera_min.required' => 'El tiempo carretera es obligatorio.',
        ]);
    }
}

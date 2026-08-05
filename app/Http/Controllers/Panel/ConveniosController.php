<?php

// Controlador de convenios

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Convenio;
use App\Models\Aseguradora;
use App\Models\CatalogoServicio;
use App\Http\Requests\Panel\StoreConvenioRequest;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ConveniosController extends Controller
{
    // Lista de convenios
    public function index()
    {
        $user = Auth::user();
        $empresaId = $user->empresa_id;

        $convenios = Convenio::with(['aseguradora', 'tipoServicio'])
            ->where('empresa_id', $empresaId)
            ->latest()
            ->get()
            ->map(fn ($c) => [
                'id' => $c->id,
                'nombre' => $c->nombre_convenio_poliza ?? '—',
                'aseguradora' => $c->aseguradora?->nombre ?? '—',
                'tipo_servicio' => $c->tipoServicio?->nombre ?? '—',
                'tipo_ruta' => $c->tipo_ruta ?? '—',
                'tipo_cobertura' => $c->tipo_cobertura ?? '—',
                'estatus' => $c->estatus ?? 'vigente',
            ]);

        return Inertia::render('Panel/Convenios/Index', [
            'convenios' => $convenios,
            'aseguradoras' => Aseguradora::where('empresa_id', $empresaId)->get(['id', 'nombre']),
        ]);
    }

    // Formulario para crear convenio
    public function create()
    {
        $user = Auth::user();
        $empresaId = $user->empresa_id;

        return Inertia::render('Panel/Convenios/Create', [
            'aseguradoras' => Aseguradora::where('empresa_id', $empresaId)->get(['id', 'nombre']),
            'tiposServicio' => CatalogoServicio::where('empresa_id', $empresaId)->get(['id', 'nombre']),
        ]);
    }

    // Guardar convenio en base de datos
    public function store(StoreConvenioRequest $request)
    {
        $user = Auth::user();

        $data = $request->validated();
        $data['empresa_id'] = $user->empresa_id;
        $data['cubre_casetas_peaje'] = $request->boolean('cubre_casetas_peaje');

        Convenio::create($data);

        return redirect()->route('panel.convenios.index')
            ->with('success', 'Convenio creado correctamente');
    }

    // Ver detalle de convenio
    public function show($id)
    {
        $convenio = Convenio::with([
            'aseguradora', 'tipoServicio',
            'convenioCoberturas', 'convenioUnidadesAutorizadas',
            'convenioManiobrasEspeciales', 'convenioDocumentosRequeridos',
        ])->where('empresa_id', auth()->user()->empresa_id)->findOrFail($id);

        return Inertia::render('Panel/Convenios/Show', [
            'convenio' => [
                'id' => $convenio->id,
                'nombre' => $convenio->nombre_convenio_poliza ?? '—',
                'aseguradora' => $convenio->aseguradora?->nombre ?? '—',
                'tipo_servicio' => $convenio->tipoServicio?->nombre ?? '—',
                'tipo_ruta' => $convenio->tipo_ruta ?? '—',
                'tipo_cobertura' => $convenio->tipo_cobertura ?? '—',
                'alcance_geografico' => $convenio->alcance_geografico ?? '—',
                'costo_banderazo' => (float) ($convenio->costo_banderazo ?? 0),
                'costo_km' => (float) ($convenio->costo_km ?? 0),
                'km_seguros_incluidos' => (float) ($convenio->km_seguros_incluidos ?? 0),
                'km_maximo_amparado' => (float) ($convenio->km_maximo_amparado ?? 0),
                'tope_presupuesto' => (float) ($convenio->tope_presupuesto ?? 0),
                'cubre_casetas_peaje' => $convenio->cubre_casetas_peaje ? 'Sí' : 'No',
                'dias_credito' => $convenio->dias_credito ?? 0,
                'estatus' => $convenio->estatus ?? 'activo',
                'coberturas' => $convenio->convenioCoberturas->map(fn ($cb) => ['id' => $cb->id, 'tipo_cobertura' => $cb->tipo_cobertura]),
                'unidades_autorizadas' => $convenio->convenioUnidadesAutorizadas->map(fn ($u) => ['id' => $u->id, 'tipo_grua' => $u->tipo_grua, 'peso_maximo_kg' => $u->peso_maximo_kg]),
                'maniobras' => $convenio->convenioManiobrasEspeciales->map(fn ($m) => ['id' => $m->id, 'concepto' => $m->concepto, 'costo' => (float) ($m->costo ?? 0)]),
                'documentos' => $convenio->convenioDocumentosRequeridos->map(fn ($d) => ['id' => $d->id, 'documento' => $d->documento, 'obligatorio' => $d->obligatorio]),
            ],
        ]);
    }

    // Formulario para editar convenio
    public function edit($id)
    {
        $user = Auth::user();
        $empresaId = $user->empresa_id;

        return Inertia::render('Panel/Convenios/Edit', [
            'convenio' => Convenio::where('empresa_id', auth()->user()->empresa_id)->findOrFail($id),
            'aseguradoras' => Aseguradora::where('empresa_id', $empresaId)->get(['id', 'nombre']),
            'tiposServicio' => CatalogoServicio::where('empresa_id', $empresaId)->get(['id', 'nombre']),
        ]);
    }

    // Actualizar datos del convenio
    public function update(StoreConvenioRequest $request, $id)
    {
        $convenio = Convenio::where('empresa_id', auth()->user()->empresa_id)->findOrFail($id);

        $data = $request->validated();
        $data['cubre_casetas_peaje'] = $request->boolean('cubre_casetas_peaje');

        $convenio->update($data);

        return redirect()->route('panel.convenios.index')
            ->with('success', 'Convenio actualizado correctamente');
    }

    // Eliminar convenio
    public function destroy($id)
    {
        $convenio = Convenio::where('empresa_id', auth()->user()->empresa_id)->findOrFail($id);

        if ($convenio->cotizaciones()->count() > 0) {
            return redirect()->back()->with('error', 'No se puede eliminar el convenio porque tiene cotizaciones asociadas.');
        }

        $convenio->delete();

        return redirect()->route('panel.convenios.index')
            ->with('success', 'Convenio eliminado correctamente');
    }
}

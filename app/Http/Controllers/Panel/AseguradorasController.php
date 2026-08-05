<?php

// Controlador de aseguradoras

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Aseguradora;
use App\Models\AseguradoraContacto;
use App\Models\Convenio;
use App\Http\Requests\Panel\StoreAseguradoraRequest;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class AseguradorasController extends Controller
{
    // Lista de aseguradoras
    public function index()
    {
        $user = Auth::user();
        $empresaId = $user->empresa_id;

        $aseguradoras = Aseguradora::where('empresa_id', $empresaId)
            ->latest()
            ->get()
            ->map(fn ($a) => [
                'id' => $a->id,
                'nombre' => $a->nombre ?? '—',
                'nombre_comercial' => $a->nombre_comercial ?? '—',
                'rfc' => $a->rfc ?? '—',
                'telefono' => $a->telefono ?? '—',
            ]);

        return Inertia::render('Panel/Aseguradoras/Index', [
            'aseguradoras' => $aseguradoras,
        ]);
    }

    // Formulario para crear aseguradora
    public function create()
    {
        return Inertia::render('Panel/Aseguradoras/Create');
    }

    // Guardar aseguradora en base de datos
    public function store(StoreAseguradoraRequest $request)
    {
        $user = Auth::user();

        $data = $request->validated();
        $data['empresa_id'] = $user->empresa_id;

        Aseguradora::create($data);

        return redirect()->route('panel.aseguradoras.index')
            ->with('success', 'Aseguradora creada correctamente');
    }

    // Ver detalle de aseguradora
    public function show($id)
    {
        $aseguradora = Aseguradora::with(['aseguradoraContactos', 'convenios'])->where('empresa_id', auth()->user()->empresa_id)->findOrFail($id);

        return Inertia::render('Panel/Aseguradoras/Show', [
            'aseguradora' => [
                'id' => $aseguradora->id,
                'nombre' => $aseguradora->nombre,
                'nombre_comercial' => $aseguradora->nombre_comercial ?? '—',
                'rfc' => $aseguradora->rfc ?? '—',
                'telefono' => $aseguradora->telefono ?? '—',

                'contactos' => $aseguradora->aseguradoraContactos->map(fn ($c) => [
                    'id' => $c->id,
                    'departamento' => $c->departamento ?? '—',
                    'nombre_contacto' => $c->nombre_contacto ?? '—',
                    'telefono' => $c->telefono ?? '—',
                    'email' => $c->email ?? '—',
                ]),
                'convenios' => $aseguradora->convenios->map(fn ($c) => [
                    'id' => $c->id,
                    'nombre' => $c->nombre_convenio_poliza ?? '—',
                    'tipo_servicio' => $c->tipoServicio?->nombre ?? '—',
                    'vigencia' => ($c->created_at?->format('d/m/Y') ?? '—') . ' al ' . ($c->updated_at?->format('d/m/Y') ?? '—'),
                    'estatus' => $c->estatus ?? 'activo',
                ]),
            ],
        ]);
    }

    // Formulario para editar aseguradora
    public function edit($id)
    {
        return Inertia::render('Panel/Aseguradoras/Create', [
            'aseguradora' => Aseguradora::where('empresa_id', auth()->user()->empresa_id)->findOrFail($id),
        ]);
    }

    // Actualizar datos de aseguradora
    public function update(StoreAseguradoraRequest $request, $id)
    {
        $aseguradora = Aseguradora::where('empresa_id', auth()->user()->empresa_id)->findOrFail($id);

        $data = $request->validated();

        $aseguradora->update($data);

        return redirect()->route('panel.aseguradoras.index')
            ->with('success', 'Aseguradora actualizada correctamente');
    }

    // Eliminar aseguradora
    public function destroy($id)
    {
        $aseguradora = Aseguradora::where('empresa_id', auth()->user()->empresa_id)->findOrFail($id);

        if ($aseguradora->convenios()->count() > 0) {
            return redirect()->back()->with('error', 'No se puede eliminar la aseguradora porque tiene convenios asociados.');
        }

        $aseguradora->delete();

        return redirect()->route('panel.aseguradoras.index')
            ->with('success', 'Aseguradora eliminada correctamente');
    }
}

<?php

// Controlador de aseguradoras

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Support\Auditoria;
use App\Models\Aseguradora;
use App\Models\AseguradoraContacto;
use App\Models\Convenio;
use App\Http\Requests\Panel\StoreAseguradoraRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
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

        $contactos = $data['contactos'] ?? [];
        unset($data['contactos']);

        $aseguradora = Aseguradora::create($data);

        foreach ($contactos as $contacto) {
            if (!empty($contacto['nombre_contacto'])) {
                $aseguradora->aseguradoraContactos()->create($contacto);
            }
        }

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
                    'activo' => $c->activo ?? true,
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
        $aseguradora = Aseguradora::with('aseguradoraContactos')->where('empresa_id', auth()->user()->empresa_id)->findOrFail($id);

        return Inertia::render('Panel/Aseguradoras/Create', [
            'aseguradora' => array_merge($aseguradora->toArray(), [
                'contactos' => $aseguradora->aseguradoraContactos->toArray(),
            ]),
        ]);
    }

    // Actualizar datos de aseguradora
    public function update(StoreAseguradoraRequest $request, $id)
    {
        $aseguradora = Aseguradora::where('empresa_id', auth()->user()->empresa_id)->findOrFail($id);

        $data = $request->validated();
        $contactos = $data['contactos'] ?? [];
        unset($data['contactos']);

        $aseguradora->update($data);

        // Reemplazar lista de contactos: elimina los anteriores y crea los nuevos
        if ($request->has('contactos')) {
            foreach ($aseguradora->aseguradoraContactos as $contactoExistente) {
                Auditoria::registrar($contactoExistente);
                $contactoExistente->update(['eliminado' => true]);
            }
            foreach ($contactos as $contacto) {
                if (!empty($contacto['nombre_contacto'])) {
                    $aseguradora->aseguradoraContactos()->create($contacto);
                }
            }
        }

        return redirect()->route('panel.aseguradoras.index')
            ->with('success', 'Aseguradora actualizada correctamente');
    }

    // Alternar estado activo/inactivo de un contacto
    public function toggleContacto(Request $request, $id, $contactoId)
    {
        $aseguradora = Aseguradora::where('empresa_id', auth()->user()->empresa_id)->findOrFail($id);
        $contacto = $aseguradora->aseguradoraContactos()->findOrFail($contactoId);

        $contacto->update(['activo' => $request->boolean('activo')]);

        return back()->with('success', 'Contacto actualizado correctamente');
    }

    // Eliminar contacto individual
    public function destroyContacto($id, $contactoId)
    {
        $aseguradora = Aseguradora::where('empresa_id', auth()->user()->empresa_id)->findOrFail($id);
        $contacto = $aseguradora->aseguradoraContactos()->findOrFail($contactoId);

        Auditoria::registrar($contacto);

        $contacto->update(['eliminado' => true]);

        return back()->with('success', 'Contacto eliminado correctamente');
    }

    // Eliminar aseguradora
    public function destroy($id)
    {
        $aseguradora = Aseguradora::where('empresa_id', auth()->user()->empresa_id)->findOrFail($id);

        if ($aseguradora->convenios()->count() > 0) {
            return redirect()->back()->with('error', 'No se puede eliminar la aseguradora porque tiene convenios asociados.');
        }

        Auditoria::registrar($aseguradora);

        $aseguradora->update(['eliminado' => true]);

        return redirect()->route('panel.aseguradoras.index')
            ->with('success', 'Aseguradora eliminada correctamente');
    }
}

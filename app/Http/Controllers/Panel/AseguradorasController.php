<?php

// Controlador de aseguradoras (transacciones, vigencia real, defaults NOT NULL y borrado físico)

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Http\Requests\Panel\StoreAseguradoraRequest;
use App\Models\Aseguradora;
use App\Support\Auditoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class AseguradorasController extends Controller
{
    public function index()
    {
        $empresaId = Auth::user()->empresa_id;

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

    public function create()
    {
        return Inertia::render('Panel/Aseguradoras/Create');
    }

    public function store(StoreAseguradoraRequest $request)
    {
        $data = $request->validated();
        $data['empresa_id'] = Auth::user()->empresa_id;

        $contactos = $data['contactos'] ?? [];
        unset($data['contactos']);

        DB::transaction(function () use ($data, $contactos) {
            $aseguradora = Aseguradora::create($data);

            foreach ($contactos as $contacto) {
                if (!empty($contacto['nombre_contacto'])) {
                    $contacto['departamento'] = $contacto['departamento'] ?? 'General';
                    $aseguradora->aseguradoraContactos()->create($contacto);
                }
            }
        });

        return redirect()->route('panel.aseguradoras.index')
            ->with('success', 'Aseguradora creada correctamente');
    }

    public function show($id)
    {
        $aseguradora = Aseguradora::with(['aseguradoraContactos', 'convenios.tipoServicio'])
            ->where('empresa_id', auth()->user()->empresa_id)
            ->findOrFail($id);

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
                    'activo' => (bool) ($c->activo ?? true),
                ]),
                'convenios' => $aseguradora->convenios->map(fn ($c) => [
                    'id' => $c->id,
                    'nombre' => $c->nombre_convenio_poliza ?? '—',
                    'tipo_servicio' => $c->tipoServicio?->nombre ?? '—',
                    'vigencia' => ($c->fecha_inicio ? \Carbon\Carbon::parse($c->fecha_inicio)->format('d/m/Y') : '—')
                        . ' al '
                        . ($c->fecha_fin ? \Carbon\Carbon::parse($c->fecha_fin)->format('d/m/Y') : '—'),
                    'estatus' => $c->estatus ?? 'vigente',
                ]),
            ],
        ]);
    }

    public function edit($id)
    {
        $aseguradora = Aseguradora::with('aseguradoraContactos')
            ->where('empresa_id', auth()->user()->empresa_id)
            ->findOrFail($id);

        return Inertia::render('Panel/Aseguradoras/Create', [
            'aseguradora' => array_merge($aseguradora->toArray(), [
                'contactos' => $aseguradora->aseguradoraContactos->toArray(),
            ]),
        ]);
    }

    public function update(StoreAseguradoraRequest $request, $id)
    {
        $aseguradora = Aseguradora::with('aseguradoraContactos')
            ->where('empresa_id', auth()->user()->empresa_id)
            ->findOrFail($id);

        $data = $request->validated();
        $contactos = $data['contactos'] ?? [];
        unset($data['contactos']);

        DB::transaction(function () use ($request, $aseguradora, $data, $contactos) {
            $aseguradora->update($data);

            if ($request->has('contactos')) {
                $aseguradora->aseguradoraContactos()->delete();
                foreach ($contactos as $contacto) {
                    if (!empty($contacto['nombre_contacto'])) {
                        $contacto['departamento'] = $contacto['departamento'] ?? 'General';
                        $aseguradora->aseguradoraContactos()->create($contacto);
                    }
                }
            }
        });

        return redirect()->route('panel.aseguradoras.index')
            ->with('success', 'Aseguradora actualizada correctamente');
    }

    public function toggleContacto(Request $request, $id, $contactoId)
    {
        $aseguradora = Aseguradora::where('empresa_id', auth()->user()->empresa_id)->findOrFail($id);
        $contacto = $aseguradora->aseguradoraContactos()->findOrFail($contactoId);

        $contacto->update(['activo' => $request->boolean('activo')]);

        return back()->with('success', 'Contacto actualizado correctamente');
    }

    public function destroyContacto($id, $contactoId)
    {
        $aseguradora = Aseguradora::where('empresa_id', auth()->user()->empresa_id)->findOrFail($id);
        $contacto = $aseguradora->aseguradoraContactos()->findOrFail($contactoId);

        DB::transaction(function () use ($contacto) {
            Auditoria::registrar($contacto);
            $contacto->delete();
        });

        return back()->with('success', 'Contacto eliminado físicamente.');
    }

    public function destroy($id)
    {
        $aseguradora = Aseguradora::where('empresa_id', auth()->user()->empresa_id)->findOrFail($id);

        DB::transaction(function () use ($aseguradora) {
            foreach ($aseguradora->convenios()->get() as $convenio) {
                Auditoria::registrar($convenio);
            }
            Auditoria::registrar($aseguradora);
            $aseguradora->delete();
        });

        return redirect()->route('panel.aseguradoras.index')
            ->with('success', 'Aseguradora eliminada físicamente de forma correcta.');
    }
}

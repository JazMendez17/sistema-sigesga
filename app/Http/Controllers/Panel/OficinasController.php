<?php

// Controlador de oficinas

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Support\Auditoria;
use App\Models\Oficina;
use App\Models\Direccion;
use App\Http\Requests\Panel\StoreOficinaRequest;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class OficinasController extends Controller
{
    // Lista de oficinas
    public function index()
    {
        $user = Auth::user();
        $empresaId = $user->empresa_id;

        $oficinas = Oficina::with('direccion')
            ->where('empresa_id', $empresaId)
            ->latest()
            ->get()
            ->map(fn ($o) => [
                'id' => $o->id,
                'nombre' => $o->nombre ?? '—',
                'direccion' => $o->direccion ? trim($o->direccion->calle . ' ' . ($o->direccion->colonia ?? '') . ', ' . ($o->direccion->ciudad ?? '')) : '—',
                'telefono' => $o->telefono ?? '—',
                'email' => $o->email ?? '—',
                'encargado' => $o->encargado ?? '—',
            ]);

        return Inertia::render('Panel/Oficinas/Index', [
            'oficinas' => $oficinas,
        ]);
    }

    // Formulario para crear oficina
    public function create()
    {
        return Inertia::render('Panel/Oficinas/Create');
    }

    // Guardar oficina en base de datos
    public function store(StoreOficinaRequest $request)
    {
        $user = Auth::user();

        $data = $request->validated();
        $data['empresa_id'] = $user->empresa_id;

        if (!empty($data['calle'])) {
            $dir = Direccion::create([
                'calle' => $data['calle'],
                'numero_exterior' => $data['numero_exterior'] ?? '',
                'numero_interior' => $data['numero_interior'] ?? '',
                'colonia' => $data['colonia'] ?? '',
                'codigo_postal' => $data['codigo_postal'] ?? '',
                'municipio_alcaldia' => $data['municipio_alcaldia'] ?? '',
                'ciudad' => $data['ciudad'] ?? '',
                'estado' => $data['estado'] ?? '',
                'pais' => $data['pais'] ?? 'México',
                'referencias' => $data['referencias'] ?? '',
            ]);
            $data['direccion_id'] = $dir->id;
        }

        Oficina::create($data);

        return redirect()->route('panel.oficinas.index')
            ->with('success', 'Oficina creada correctamente');
    }

    // Ver detalle de oficina
    public function show($id)
    {
        $oficina = Oficina::with('direccion')->where('empresa_id', auth()->user()->empresa_id)->findOrFail($id);

        return Inertia::render('Panel/Oficinas/Show', [
            'oficina' => $oficina,
        ]);
    }

    // Formulario para editar oficina
    public function edit($id)
    {
        return Inertia::render('Panel/Oficinas/Create', [
            'oficina' => Oficina::with('direccion')->where('empresa_id', auth()->user()->empresa_id)->findOrFail($id),
        ]);
    }

    // Actualizar datos de la oficina
    public function update(StoreOficinaRequest $request, $id)
    {
        $oficina = Oficina::where('empresa_id', auth()->user()->empresa_id)->findOrFail($id);

        $data = $request->validated();

        if (!empty($data['calle'])) {
            if ($oficina->direccion_id) {
                $oficina->direccion->update([
                    'calle' => $data['calle'],
                    'numero_exterior' => $data['numero_exterior'] ?? '',
                    'numero_interior' => $data['numero_interior'] ?? '',
                    'colonia' => $data['colonia'] ?? '',
                    'codigo_postal' => $data['codigo_postal'] ?? '',
                    'municipio_alcaldia' => $data['municipio_alcaldia'] ?? '',
                    'ciudad' => $data['ciudad'] ?? '',
                    'estado' => $data['estado'] ?? '',
                    'pais' => $data['pais'] ?? 'México',
                    'referencias' => $data['referencias'] ?? '',
                ]);
            } else {
                $dir = Direccion::create([
                    'calle' => $data['calle'],
                    'numero_exterior' => $data['numero_exterior'] ?? '',
                    'numero_interior' => $data['numero_interior'] ?? '',
                    'colonia' => $data['colonia'] ?? '',
                    'codigo_postal' => $data['codigo_postal'] ?? '',
                    'municipio_alcaldia' => $data['municipio_alcaldia'] ?? '',
                    'ciudad' => $data['ciudad'] ?? '',
                    'estado' => $data['estado'] ?? '',
                    'pais' => $data['pais'] ?? 'México',
                    'referencias' => $data['referencias'] ?? '',
                ]);
                $data['direccion_id'] = $dir->id;
            }
        }

        $oficina->update($data);

        return redirect()->route('panel.oficinas.index')
            ->with('success', 'Oficina actualizada correctamente');
    }

    // Eliminar oficina
    public function destroy($id)
    {
        $oficina = Oficina::where('empresa_id', auth()->user()->empresa_id)->findOrFail($id);

        Auditoria::registrar($oficina);

        $oficina->update(['eliminado' => true]);

        return redirect()->route('panel.oficinas.index')
            ->with('success', 'Oficina eliminada correctamente');
    }
}

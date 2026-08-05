<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\CatalogoServicio;
use App\Http\Requests\Panel\StoreTipoServicioRequest;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class TiposServicioController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $empresaId = $user->empresa_id;

        $tipos = CatalogoServicio::where('empresa_id', $empresaId)
            ->latest()
            ->get()
            ->map(fn ($t) => [
                'id' => $t->id,
                'nombre' => $t->nombre ?? '—',
                'requiere_maniobra' => $t->requiere_maniobra ?? false,
                'activo' => $t->activo ?? true,
            ]);

        return Inertia::render('Panel/TiposServicio/Index', [
            'tiposServicio' => $tipos,
        ]);
    }

    public function create()
    {
        return Inertia::render('Panel/TiposServicio/Create');
    }

    public function store(StoreTipoServicioRequest $request)
    {
        $user = Auth::user();

        $data = $request->validated();
        $data['empresa_id'] = $user->empresa_id;
        $data['requiere_maniobra'] = $request->boolean('requiere_maniobra');
        $data['activo'] = $request->boolean('activo');

        CatalogoServicio::create($data);

        return back()->with('success', 'Tipo de servicio creado correctamente');
    }

    public function edit($id)
    {
        return Inertia::render('Panel/TiposServicio/Create', [
            'tipoServicio' => CatalogoServicio::findOrFail($id),
        ]);
    }

    public function update(StoreTipoServicioRequest $request, $id)
    {
        $tipo = CatalogoServicio::findOrFail($id);

        $data = $request->validated();
        $data['requiere_maniobra'] = $request->boolean('requiere_maniobra');
        $data['activo'] = $request->boolean('activo', !array_key_exists('activo', $request->all()) ? null : false);

        if (array_key_exists('nombre', $data)) {
            $tipo->update($data);
        } else {
            unset($data['nombre']);
            $tipo->update($data);
        }

        return back()->with('success', 'Tipo de servicio actualizado correctamente');
    }

    public function destroy($id)
    {
        CatalogoServicio::findOrFail($id)->delete();

        return back()->with('success', 'Tipo de servicio eliminado correctamente');
    }
}

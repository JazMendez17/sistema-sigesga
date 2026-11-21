<?php

// Controlador de tipos de servicio (multi-tenant estricto + borrado físico)

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Http\Requests\Panel\StoreTipoServicioRequest;
use App\Models\CatalogoServicio;
use App\Models\Cotizacione;
use App\Support\Auditoria;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class TiposServicioController extends Controller
{
    public function index()
    {
        $empresaId = Auth::user()->empresa_id;

        $tipos = CatalogoServicio::where('empresa_id', $empresaId)
            ->latest()
            ->get()
            ->map(fn ($t) => [
                'id' => $t->id,
                'nombre' => $t->nombre ?? '—',
                'requiere_maniobra' => (bool) ($t->requiere_maniobra ?? false),
                'activo' => (bool) ($t->activo ?? true),
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
        $data = $request->validated();
        $data['empresa_id'] = Auth::user()->empresa_id;
        $data['requiere_maniobra'] = $request->boolean('requiere_maniobra');
        $data['activo'] = $request->has('activo') ? $request->boolean('activo') : true;

        CatalogoServicio::create($data);

        return redirect()->route('panel.tipos-servicio.index')
            ->with('success', 'Tipo de servicio creado correctamente');
    }

    public function edit($id)
    {
        $tipo = CatalogoServicio::where('empresa_id', Auth::user()->empresa_id)->findOrFail($id);

        return Inertia::render('Panel/TiposServicio/Create', [
            'tipoServicio' => $tipo,
        ]);
    }

    public function update(StoreTipoServicioRequest $request, $id)
    {
        $tipo = CatalogoServicio::where('empresa_id', Auth::user()->empresa_id)->findOrFail($id);

        $data = $request->validated();

        if ($request->has('requiere_maniobra')) {
            $data['requiere_maniobra'] = $request->boolean('requiere_maniobra');
        }
        if ($request->has('activo')) {
            $data['activo'] = $request->boolean('activo');
        }

        $tipo->update($data);

        if ($request->has('nombre')) {
            return redirect()->route('panel.tipos-servicio.index')
                ->with('success', 'Tipo de servicio actualizado correctamente');
        }

        return back()->with('success', 'Tipo de servicio actualizado correctamente');
    }

    public function destroy($id)
    {
        $tipo = CatalogoServicio::where('empresa_id', Auth::user()->empresa_id)->findOrFail($id);

        if (Cotizacione::where('tipo_servicio_id', $tipo->id)->exists()) {
            return back()->with('error', 'No se puede eliminar este tipo de servicio porque tiene cotizaciones asociadas. Desactívalo en su lugar.');
        }

        DB::transaction(function () use ($tipo) {
            Auditoria::registrar($tipo);
            $tipo->delete();
        });

        return redirect()->route('panel.tipos-servicio.index')
            ->with('success', 'Tipo de servicio eliminado físicamente de forma correcta.');
    }
}

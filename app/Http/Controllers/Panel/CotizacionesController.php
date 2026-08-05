<?php

// Controlador de cotizaciones

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Cotizacione;
use App\Models\Cliente;
use App\Models\CatalogoServicio;
use App\Http\Requests\Panel\StoreCotizacionRequest;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class CotizacionesController extends Controller
{
    // Lista de cotizaciones
    public function index()
    {
        $user = Auth::user();
        $empresaId = $user->empresa_id;

        $cotizaciones = Cotizacione::with(['cliente', 'tipoServicio', 'usuarioCreador'])
            ->where('empresa_id', $empresaId)
            ->latest()
            ->get()
            ->map(fn ($c) => [
                'id' => $c->id,
                'folio' => $c->folio ?? 'COT-' . str_pad($c->id, 5, '0', STR_PAD_LEFT),
                'cliente' => $c->cliente?->nombre ?? '—',
                'tipo' => $c->tipoServicio?->nombre ?? '—',
                'origen' => $c->origen_direccion ?? '—',
                'destino' => $c->destino_direccion ?? '—',
                'total' => (float) ($c->costo_total ?? 0),
                'estatus' => $c->estatus ?? 'pendiente',
                'fecha' => $c->created_at?->format('d/m/Y'),
            ]);

        return Inertia::render('Panel/Cotizaciones/Index', [
            'cotizaciones' => $cotizaciones,
        ]);
    }

    // Formulario para crear cotización
    public function create()
    {
        $user = Auth::user();
        $empresaId = $user->empresa_id;

        return Inertia::render('Panel/Cotizaciones/Create', [
            'clientes' => Cliente::where('empresa_id', $empresaId)->get(['id', 'nombre']),
            'tiposServicio' => CatalogoServicio::where('empresa_id', $empresaId)->get(['id', 'nombre']),
        ]);
    }

    // Guardar cotización en base de datos
    public function store(StoreCotizacionRequest $request)
    {
        $user = Auth::user();

        $data = $request->validated();
        $data['empresa_id'] = $user->empresa_id;
        $data['usuario_creador_id'] = $user->id;
        $data['estatus'] = 'pendiente';

        Cotizacione::create($data);

        return redirect()->route('panel.cotizaciones.index')
            ->with('success', 'Cotización creada correctamente');
    }

    // Ver detalle de cotización
    public function show($id)
    {
        $cotizacion = Cotizacione::with(['cliente', 'tipoServicio', 'usuarioCreador', 'servicio'])->where('empresa_id', auth()->user()->empresa_id)->findOrFail($id);

        return Inertia::render('Panel/Cotizaciones/Show', [
            'cotizacion' => [
                'id' => $cotizacion->id,
                'folio' => $cotizacion->folio ?? 'COT-' . str_pad($cotizacion->id, 5, '0', STR_PAD_LEFT),
                'cliente' => $cotizacion->cliente?->nombre ?? '—',
                'fecha' => $cotizacion->created_at?->format('d/m/Y'),
                'tipo' => $cotizacion->tipoServicio?->nombre ?? '—',
                'estatus' => $cotizacion->estatus ?? 'pendiente',
                'origen' => $cotizacion->origen_direccion ?? '—',
                'destino' => $cotizacion->destino_direccion ?? '—',
                'distancia' => (float) ($cotizacion->distancia_km ?? 0),
                'total_estimado' => (float) ($cotizacion->costo_total ?? 0),
                'usuario_creador' => $cotizacion->usuarioCreador?->name ?? '—',
                'servicio_id' => $cotizacion->servicio?->id,
            ],
        ]);
    }

    // Formulario para editar cotización
    public function edit($id)
    {
        $user = Auth::user();
        $empresaId = $user->empresa_id;
        $cotizacion = Cotizacione::where('empresa_id', auth()->user()->empresa_id)->findOrFail($id);

        return Inertia::render('Panel/Cotizaciones/Create', [
            'cotizacion' => $cotizacion,
            'clientes' => Cliente::where('empresa_id', $empresaId)->get(['id', 'nombre']),
            'tiposServicio' => CatalogoServicio::where('empresa_id', $empresaId)->get(['id', 'nombre']),
        ]);
    }

    // Actualizar datos de cotización
    public function update(StoreCotizacionRequest $request, $id)
    {
        $cotizacion = Cotizacione::where('empresa_id', auth()->user()->empresa_id)->findOrFail($id);

        $data = $request->validated();

        $cotizacion->update($data);

        return redirect()->route('panel.cotizaciones.index')
            ->with('success', 'Cotización actualizada correctamente');
    }

    // Eliminar cotización
    public function destroy($id)
    {
        Cotizacione::where('empresa_id', auth()->user()->empresa_id)->findOrFail($id)->delete();

        return redirect()->route('panel.cotizaciones.index')
            ->with('success', 'Cotización eliminada correctamente');
    }
}

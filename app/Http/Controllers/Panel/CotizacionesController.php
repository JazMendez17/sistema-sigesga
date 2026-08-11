<?php

// Controlador de cotizaciones

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Cotizacione;
use App\Models\Cliente;
use App\Models\CatalogoServicio;
use App\Models\Convenio;
use App\Models\ConvenioTarifa;
use App\Models\ConvenioConceptoAdicional;
use App\Models\TarifasEmpresa;
use App\Models\Servicio;
use App\Models\Operadore;
use App\Models\Unidade;
use App\Models\Oficina;
use App\Http\Requests\Panel\StoreCotizacionRequest;
use Illuminate\Http\Request;
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
            'clientes' => Cliente::where('empresa_id', $empresaId)->get(['id', 'nombre', 'apellido_paterno', 'apellido_materno']),
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

        if (empty($data['folio'])) {
            $data['folio'] = 'COT-' . str_pad((Cotizacione::max('id') ?? 0) + 1, 5, '0', STR_PAD_LEFT);
        }

        Cotizacione::create($data);

        return redirect()->route('panel.cotizaciones.index')
            ->with('success', 'Cotización creada correctamente');
    }

    // Ver detalle de cotización
    public function show($id)
    {
        $user = Auth::user();
        $cotizacion = Cotizacione::with(['cliente', 'tipoServicio', 'usuarioCreador', 'servicio'])->where('empresa_id', $user->empresa_id)->findOrFail($id);

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
            'operadores' => Operadore::with('empleado')->where('empresa_id', $user->empresa_id)->where('disponible', true)->get()->map(fn($o) => [
                'id' => $o->id, 'nombre' => $o->empleado?->nombre ? trim($o->empleado->nombre.' '.($o->empleado->apellido_paterno??'')) : ('Operador #'.$o->id),
            ]),
            'unidades' => Unidade::where('empresa_id', $user->empresa_id)->where('activo', true)->get(['id', 'placas', 'numero_economico']),
        ]);
    }

    // Aprobar cotización y crear servicio
    public function aprobar(Request $request, $id)
    {
        $cotizacion = Cotizacione::where('empresa_id', auth()->user()->empresa_id)->findOrFail($id);

        $request->validate([
            'operador_id' => 'required|exists:operadores,id',
            'unidad_id' => 'required|exists:unidades,id',
        ]);

        $cotizacion->update(['estatus' => 'aprobado']);

        Servicio::create([
            'empresa_id' => $cotizacion->empresa_id,
            'cotizacion_id' => $cotizacion->id,
            'operador_id' => $request->operador_id,
            'unidad_id' => $request->unidad_id,
            'estado' => 'asignado',
            'costo_final_real' => $cotizacion->costo_total,
        ]);

        Operadore::where('id', $request->operador_id)->update(['disponible' => false]);

        return back()->with('success', 'Cotización aprobada y servicio creado correctamente.');
    }

    // Rechazar cotización
    public function rechazar(Request $request, $id)
    {
        $cotizacion = Cotizacione::where('empresa_id', auth()->user()->empresa_id)->findOrFail($id);
        $cotizacion->update(['estatus' => 'rechazado']);

        return back()->with('success', 'Cotización rechazada.');
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

    // API: Obtener tarifa según cliente y tipo de servicio para auto-llenado
    public function obtenerTarifa(Request $request)
    {
        $cliente = Cliente::with('aseguradora')->find($request->cliente_id);
        $tipoServicioId = $request->tipo_servicio_id;

        if (!$cliente || !$tipoServicioId) {
            return response()->json(null);
        }

        // Si el cliente tiene aseguradora, buscar en convenio_tarifas
        if ($cliente->aseguradora_id) {
            $convenio = Convenio::where('aseguradora_id', $cliente->aseguradora_id)
                ->where('empresa_id', auth()->user()->empresa_id)
                ->first();

            if ($convenio) {
                $tarifa = ConvenioTarifa::where('convenio_id', $convenio->id)
                    ->where('servicio_id', $tipoServicioId)
                    ->first();

                $conceptos = ConvenioConceptoAdicional::where('convenio_id', $convenio->id)->first();

                return response()->json([
                    'origen' => 'convenio',
                    'convenio_id' => $convenio->id,
                    'convenio_nombre' => $convenio->nombre_convenio_poliza,
                    'banderazo' => (float) ($tarifa->banderazo ?? 0),
                    'km_incluidos' => (int) ($tarifa->km_incluidos ?? 0),
                    'costo_km_extra' => (float) ($tarifa->costo_km_extra ?? 0),
                    'cubre_casetas' => $conceptos?->cubre_casetas ?? false,
                    'tarifa_nocturna_recargo_pct' => (float) ($tarifa->tarifa_nocturna_recargo_pct ?? 0),
                    'tarifa_domingo_festivo_recargo_pct' => (float) ($tarifa->tarifa_domingo_festivo_recargo_pct ?? 0),
                    'minutos_espera_incluidos' => (int) ($tarifa->minutos_espera_incluidos ?? 0),
                    'costo_espera_adicional_hora' => (float) ($tarifa->costo_espera_adicional_hora ?? 0),
                    'descuento_pct' => (float) ($tarifa->descuento_pct ?? 0),
                ]);
            }
        }

        // Si no tiene aseguradora o no se encontró convenio, buscar tarifa propia
        $tarifaPropia = TarifasEmpresa::where('tipo_servicio_id', $tipoServicioId)
            ->where('activo', true)
            ->first();

        if ($tarifaPropia) {
            return response()->json([
                'origen' => 'propia',
                'banderazo' => (float) $tarifaPropia->costo_banderazo,
                'km_incluidos' => (float) $tarifaPropia->km_incluidos,
                'costo_km_extra' => (float) ($tarifaPropia->costo_km ?? 0),
                'cubre_casetas' => (bool) $tarifaPropia->cubre_casetas_peaje,
                'descuento_pct' => 0,
            ]);
        }

        return response()->json(null);
    }
}

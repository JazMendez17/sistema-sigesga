<?php

// Controlador de servicios

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Servicio;
use App\Models\Cotizacione;
use App\Models\Operadore;
use App\Models\Unidade;
use App\Models\Oficina;
use App\Http\Requests\Panel\StoreServicioRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ServiciosController extends Controller
{
    // Lista de servicios
    public function index()
    {
        $user = Auth::user();
        $empresaId = $user->empresa_id;

        $servicios = Servicio::with(['cotizacion.cliente', 'operador.empleado', 'unidad'])
            ->where('empresa_id', $empresaId)
            ->latest()
            ->get()
            ->map(fn ($s) => [
                'id' => $s->id,
                'folio' => 'SVC-' . str_pad($s->id, 5, '0', STR_PAD_LEFT),
                'cliente' => $s->cotizacion?->cliente?->nombre ?? '—',
                'tipo' => $s->cotizacion?->tipoServicio?->nombre ?? '—',
                'operador' => $s->operador?->empleado?->nombre ?? '—',
                'origen' => $s->cotizacion?->origen_direccion ?? '—',
                'destino' => $s->cotizacion?->destino_direccion ?? '—',
                'fecha' => $s->created_at?->format('d/m/Y'),
                'estatus' => $s->estado ?? 'pendiente',
            ]);

        return Inertia::render('Panel/Servicios/Index', [
            'servicios' => $servicios,
        ]);
    }

    // Formulario para crear servicio
    public function create()
    {
        $user = Auth::user();
        $empresaId = $user->empresa_id;

        return Inertia::render('Panel/Servicios/Create', [
            'cotizaciones' => Cotizacione::where('empresa_id', $empresaId)->where('estatus', 'aprobada')->get(['id', 'folio']),
            'operadores' => Operadore::with('empleado')->where('empresa_id', $empresaId)->get(),
            'unidades' => Unidade::where('empresa_id', $empresaId)->get(['id', 'placas', 'numero_economico']),
            'oficinas' => Oficina::where('empresa_id', $empresaId)->get(['id', 'nombre']),
        ]);
    }

    // Guardar servicio en base de datos
    public function store(StoreServicioRequest $request)
    {
        $user = Auth::user();

        $data = $request->validated();

        if ($request->unidad_id) {
            $activeService = Servicio::where('unidad_id', $request->unidad_id)
                ->whereIn('estado', ['asignado', 'inicio_servicio', 'en_sitio_origen', 'salida_destino', 'en_destino'])
                ->exists();
            if ($activeService) {
                return redirect()->back()->with('error', 'La unidad seleccionada ya está asignada a un servicio activo.');
            }
        }

        if ($request->operador_id) {
            $activeOperador = Servicio::where('operador_id', $request->operador_id)
                ->whereIn('estado', ['asignado', 'inicio_servicio', 'en_sitio_origen', 'salida_destino', 'en_destino'])
                ->exists();
            if ($activeOperador) {
                return redirect()->back()->with('error', 'El operador seleccionado ya está asignado a un servicio activo.');
            }
        }

        $data['empresa_id'] = $user->empresa_id;
        $data['estado'] = 'asignado';

        Servicio::create($data);

        return redirect()->route('panel.servicios.index')
            ->with('success', 'Servicio creado correctamente');
    }

    // Ver detalle de servicio con bitácora
    public function show($id)
    {
        $servicio = Servicio::with([
            'cotizacion.cliente', 'cotizacion.tipoServicio',
            'operador.empleado', 'unidad', 'bitacoraTiemposServicio',
        ])->where('empresa_id', auth()->user()->empresa_id)->findOrFail($id);

        return Inertia::render('Panel/Servicios/Show', [
            'servicio' => [
                'id' => $servicio->id,
                'folio' => 'SVC-' . str_pad($servicio->id, 5, '0', STR_PAD_LEFT),
                'cliente' => $servicio->cotizacion?->cliente?->nombre ?? '—',
                'fecha' => $servicio->created_at?->format('d/m/Y'),
                'tipo' => $servicio->cotizacion?->tipoServicio?->nombre ?? '—',
                'operador' => $servicio->operador?->empleado?->nombre ?? '—',
                'unidad' => $servicio->unidad?->placas ?? '—',
                'origen' => $servicio->cotizacion?->origen_direccion ?? '—',
                'destino' => $servicio->cotizacion?->destino_direccion ?? '—',
                'estatus' => $servicio->estado ?? 'asignado',
                'observaciones' => $servicio->observaciones ?? '—',
                'kms_salida' => $servicio->kms_salida,
                'kms_llegada_cliente' => $servicio->kms_llegada_cliente,
                'kms_termino_servicio' => $servicio->kms_termino_servicio,
                'kms_regreso_base' => $servicio->kms_regreso_base,
                'kms_cobrados_reales' => $servicio->kms_cobrados_reales,
                'bitacora' => $servicio->bitacoraTiemposServicio ? [
                    'salida' => $servicio->bitacoraTiemposServicio->hora_asignado,
                    'llegada' => $servicio->bitacoraTiemposServicio->hora_inicio_servicio,
                    'termino' => $servicio->bitacoraTiemposServicio->hora_finalizado,
                    'regreso' => $servicio->bitacoraTiemposServicio->hora_regreso_base ?? null,
                ] : null,
            ],
        ]);
    }

    // Formulario para editar servicio
    public function edit($id)
    {
        $user = Auth::user();
        $empresaId = $user->empresa_id;

        $servicio = Servicio::where('empresa_id', auth()->user()->empresa_id)->findOrFail($id);

        return Inertia::render('Panel/Servicios/Create', [
            'servicio' => $servicio,
            'cotizaciones' => Cotizacione::where('empresa_id', $empresaId)->get(['id', 'folio']),
            'operadores' => Operadore::with('empleado')->where('empresa_id', $empresaId)->get(),
            'unidades' => Unidade::where('empresa_id', $empresaId)->get(['id', 'placas', 'numero_economico']),
            'oficinas' => Oficina::where('empresa_id', $empresaId)->get(['id', 'nombre']),
        ]);
    }

    // Actualizar datos del servicio
    public function update(StoreServicioRequest $request, $id)
    {
        $servicio = Servicio::where('empresa_id', auth()->user()->empresa_id)->findOrFail($id);

        $data = $request->validated();

        if ($request->unidad_id) {
            $activeService = Servicio::where('unidad_id', $request->unidad_id)
                ->whereIn('estado', ['asignado', 'inicio_servicio', 'en_sitio_origen', 'salida_destino', 'en_destino'])
                ->where('id', '!=', $id)
                ->exists();
            if ($activeService) {
                return redirect()->back()->with('error', 'La unidad seleccionada ya está asignada a un servicio activo.');
            }
        }

        if ($request->operador_id) {
            $activeOperador = Servicio::where('operador_id', $request->operador_id)
                ->whereIn('estado', ['asignado', 'inicio_servicio', 'en_sitio_origen', 'salida_destino', 'en_destino'])
                ->where('id', '!=', $id)
                ->exists();
            if ($activeOperador) {
                return redirect()->back()->with('error', 'El operador seleccionado ya está asignado a un servicio activo.');
            }
        }

        $servicio->update($data);

        return redirect()->route('panel.servicios.index')
            ->with('success', 'Servicio actualizado correctamente');
    }

    // Avanzar estado del servicio (panel del operador)
    public function avanzarEstado(Request $request, $id)
    {
        $servicio = Servicio::where('empresa_id', auth()->user()->empresa_id)->findOrFail($id);
        $nuevoEstado = $request->input('estado');

        $flujo = ['asignado', 'inicio_servicio', 'en_sitio_origen', 'salida_destino', 'en_destino', 'finalizado'];
        if (!in_array($nuevoEstado, $flujo)) {
            return back()->with('error', 'Estado no válido.');
        }

        $data = ['estado' => $nuevoEstado];

        // Registrar bitácora de tiempos
        $bitacora = $servicio->bitacoraTiemposServicio;
        if (!$bitacora) {
            $bitacora = \App\Models\BitacoraTiemposServicio::create(['servicio_id' => $servicio->id]);
        }

        $camposTiempo = [
            'asignado' => 'hora_asignado',
            'inicio_servicio' => 'hora_inicio_servicio',
            'en_sitio_origen' => 'hora_en_sitio_origen',
            'salida_destino' => 'hora_salida_destino',
            'en_destino' => 'hora_en_destino',
            'finalizado' => 'hora_finalizado',
        ];

        if (isset($camposTiempo[$nuevoEstado])) {
            $bitacora->update([$camposTiempo[$nuevoEstado] => now()]);
        }

        if ($nuevoEstado === 'finalizado') {
            $request->validate([
                'kms_termino_servicio' => 'nullable|integer|min:0',
                'observaciones' => 'nullable|string|max:500',
            ]);
            $data['kms_termino_servicio'] = $request->kms_termino_servicio;
            $data['observaciones'] = $request->observaciones;

            // Liberar operador
            if ($servicio->operador_id) {
                \App\Models\Operadore::where('id', $servicio->operador_id)->update(['disponible' => true]);
            }
        }

        $servicio->update($data);

        return back()->with('success', "Servicio actualizado a: {$nuevoEstado}");
    }

    // Eliminar servicio
    public function destroy($id)
    {
        Servicio::where('empresa_id', auth()->user()->empresa_id)->findOrFail($id)->delete();

        return redirect()->route('panel.servicios.index')
            ->with('success', 'Servicio eliminado correctamente');
    }
}

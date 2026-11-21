<?php

// Controlador de servicios (borrado físico, bitácora consistente, permisos estrictos)

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Http\Requests\Panel\StoreServicioRequest;
use App\Models\AutorizacionesCancelacione;
use App\Models\BitacoraTiemposServicio;
use App\Models\CalificacionesServicio;
use App\Models\Cliente;
use App\Models\Cotizacione;
use App\Models\Oficina;
use App\Models\Operadore;
use App\Models\Servicio;
use App\Models\Unidade;
use App\Services\FacturaCalculator;
use App\Support\Auditoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class ServiciosController extends Controller
{
    private const ESTADOS_ACTIVOS = ['asignado', 'inicio_servicio', 'en_sitio_origen', 'salida_destino', 'en_destino'];

    public function index()
    {
        $user = Auth::user();
        $empresaId = $user->empresa_id;

        $query = Servicio::with(['cotizacion.cliente', 'cotizacion.tipoServicio', 'operador.empleado', 'unidad'])
            ->where('empresa_id', $empresaId);

        if ($user->rol === 'operador') {
            if (!$user->empleado_id) {
                return Inertia::render('Panel/Servicios/Index', [
                    'servicios' => [],
                    'puedeGestionar' => false,
                ]);
            }
            $query->whereHas('operador', fn ($q) => $q->where('empleado_id', $user->empleado_id));
        }

        $servicios = $query
            ->latest()
            ->get()
            ->map(fn ($s) => [
                'id' => $s->id,
                'folio' => 'SVC-' . str_pad((string) $s->id, 5, '0', STR_PAD_LEFT),
                'cliente' => $s->cotizacion?->cliente?->nombre ?? '—',
                'tipo' => $s->cotizacion?->tipoServicio?->nombre ?? '—',
                'operador' => $s->operador?->empleado?->nombre ?? '—',
                'origen' => $s->cotizacion?->origen_direccion ?? '—',
                'destino' => $s->cotizacion?->destino_direccion ?? '—',
                'fecha' => $s->created_at?->format('d/m/Y'),
                'estatus' => $s->estado ?? 'asignado',
            ]);

        return Inertia::render('Panel/Servicios/Index', [
            'servicios' => $servicios,
            'puedeGestionar' => in_array($user->rol, ['superadmin', 'admin', 'cotizador'], true),
        ]);
    }

    public function create()
    {
        $empresaId = Auth::user()->empresa_id;
        $operadoresOcupados = Servicio::where('empresa_id', $empresaId)->whereIn('estado', self::ESTADOS_ACTIVOS)->select('operador_id');
        $unidadesOcupadas = Servicio::where('empresa_id', $empresaId)->whereIn('estado', self::ESTADOS_ACTIVOS)->select('unidad_id');

        return Inertia::render('Panel/Servicios/Create', [
            'cotizaciones' => Cotizacione::where('empresa_id', $empresaId)
                ->where('estatus', 'aprobado')
                ->whereDoesntHave('servicio')
                ->get(['id', 'folio']),
            'operadores' => Operadore::with('empleado')->where('empresa_id', $empresaId)
                ->where('disponible', true)->whereNotIn('id', $operadoresOcupados)->get(),
            'unidades' => Unidade::where('empresa_id', $empresaId)->where('activo', true)
                ->whereNotIn('id', $unidadesOcupadas)->get(['id', 'placas', 'numero_economico']),
            'oficinas' => Oficina::where('empresa_id', $empresaId)->get(['id', 'nombre']),
        ]);
    }

    public function store(StoreServicioRequest $request)
    {
        $user = Auth::user();
        $data = $request->validated();
        $data['empresa_id'] = $user->empresa_id;
        $data['estado'] = 'asignado';

        DB::transaction(function () use ($data, $user) {
            $cotizacion = Cotizacione::where('empresa_id', $user->empresa_id)
                ->where('estatus', 'aprobado')->lockForUpdate()->findOrFail($data['cotizacion_id']);
            if ($cotizacion->servicio()->exists()) {
                throw ValidationException::withMessages(['cotizacion_id' => 'La cotización ya tiene un servicio asignado.']);
            }

            $unidad = Unidade::where('empresa_id', $user->empresa_id)->lockForUpdate()->findOrFail($data['unidad_id']);
            $operador = Operadore::where('empresa_id', $user->empresa_id)->lockForUpdate()->findOrFail($data['operador_id']);

            $unidadOcupada = Servicio::where('empresa_id', $user->empresa_id)->where('unidad_id', $unidad->id)->whereIn('estado', self::ESTADOS_ACTIVOS)->exists();
            $operadorOcupado = Servicio::where('empresa_id', $user->empresa_id)->where('operador_id', $operador->id)->whereIn('estado', self::ESTADOS_ACTIVOS)->exists();

            if (!$unidad->activo || $unidadOcupada) {
                throw ValidationException::withMessages(['unidad_id' => 'La unidad no está activa o ya tiene un servicio en curso.']);
            }
            if (!$operador->disponible || $operadorOcupado) {
                throw ValidationException::withMessages(['operador_id' => 'El operador no está disponible o ya tiene un servicio en curso.']);
            }

            $data['cargo_zona_especial'] = $cotizacion->cargo_zona_especial ?? 0;
            $data['costo_final_real'] = $cotizacion->costo_total;

            $servicio = Servicio::create($data);
            BitacoraTiemposServicio::create([
                'servicio_id' => $servicio->id,
                'hora_asignado' => now(),
            ]);

            $operador->update(['disponible' => false]);
        });

        return redirect()->route('panel.servicios.index')
            ->with('success', 'Servicio creado correctamente');
    }

    public function show($id)
    {
        $user = Auth::user();

        $query = Servicio::with([
            'cotizacion.cliente', 'cotizacion.tipoServicio',
            'operador.empleado', 'unidad', 'bitacoraTiemposServicio',
        ])->where('empresa_id', $user->empresa_id);

        if ($user->rol === 'cliente') {
            $cliente = Cliente::where('empresa_id', $user->empresa_id)->where('usuario_id', $user->id)->first();
            if (!$cliente) {
                abort(404);
            }
            $query->whereHas('cotizacion', fn ($q) => $q->where('cliente_id', $cliente->id));
        } elseif ($user->rol === 'operador') {
            if (!$user->empleado_id) {
                abort(403);
            }
            $query->whereHas('operador', fn ($q) => $q->where('empleado_id', $user->empleado_id));
        } elseif (!in_array($user->rol, ['superadmin', 'admin', 'cotizador'], true)) {
            abort(403);
        }

        $servicio = $query->findOrFail($id);
        $bit = $servicio->bitacoraTiemposServicio;

        return Inertia::render('Panel/Servicios/Show', [
            'servicio' => [
                'id' => $servicio->id,
                'folio' => 'SVC-' . str_pad((string) $servicio->id, 5, '0', STR_PAD_LEFT),
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
                'bitacora' => $bit ? [
                    'salida' => $bit->hora_asignado,
                    'inicio' => $bit->hora_inicio_servicio,
                    'llegada' => $bit->hora_en_sitio_origen,
                    'salida_destino' => $bit->hora_salida_destino,
                    'en_destino' => $bit->hora_en_destino,
                    'termino' => $bit->hora_finalizado,
                    'regreso' => $bit->hora_en_destino,
                ] : null,
            ],
        ]);
    }

    public function edit($id)
    {
        $empresaId = Auth::user()->empresa_id;
        $servicio = Servicio::with('factura')->where('empresa_id', $empresaId)->findOrFail($id);

        if ($servicio->factura) {
            return redirect()->route('panel.servicios.show', $servicio->id)
                ->with('error', 'No se puede editar un servicio que ya fue facturado.');
        }

        return Inertia::render('Panel/Servicios/Create', [
            'servicio' => $servicio,
            'cotizaciones' => Cotizacione::where('empresa_id', $empresaId)->get(['id', 'folio']),
            'operadores' => Operadore::with('empleado')->where('empresa_id', $empresaId)->get(),
            'unidades' => Unidade::where('empresa_id', $empresaId)->get(['id', 'placas', 'numero_economico']),
            'oficinas' => Oficina::where('empresa_id', $empresaId)->get(['id', 'nombre']),
        ]);
    }

    public function update(StoreServicioRequest $request, $id)
    {
        $empresaId = auth()->user()->empresa_id;
        $data = $request->validated();

        if (!isset($data['estado']) || $data['estado'] === null) {
            unset($data['estado']);
        }
        if (array_key_exists('cargo_zona_especial', $data) && $data['cargo_zona_especial'] === null) {
            $data['cargo_zona_especial'] = 0;
        }

        DB::transaction(function () use ($empresaId, $id, $data) {
            $servicio = Servicio::with('factura')->where('empresa_id', $empresaId)->lockForUpdate()->findOrFail($id);

            if ($servicio->factura) {
                throw ValidationException::withMessages(['servicio' => 'No se puede editar un servicio ya facturado.']);
            }

            $nuevoEstado = $data['estado'] ?? $servicio->estado;
            $operadorAnteriorId = $servicio->operador_id;
            $nuevoOperadorId = $data['operador_id'] ?? $servicio->operador_id;
            $nuevaUnidadId = $data['unidad_id'] ?? $servicio->unidad_id;

            if (in_array($nuevoEstado, self::ESTADOS_ACTIVOS, true)) {
                if (Servicio::where('empresa_id', $empresaId)->where('unidad_id', $nuevaUnidadId)->whereIn('estado', self::ESTADOS_ACTIVOS)->where('id', '!=', $servicio->id)->exists()) {
                    throw ValidationException::withMessages(['unidad_id' => 'La unidad seleccionada ya está asignada a un servicio activo.']);
                }
                if (Servicio::where('empresa_id', $empresaId)->where('operador_id', $nuevoOperadorId)->whereIn('estado', self::ESTADOS_ACTIVOS)->where('id', '!=', $servicio->id)->exists()) {
                    throw ValidationException::withMessages(['operador_id' => 'El operador seleccionado ya está asignado a un servicio activo.']);
                }
            }

            $servicio->update($data);

            // Sincronizar disponibilidad de operadores
            if ($operadorAnteriorId && ($operadorAnteriorId != $nuevoOperadorId || in_array($nuevoEstado, ['finalizado', 'cancelado'], true))) {
                Operadore::where('id', $operadorAnteriorId)->where('empresa_id', $empresaId)->update(['disponible' => true]);
            }
            if ($nuevoOperadorId && in_array($nuevoEstado, self::ESTADOS_ACTIVOS, true)) {
                Operadore::where('id', $nuevoOperadorId)->where('empresa_id', $empresaId)->update(['disponible' => false]);
            }
        });

        return redirect()->route('panel.servicios.index')
            ->with('success', 'Servicio actualizado correctamente');
    }

    public function avanzarEstado(Request $request, $id)
    {
        $user = auth()->user();
        $nuevoEstado = $request->input('estado');
        $flujo = ['asignado', 'inicio_servicio', 'en_sitio_origen', 'salida_destino', 'en_destino', 'finalizado'];

        if (!in_array($nuevoEstado, $flujo, true)) {
            return back()->with('error', 'Estado no válido.');
        }

        // Validar antes de tocar la BD
        if ($nuevoEstado === 'finalizado') {
            $request->validate([
                'kms_termino_servicio' => 'nullable|integer|min:0',
                'kms_cobrados_reales' => 'nullable|integer|min:0',
                'observaciones' => 'nullable|string|max:500',
            ]);
        }

        $servicio = Servicio::with(['operador', 'cotizacion'])
            ->where('empresa_id', $user->empresa_id)
            ->findOrFail($id);

        if ($user->rol === 'operador' && (!$user->empleado_id || !$servicio->operador || $servicio->operador->empleado_id !== $user->empleado_id)) {
            abort(403, 'Solo puedes actualizar servicios asignados a tu operador.');
        }

        $indiceActual = array_search($servicio->estado, $flujo, true);
        $indiceNuevo = array_search($nuevoEstado, $flujo, true);

        if ($indiceActual === false || $indiceNuevo !== $indiceActual + 1) {
            return back()->with('error', 'Transición no permitida: el servicio debe avanzar paso a paso desde su estado actual.');
        }

        DB::transaction(function () use ($request, $servicio, $nuevoEstado) {
            $data = ['estado' => $nuevoEstado];

            $bitacora = $servicio->bitacoraTiemposServicio
                ?? BitacoraTiemposServicio::create(['servicio_id' => $servicio->id, 'hora_asignado' => $servicio->created_at ?? now()]);

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
                if ($request->filled('kms_termino_servicio')) {
                    $data['kms_termino_servicio'] = (int) $request->kms_termino_servicio;
                }
                if ($request->filled('kms_cobrados_reales')) {
                    $data['kms_cobrados_reales'] = (int) $request->kms_cobrados_reales;
                }
                if ($request->filled('observaciones')) {
                    $data['observaciones'] = $request->observaciones;
                }

                $servicio->fill($data);
                if ($servicio->cotizacion) {
                    $calculo = app(FacturaCalculator::class)->calcular($servicio);
                    $data['costo_final_real'] = $calculo['total'];
                }

                if ($servicio->operador_id) {
                    Operadore::where('id', $servicio->operador_id)
                        ->where('empresa_id', $servicio->empresa_id)
                        ->update(['disponible' => true]);
                }
            }

            $servicio->update($data);
        });

        return back()->with('success', "Servicio actualizado a: {$nuevoEstado}");
    }

    public function solicitarCancelacion(Request $request, $id)
    {
        $request->validate([
            'motivo_cancelacion' => 'required|string|max:500',
            'tipo_incidencia' => 'required|in:cliente_cancela,operador_siniestro,falla_mecanica,unidad_ponchada,otro',
        ]);

        $user = auth()->user();

        $resultado = DB::transaction(function () use ($request, $user, $id) {
            $servicio = Servicio::with('operador')
                ->where('empresa_id', $user->empresa_id)
                ->lockForUpdate()
                ->findOrFail($id);

            if ($user->rol === 'operador' && (!$user->empleado_id || !$servicio->operador || $servicio->operador->empleado_id !== $user->empleado_id)) {
                abort(403, 'Solo puedes solicitar la cancelación de servicios asignados a tu operador.');
            }

            if (!in_array($servicio->estado, self::ESTADOS_ACTIVOS, true)) {
                return 'Este servicio no se puede solicitar en cancelación en su estado actual.';
            }

            $existente = AutorizacionesCancelacione::where('servicio_id', $servicio->id)
                ->where('estatus', 'pendiente')
                ->exists();
            if ($existente) {
                return 'Este servicio ya tiene una solicitud de cancelación pendiente.';
            }

            AutorizacionesCancelacione::create([
                'servicio_id' => $servicio->id,
                'usuario_solicitante_id' => $user->id,
                'motivo_cancelacion' => $request->motivo_cancelacion,
                'tipo_incidencia' => $request->tipo_incidencia,
                'estatus' => 'pendiente',
                'estado_previo' => $servicio->estado,
                'fecha_solicitud' => now(),
            ]);

            $servicio->update(['estado' => 'solicitud_cancelacion']);

            return null;
        });

        if ($resultado !== null) {
            return back()->with('error', $resultado);
        }

        return back()->with('success', 'Solicitud de cancelación enviada. Queda pendiente de autorización.');
    }

    // Página de evaluación: exclusiva para el cliente dueño sobre servicios finalizados
    public function evaluar($id)
    {
        $user = Auth::user();
        abort_unless($user->rol === 'cliente', 403);

        $cliente = Cliente::where('empresa_id', $user->empresa_id)
            ->where('usuario_id', $user->id)
            ->firstOrFail();

        $servicio = Servicio::with(['cotizacion.cliente', 'cotizacion.tipoServicio'])
            ->where('empresa_id', $user->empresa_id)
            ->where('estado', 'finalizado')
            ->whereHas('cotizacion', fn ($q) => $q->where('cliente_id', $cliente->id))
            ->findOrFail($id);

        return Inertia::render('Panel/EvaluarServicio', [
            'servicio' => [
                'id' => $servicio->id,
                'folio' => 'SVC-' . str_pad((string) $servicio->id, 5, '0', STR_PAD_LEFT),
                'cliente' => $servicio->cotizacion?->cliente?->nombre ?? '—',
                'tipo' => $servicio->cotizacion?->tipoServicio?->nombre ?? '—',
                'fecha' => $servicio->created_at?->format('d/m/Y'),
            ],
        ]);
    }

    public function guardarEvaluacion(Request $request, $id)
    {
        $usuario = auth()->user();
        abort_unless($usuario->rol === 'cliente', 403);

        $request->validate([
            'estrellas' => 'required|integer|min:1|max:5',
            'comentario' => 'nullable|string|max:500',
        ]);

        $cliente = Cliente::where('empresa_id', $usuario->empresa_id)
            ->where('usuario_id', $usuario->id)
            ->firstOrFail();

        $servicio = Servicio::with('cotizacion')
            ->where('empresa_id', $usuario->empresa_id)
            ->where('estado', 'finalizado')
            ->whereHas('cotizacion', fn ($q) => $q->where('cliente_id', $cliente->id))
            ->findOrFail($id);

        CalificacionesServicio::updateOrCreate(
            ['servicio_id' => $servicio->id],
            [
                'cliente_id' => $cliente->id,
                'estrellas' => (int) $request->estrellas,
                'comentario' => $request->comentario,
                'created_at' => now(),
            ]
        );

        return redirect()->route('panel.dashboard')->with('success', '¡Gracias por evaluar nuestro servicio!');
    }

    public function misEvaluaciones()
    {
        $user = auth()->user();
        $cliente = Cliente::where('empresa_id', $user->empresa_id)
            ->where('usuario_id', $user->id)
            ->first();

        if (!$cliente) {
            return Inertia::render('Panel/ClienteEvaluaciones', ['servicios' => []]);
        }

        $servicios = Servicio::with('cotizacion.tipoServicio')
            ->where('empresa_id', $user->empresa_id)
            ->whereHas('cotizacion', fn ($q) => $q->where('empresa_id', $user->empresa_id)->where('cliente_id', $cliente->id))
            ->where('estado', 'finalizado')
            ->whereDoesntHave('calificacionesServicio')
            ->latest()
            ->get()
            ->map(fn ($s) => [
                'id' => $s->id,
                'folio' => 'SVC-' . str_pad((string) $s->id, 5, '0', STR_PAD_LEFT),
                'tipo' => $s->cotizacion?->tipoServicio?->nombre ?? '—',
                'fecha' => $s->created_at?->format('d/m/Y'),
                'monto' => '$' . number_format($s->costo_final_real ?? $s->cotizacion?->costo_total ?? 0, 2),
            ]);

        return Inertia::render('Panel/ClienteEvaluaciones', ['servicios' => $servicios]);
    }

    // Borrado físico real del servicio
    public function destroy($id)
    {
        $servicio = Servicio::with('factura')
            ->where('empresa_id', auth()->user()->empresa_id)
            ->findOrFail($id);

        if ($servicio->factura) {
            return back()->with('error', 'No se puede eliminar un servicio que ya tiene una factura emitida.');
        }

        DB::transaction(function () use ($servicio) {
            Auditoria::registrar($servicio);

            if ($servicio->operador_id && in_array($servicio->estado, self::ESTADOS_ACTIVOS, true)) {
                Operadore::where('id', $servicio->operador_id)
                    ->where('empresa_id', $servicio->empresa_id)
                    ->update(['disponible' => true]);
            }

            $servicio->delete();
        });

        return redirect()->route('panel.servicios.index')
            ->with('success', 'Servicio eliminado físicamente de forma correcta.');
    }
}

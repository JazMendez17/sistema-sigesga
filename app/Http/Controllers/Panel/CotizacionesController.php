<?php

// Controlador de cotizaciones (borrado físico, cálculo seguro en backend y aislamiento multi-tenant)

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Http\Requests\Panel\BuscarRutasCotizacionRequest;
use App\Http\Requests\Panel\PagarCotizacionTarjetaRequest;
use App\Http\Requests\Panel\StoreCotizacionRequest;
use App\Mail\CotizacionMail;
use App\Models\BitacoraTiemposServicio;
use App\Models\CatalogoServicio;
use App\Models\Cliente;
use App\Models\Convenio;
use App\Models\ConvenioConceptoAdicional;
use App\Models\ConvenioTarifa;
use App\Models\Cotizacione;
use App\Models\Notificacione;
use App\Models\Operadore;
use App\Models\Servicio;
use App\Models\TarifasEmpresa;
use App\Models\Unidade;
use App\Models\Usuario;
use App\Services\GoogleMapsService;
use App\Services\PagoTarjetaService;
use App\Support\Auditoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use RuntimeException;

class CotizacionesController extends Controller
{
    private const ROLES_GESTION = ['superadmin', 'admin', 'cotizador'];

    /** Recalcula los importes financieros en el servidor y garantiza valores no nulos. */
    private function recalcularMontos(array $data): array
    {
        $distancia = max(0.0, (float) ($data['distancia_km'] ?? 0));
        $banderazo = max(0.0, (float) ($data['costo_banderazo'] ?? 0));
        $costoKm = max(0.0, (float) ($data['costo_km'] ?? 0));
        $kmIncluidos = max(0.0, (float) ($data['km_incluidos'] ?? 0));
        $kmExcedente = round(max(0.0, $distancia - $kmIncluidos), 2);
        $cargoZona = max(0.0, (float) ($data['cargo_zona_especial'] ?? 0));
        $incluyePeajes = (bool) ($data['incluye_peajes'] ?? false);
        $casetas = max(0.0, (float) ($data['costo_aprox_casetas'] ?? 0));
        $descuentoPct = max(0.0, min(100.0, (float) ($data['descuento_pct'] ?? 0)));

        $bruto = round($banderazo + ($kmExcedente * $costoKm) + $cargoZona, 2);
        $subtotalNeto = round(max($banderazo, $bruto * (1 - $descuentoPct / 100)), 2);
        $montoDescuento = round(max(0.0, $bruto - $subtotalNeto), 2);
        $montoIva = round($subtotalNeto * 0.16, 2);
        $casetasCobrar = $incluyePeajes ? 0.0 : $casetas;
        $costoTotal = round($subtotalNeto + $montoIva + $casetasCobrar, 2);

        $data['km_incluidos'] = $kmIncluidos;
        $data['km_excedente'] = $kmExcedente;
        $data['cargo_zona_especial'] = $cargoZona;
        $data['incluye_peajes'] = $incluyePeajes;
        $data['costo_aprox_casetas'] = $casetas;
        $data['descuento_pct'] = $descuentoPct;
        $data['subtotal'] = $subtotalNeto;
        $data['monto_descuento'] = $montoDescuento;
        $data['monto_iva'] = $montoIva;
        $data['costo_total'] = $costoTotal;

        return $data;
    }

    public function index()
    {
        $empresaId = Auth::user()->empresa_id;

        $cotizaciones = Cotizacione::with(['cliente', 'tipoServicio', 'usuarioCreador'])
            ->where('empresa_id', $empresaId)
            ->latest()
            ->get()
            ->map(fn ($c) => [
                'id' => $c->id,
                'folio' => $c->folio ?? 'COT-' . str_pad((string) $c->id, 5, '0', STR_PAD_LEFT),
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

    public function create()
    {
        $empresaId = Auth::user()->empresa_id;

        return Inertia::render('Panel/Cotizaciones/Create', [
            'clientes' => Cliente::where('empresa_id', $empresaId)->get(['id', 'nombre', 'apellido_paterno', 'apellido_materno', 'aseguradora_id']),
            'tiposServicio' => CatalogoServicio::where('empresa_id', $empresaId)->where('activo', true)->get(['id', 'nombre']),
            'googleMapsKey' => config('services.google.frontend_key'),
        ]);
    }

    public function rutas(BuscarRutasCotizacionRequest $request)
    {
        try {
            $resultado = app(GoogleMapsService::class)->rutas($request->validated());

            return response()->json(['ok' => true] + $resultado);
        } catch (RuntimeException $e) {
            Log::warning('Error de Google Routes API al cotizar', ['error' => $e->getMessage()]);

            return response()->json([
                'ok' => false,
                'error' => $e->getMessage(),
            ], 422);
        } catch (\Throwable $e) {
            Log::error('Error inesperado al calcular rutas', ['error' => $e->getMessage()]);

            return response()->json([
                'ok' => false,
                'error' => 'No se pudieron calcular las rutas en este momento. Intenta de nuevo.',
            ], 422);
        }
    }

    public function store(StoreCotizacionRequest $request)
    {
        $user = Auth::user();

        $data = $this->recalcularMontos($request->validated());
        $data['empresa_id'] = $user->empresa_id;
        $data['usuario_creador_id'] = $user->id;
        $data['estatus'] = 'pendiente';

        $clienteSeleccionado = Cliente::where('empresa_id', $user->empresa_id)->find($data['cliente_id']);
        $requierePagoTarjeta = empty($data['convenio_aplicado_id']) && empty($clienteSeleccionado?->aseguradora_id);
        $data['requiere_pago_tarjeta'] = $requierePagoTarjeta;
        $data['estatus_pago'] = $requierePagoTarjeta ? 'pendiente' : 'no_requerido';

        $cotizacion = DB::transaction(function () use ($data) {
            if (empty($data['folio'])) {
                $folios = Cotizacione::query()
                    ->where('folio', 'like', 'COT-%')
                    ->lockForUpdate()
                    ->pluck('folio');

                $maxNum = 0;
                foreach ($folios as $folio) {
                    if (preg_match('/^COT-(\d+)$/', (string) $folio, $m)) {
                        $maxNum = max($maxNum, (int) $m[1]);
                    }
                }
                $data['folio'] = 'COT-' . str_pad((string) ($maxNum + 1), 5, '0', STR_PAD_LEFT);
            }

            $cotizacion = Cotizacione::create($data);
            $cliente = Cliente::with('usuario')->find($cotizacion->cliente_id);
            $usuarioCliente = $cliente?->usuario;

            if ($usuarioCliente) {
                $this->crearNotificacion($cotizacion->empresa_id, $usuarioCliente->id,
                    "Nueva cotización {$cotizacion->folio} disponible para revisión y aprobación en tu portal.");
            }

            $this->notificarRolesCotizacion($cotizacion,
                "Nueva cotización {$cotizacion->folio} creada para {$cliente?->nombre}. Pendiente de aprobación del cliente.");

            return $cotizacion;
        });

        $cliente = Cliente::with(['usuario', 'aseguradora'])->find($cotizacion->cliente_id);
        $correo = $cliente?->email ?: $cliente?->usuario?->email;
        $enviado = true;

        if ($correo) {
            try {
                Mail::to($correo)->send(new CotizacionMail($cotizacion->load('cliente.aseguradora', 'tipoServicio')));
            } catch (\Throwable $e) {
                $enviado = false;
                Log::error('Error al enviar cotización por email', [
                    'cotizacion_id' => $cotizacion->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return redirect()->route('panel.cotizaciones.index')
            ->with('success', $enviado
                ? 'Cotización creada correctamente.'
                : 'Cotización creada, pero no se pudo enviar el correo al cliente.');
    }

    public function show($id)
    {
        $user = Auth::user();
        abort_unless(in_array($user->rol, ['superadmin', 'admin', 'cotizador', 'cliente'], true), 403);

        $query = Cotizacione::with(['cliente.aseguradora', 'convenioAplicado', 'tipoServicio', 'usuarioCreador', 'servicio', 'ultimoPago'])
            ->where('empresa_id', $user->empresa_id);

        if ($user->rol === 'cliente') {
            $cliente = Cliente::where('empresa_id', $user->empresa_id)
                ->where('usuario_id', $user->id)
                ->first();
            if (!$cliente) {
                abort(404);
            }
            $query->where('cliente_id', $cliente->id);
        }

        $cotizacion = $query->findOrFail($id);

        $estadosActivos = ['asignado', 'inicio_servicio', 'en_sitio_origen', 'salida_destino', 'en_destino'];
        $unidadesOcupadas = Servicio::where('empresa_id', $user->empresa_id)
            ->whereIn('estado', $estadosActivos)
            ->select('unidad_id');
        $operadoresOcupados = Servicio::where('empresa_id', $user->empresa_id)
            ->whereIn('estado', $estadosActivos)
            ->select('operador_id');

        $requierePagoTarjeta = $cotizacion->necesitaPagoTarjeta();
        $estatusPago = $cotizacion->estatus_pago ?: ($requierePagoTarjeta ? 'pendiente' : 'no_requerido');
        $ultimoPago = $cotizacion->ultimoPago;

        return Inertia::render('Panel/Cotizaciones/Show', [
            'cotizacion' => [
                'id' => $cotizacion->id,
                'folio' => $cotizacion->folio ?? 'COT-' . str_pad((string) $cotizacion->id, 5, '0', STR_PAD_LEFT),
                'cliente' => $cotizacion->cliente?->nombre ?? '—',
                'aseguradora' => $cotizacion->cliente?->aseguradora?->nombre,
                'convenio' => $cotizacion->convenioAplicado?->nombre_convenio_poliza,
                'tiene_convenio' => !$requierePagoTarjeta,
                'requiere_pago_tarjeta' => $requierePagoTarjeta,
                'estatus_pago' => $estatusPago,
                'pagada_at' => $cotizacion->pagada_at?->format('d/m/Y H:i'),
                'pago' => $ultimoPago ? [
                    'referencia' => $ultimoPago->referencia,
                    'monto' => (float) $ultimoPago->monto,
                    'moneda' => $ultimoPago->moneda,
                    'tarjeta_marca' => $ultimoPago->tarjeta_marca,
                    'tarjeta_ultimos4' => $ultimoPago->tarjeta_ultimos4,
                    'tarjeta_titular' => $ultimoPago->tarjeta_titular,
                    'pagado_en' => $ultimoPago->pagado_en?->format('d/m/Y H:i'),
                    'estatus' => $ultimoPago->estatus,
                ] : null,
                'fecha' => $cotizacion->created_at?->format('d/m/Y'),
                'tipo' => $cotizacion->tipoServicio?->nombre ?? '—',
                'estatus' => $cotizacion->estatus ?? 'pendiente',
                'origen' => $cotizacion->origen_direccion ?? '—',
                'destino' => $cotizacion->destino_direccion ?? '—',
                'distancia' => (float) ($cotizacion->distancia_km ?? 0),
                'banderazo' => (float) ($cotizacion->costo_banderazo ?? 0),
                'km_incluidos' => (float) ($cotizacion->km_incluidos ?? 0),
                'km_excedente' => (float) ($cotizacion->km_excedente ?? 0),
                'costo_km' => (float) ($cotizacion->costo_km ?? 0),
                'cargo_zona_especial' => (float) ($cotizacion->cargo_zona_especial ?? 0),
                'subtotal' => (float) ($cotizacion->subtotal ?? 0),
                'descuento_pct' => (float) ($cotizacion->descuento_pct ?? 0),
                'monto_descuento' => (float) ($cotizacion->monto_descuento ?? 0),
                'iva' => (float) ($cotizacion->monto_iva ?? 0),
                'casetas' => (float) ($cotizacion->costo_aprox_casetas ?? 0),
                'incluye_peajes' => (bool) ($cotizacion->incluye_peajes ?? false),
                'total_estimado' => (float) ($cotizacion->costo_total ?? 0),
                'usuario_creador' => $cotizacion->usuarioCreador?->name ?? '—',
                'servicio_id' => $cotizacion->servicio?->id,
                'cliente_aprobada_at' => $cotizacion->cliente_aprobada_at,
                'aprobada_internamente_at' => $cotizacion->aprobada_internamente_at,
                'puede_aprobar_cliente' => $user->rol === 'cliente'
                    && !$cotizacion->cliente_aprobada_at
                    && $cotizacion->estatus === 'pendiente',
                'puede_pagar_tarjeta' => $estatusPago !== 'pagado'
                    && $cotizacion->estatus === 'pendiente',
                'es_cliente' => $user->rol === 'cliente',
            ],
            'operadores' => $user->rol === 'cliente' ? [] : Operadore::with('empleado')
                ->where('empresa_id', $user->empresa_id)
                ->where('disponible', true)
                ->whereNotIn('id', $operadoresOcupados)
                ->get()
                ->map(fn ($o) => [
                    'id' => $o->id,
                    'nombre' => $o->empleado?->nombre ? trim($o->empleado->nombre . ' ' . ($o->empleado->apellido_paterno ?? '')) : ('Operador #' . $o->id),
                ]),
            'unidades' => $user->rol === 'cliente' ? [] : Unidade::where('empresa_id', $user->empresa_id)
                ->where('activo', true)
                ->whereNotIn('id', $unidadesOcupadas)
                ->get(['id', 'placas', 'numero_economico']),
        ]);
    }

    public function aprobar(Request $request, $id)
    {
        $empresaId = auth()->user()->empresa_id;
        $cotizacion = Cotizacione::where('empresa_id', $empresaId)->findOrFail($id);

        if ($cotizacion->estatus !== 'pendiente' || $cotizacion->servicio) {
            return back()->with('error', 'Solo se pueden aprobar cotizaciones en estado pendiente sin servicio asociado.');
        }

        if (!$cotizacion->cliente_aprobada_at) {
            return back()->with('error', 'La cotización debe ser aprobada por el cliente o aseguradora antes de crear el servicio.');
        }

        if ($cotizacion->necesitaPagoTarjeta() && $cotizacion->estatus_pago !== 'pagado') {
            return back()->with('error', 'Esta cotización no cuenta con convenio de aseguradora y requiere pago con tarjeta confirmado antes de crear el servicio.');
        }

        $data = $request->validate([
            'operador_id' => ['required', Rule::exists('operadores', 'id')->where('empresa_id', $empresaId)],
            'unidad_id' => ['required', Rule::exists('unidades', 'id')->where('empresa_id', $empresaId)],
        ]);

        DB::transaction(function () use ($cotizacion, $data, $empresaId) {
            $cotizacion = Cotizacione::where('empresa_id', $empresaId)->lockForUpdate()->findOrFail($cotizacion->id);
            if ($cotizacion->estatus !== 'pendiente' || $cotizacion->servicio()->exists()) {
                throw ValidationException::withMessages(['cotizacion' => 'La cotización ya fue procesada.']);
            }

            $operador = Operadore::where('empresa_id', $empresaId)->lockForUpdate()->findOrFail($data['operador_id']);
            $unidad = Unidade::where('empresa_id', $empresaId)->lockForUpdate()->findOrFail($data['unidad_id']);

            $estadosActivos = ['asignado', 'inicio_servicio', 'en_sitio_origen', 'salida_destino', 'en_destino'];
            $operadorOcupado = Servicio::where('empresa_id', $empresaId)->where('operador_id', $operador->id)->whereIn('estado', $estadosActivos)->exists();
            $unidadOcupada = Servicio::where('empresa_id', $empresaId)->where('unidad_id', $unidad->id)->whereIn('estado', $estadosActivos)->exists();

            if (!$operador->disponible || $operadorOcupado || !$unidad->activo || $unidadOcupada) {
                throw ValidationException::withMessages(['operador_id' => 'El operador o la unidad no están disponibles. Actualiza la página e intenta de nuevo.']);
            }

            $cotizacion->update([
                'estatus' => 'aprobado',
                'aprobada_internamente_at' => now(),
            ]);

            $servicio = Servicio::create([
                'empresa_id' => $cotizacion->empresa_id,
                'cotizacion_id' => $cotizacion->id,
                'operador_id' => $operador->id,
                'unidad_id' => $unidad->id,
                'estado' => 'asignado',
                'cargo_zona_especial' => $cotizacion->cargo_zona_especial ?? 0,
                'costo_final_real' => $cotizacion->costo_total,
            ]);

            BitacoraTiemposServicio::create([
                'servicio_id' => $servicio->id,
                'hora_asignado' => now(),
            ]);

            $operador->update(['disponible' => false]);
        });

        return back()->with('success', 'Cotización aprobada y servicio creado correctamente.');
    }

    public function rechazar(Request $request, $id)
    {
        $cotizacion = Cotizacione::with('servicio')
            ->where('empresa_id', auth()->user()->empresa_id)
            ->findOrFail($id);

        if ($cotizacion->estatus !== 'pendiente' || $cotizacion->servicio) {
            return back()->with('error', 'Solo se pueden rechazar cotizaciones pendientes sin servicio asignado.');
        }

        $cotizacion->update(['estatus' => 'rechazado']);

        return back()->with('success', 'Cotización rechazada.');
    }

    public function aprobarCliente($id)
    {
        $usuario = auth()->user();
        abort_unless($usuario->rol === 'cliente', 403);

        $cliente = Cliente::where('empresa_id', $usuario->empresa_id)
            ->where('usuario_id', $usuario->id)
            ->firstOrFail();

        $estadoResultado = DB::transaction(function () use ($cliente, $id, $usuario) {
            $cotizacion = Cotizacione::with('cliente')
                ->where('empresa_id', $usuario->empresa_id)
                ->where('cliente_id', $cliente->id)
                ->lockForUpdate()
                ->findOrFail($id);

            if ($cotizacion->estatus !== 'pendiente' || $cotizacion->cliente_aprobada_at) {
                return 'no_disponible';
            }

            if ($cotizacion->necesitaPagoTarjeta() && $cotizacion->estatus_pago !== 'pagado') {
                return 'requiere_pago';
            }

            $cotizacion->update(['cliente_aprobada_at' => now()]);

            $ids = Usuario::where('empresa_id', $cotizacion->empresa_id)
                ->whereIn('rol', self::ROLES_GESTION)
                ->pluck('id')
                ->when($cotizacion->usuario_creador_id, fn ($col) => $col->push($cotizacion->usuario_creador_id))
                ->unique();

            foreach ($ids as $usuarioId) {
                $this->crearNotificacion($cotizacion->empresa_id, $usuarioId,
                    "El cliente aprobó la cotización {$cotizacion->folio}. Requiere aprobación interna.");
            }

            return 'ok';
        });

        if ($estadoResultado === 'requiere_pago') {
            return back()->with('error', 'Esta cotización no cuenta con convenio de aseguradora. Ingresa los datos de tu tarjeta para realizar el cobro y aprobarla.');
        }

        if ($estadoResultado !== 'ok') {
            return back()->with('error', 'Esta cotización ya no está disponible para aprobación.');
        }

        return back()->with('success', 'Cotización aprobada. El equipo interno continuará con la autorización.');
    }

    /** Procesa el cobro con tarjeta para cotizaciones sin convenio de aseguradora y aprueba la cotización del cliente. */
    public function pagarTarjeta(PagarCotizacionTarjetaRequest $request, $id, PagoTarjetaService $pagoService)
    {
        $usuario = auth()->user();
        abort_unless(in_array($usuario->rol, ['superadmin', 'admin', 'cotizador', 'cliente'], true), 403);

        $query = Cotizacione::with('cliente.usuario')
            ->where('empresa_id', $usuario->empresa_id);

        if ($usuario->rol === 'cliente') {
            $cliente = Cliente::where('empresa_id', $usuario->empresa_id)
                ->where('usuario_id', $usuario->id)
                ->firstOrFail();
            $query->where('cliente_id', $cliente->id);
        }

        $cotizacion = $query->findOrFail($id);

        $pago = $pagoService->cobrar($cotizacion, $request->validated(), $usuario->id);

        $ids = Usuario::where('empresa_id', $cotizacion->empresa_id)
            ->whereIn('rol', self::ROLES_GESTION)
            ->pluck('id')
            ->when($cotizacion->usuario_creador_id, fn ($col) => $col->push($cotizacion->usuario_creador_id))
            ->unique();

        $montoFmt = number_format((float) $pago->monto, 2);
        foreach ($ids as $uid) {
            $this->crearNotificacion(
                $cotizacion->empresa_id,
                $uid,
                "Pago con tarjeta confirmado (\${$montoFmt} MXN · {$pago->tarjeta_marca} •••• {$pago->tarjeta_ultimos4} · Ref: {$pago->referencia}) para la cotización {$cotizacion->folio}. Lista para asignar servicio."
            );
        }

        if ($cotizacion->cliente?->usuario_id && $cotizacion->cliente->usuario_id !== $usuario->id) {
            $this->crearNotificacion(
                $cotizacion->empresa_id,
                $cotizacion->cliente->usuario_id,
                "Se confirmó tu pago con tarjeta por \${$montoFmt} MXN (Ref: {$pago->referencia}) para la cotización {$cotizacion->folio}."
            );
        }

        return back()->with('success', "Pago con tarjeta aprobado (Ref: {$pago->referencia}). Cotización lista para asignación de servicio.");
    }

    protected function crearNotificacion(int $empresaId, int $usuarioId, string $mensaje): void
    {
        Notificacione::create([
            'empresa_id' => $empresaId,
            'usuario_id' => $usuarioId,
            'mensaje' => $mensaje,
            'canal' => 'sistema_push',
            'estado' => 'pendiente',
        ]);
    }

    protected function notificarRolesCotizacion(Cotizacione $cotizacion, string $mensaje): void
    {
        Usuario::where('empresa_id', $cotizacion->empresa_id)
            ->whereIn('rol', self::ROLES_GESTION)
            ->where('id', '!=', auth()->id())
            ->pluck('id')
            ->each(fn ($usuarioId) => $this->crearNotificacion($cotizacion->empresa_id, $usuarioId, $mensaje));
    }

    public function edit($id)
    {
        $empresaId = Auth::user()->empresa_id;
        $cotizacion = Cotizacione::with('servicio')
            ->where('empresa_id', $empresaId)
            ->findOrFail($id);

        if ($cotizacion->estatus === 'aprobado' || $cotizacion->servicio || $cotizacion->estatus_pago === 'pagado') {
            return redirect()->route('panel.cotizaciones.show', $cotizacion->id)
                ->with('error', 'No se puede editar una cotización ya aprobada, pagada o con servicio asociado.');
        }

        return Inertia::render('Panel/Cotizaciones/Create', [
            'cotizacion' => $cotizacion,
            'clientes' => Cliente::where('empresa_id', $empresaId)->get(['id', 'nombre', 'apellido_paterno', 'apellido_materno', 'aseguradora_id']),
            'tiposServicio' => CatalogoServicio::where('empresa_id', $empresaId)->get(['id', 'nombre']),
            'googleMapsKey' => config('services.google.frontend_key'),
        ]);
    }

    public function update(StoreCotizacionRequest $request, $id)
    {
        $cotizacion = Cotizacione::with('servicio')
            ->where('empresa_id', auth()->user()->empresa_id)
            ->findOrFail($id);

        if ($cotizacion->estatus === 'aprobado' || $cotizacion->servicio || $cotizacion->estatus_pago === 'pagado') {
            return redirect()->route('panel.cotizaciones.index')
                ->with('error', 'No se puede modificar una cotización aprobada, pagada o con servicio asignado.');
        }

        $data = $this->recalcularMontos($request->validated());
        if (empty($data['folio'])) {
            unset($data['folio']);
        }

        $clienteSeleccionado = Cliente::where('empresa_id', auth()->user()->empresa_id)->find($data['cliente_id']);
        $requierePagoTarjeta = empty($data['convenio_aplicado_id']) && empty($clienteSeleccionado?->aseguradora_id);
        $data['requiere_pago_tarjeta'] = $requierePagoTarjeta;
        $data['estatus_pago'] = $requierePagoTarjeta ? 'pendiente' : 'no_requerido';

        $cotizacion->update($data);

        return redirect()->route('panel.cotizaciones.index')
            ->with('success', 'Cotización actualizada correctamente');
    }

    // Borrado físico real de la cotización
    public function destroy($id)
    {
        $cotizacion = Cotizacione::with('servicio')
            ->where('empresa_id', auth()->user()->empresa_id)
            ->findOrFail($id);

        if ($cotizacion->servicio) {
            return back()->with('error', 'No se puede eliminar una cotización que ya tiene un servicio asociado.');
        }

        DB::transaction(function () use ($cotizacion) {
            Auditoria::registrar($cotizacion);
            $cotizacion->delete();
        });

        return redirect()->route('panel.cotizaciones.index')
            ->with('success', 'Cotización eliminada físicamente de forma correcta.');
    }

    public function misCotizaciones()
    {
        $user = auth()->user();
        $cliente = Cliente::where('empresa_id', $user->empresa_id)
            ->where('usuario_id', $user->id)
            ->first();

        if (!$cliente) {
            return Inertia::render('Panel/ClienteCotizaciones', ['cotizaciones' => []]);
        }

        $cotizaciones = Cotizacione::where('empresa_id', $user->empresa_id)
            ->where('cliente_id', $cliente->id)
            ->with(['tipoServicio', 'cliente'])
            ->latest()
            ->get()
            ->map(function ($c) {
                $requierePago = $c->necesitaPagoTarjeta();
                $estatusPago = $c->estatus_pago ?: ($requierePago ? 'pendiente' : 'no_requerido');

                return [
                    'id' => $c->id,
                    'folio' => $c->folio ?? 'COT-' . str_pad((string) $c->id, 5, '0', STR_PAD_LEFT),
                    'tipo' => $c->tipoServicio?->nombre ?? '—',
                    'origen' => $c->origen_direccion ?? '—',
                    'destino' => $c->destino_direccion ?? '—',
                    'estatus' => $c->estatus,
                    'estatus_pago' => $estatusPago,
                    'requiere_pago_tarjeta' => $requierePago,
                    'puede_pagar_tarjeta' => $estatusPago !== 'pagado' && $c->estatus === 'pendiente',
                    'fecha' => $c->created_at?->format('d/m/Y'),
                    'total' => '$' . number_format($c->costo_total ?? 0, 2),
                ];
            });

        return Inertia::render('Panel/ClienteCotizaciones', ['cotizaciones' => $cotizaciones]);
    }

    public function obtenerTarifa(Request $request)
    {
        $empresaId = auth()->user()->empresa_id;
        $cliente = Cliente::with('aseguradora')->where('empresa_id', $empresaId)->find($request->cliente_id);
        $tipoServicioId = $request->tipo_servicio_id;

        if (!$cliente || !$tipoServicioId || !CatalogoServicio::where('empresa_id', $empresaId)->whereKey($tipoServicioId)->exists()) {
            return response()->json(null);
        }

        if ($cliente->aseguradora_id) {
            $hoy = now()->toDateString();
            $convenio = Convenio::where('aseguradora_id', $cliente->aseguradora_id)
                ->where('empresa_id', $empresaId)
                ->where('estatus', 'vigente')
                ->where(fn ($q) => $q->whereNull('fecha_inicio')->orWhere('fecha_inicio', '<=', $hoy))
                ->where(fn ($q) => $q->whereNull('fecha_fin')->orWhere('fecha_fin', '>=', $hoy))
                ->first();

            if ($convenio) {
                $tarifa = ConvenioTarifa::where('convenio_id', $convenio->id)
                    ->where('servicio_id', $tipoServicioId)
                    ->first();

                $conceptos = ConvenioConceptoAdicional::where('convenio_id', $convenio->id)->first();
                $cubreCasetas = (bool) ($conceptos?->cubre_casetas ?? $convenio->cubre_casetas_peaje ?? false);

                if ($tarifa || $convenio->costo_banderazo !== null || $convenio->costo_km !== null) {
                    return response()->json([
                        'origen' => 'convenio',
                        'convenio_id' => $convenio->id,
                        'convenio_nombre' => $convenio->nombre_convenio_poliza,
                        'banderazo' => (float) ($tarifa?->banderazo ?? $convenio->costo_banderazo ?? 0),
                        'km_incluidos' => (float) ($tarifa?->km_incluidos ?? $convenio->km_seguros_incluidos ?? 0),
                        'costo_km_extra' => (float) ($tarifa?->costo_km_extra ?? $convenio->costo_km ?? 0),
                        'cubre_casetas' => $cubreCasetas,
                        'incluye_casetas' => $cubreCasetas,
                        'tarifa_nocturna_recargo_pct' => (float) ($tarifa?->tarifa_nocturna_recargo_pct ?? 0),
                        'tarifa_domingo_festivo_recargo_pct' => (float) ($tarifa?->tarifa_domingo_festivo_recargo_pct ?? 0),
                        'minutos_espera_incluidos' => (int) ($tarifa?->minutos_espera_incluidos ?? 0),
                        'costo_espera_adicional_hora' => (float) ($tarifa?->costo_espera_adicional_hora ?? 0),
                        'descuento_pct' => (float) ($tarifa?->descuento_pct ?? 0),
                    ]);
                }
            }
        }

        $tarifaPropia = TarifasEmpresa::where('tipo_servicio_id', $tipoServicioId)
            ->where('empresa_id', $empresaId)
            ->where('activo', true)
            ->first();

        if ($tarifaPropia) {
            return response()->json([
                'origen' => 'propia',
                'tarifa_empresa_id' => $tarifaPropia->id,
                'banderazo' => (float) ($tarifaPropia->costo_banderazo ?? 0),
                'km_incluidos' => (float) ($tarifaPropia->km_incluidos ?? 0),
                'costo_km_extra' => (float) ($tarifaPropia->costo_km_extra ?? $tarifaPropia->costo_km ?? 0),
                'cubre_casetas' => (bool) $tarifaPropia->cubre_casetas_peaje,
                'incluye_casetas' => (bool) $tarifaPropia->cubre_casetas_peaje,
                'descuento_pct' => (float) ($tarifaPropia->descuento_pct ?? 0),
            ]);
        }

        return response()->json(null);
    }
}

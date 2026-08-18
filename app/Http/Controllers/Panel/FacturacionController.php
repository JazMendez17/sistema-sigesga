<?php

// Controlador de facturación

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Mail\FacturaMail;
use App\Models\Factura;
use App\Models\Servicio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;

class FacturacionController extends Controller
{
    // Lista de facturas
    public function index()
    {
        $user = Auth::user();
        $empresaId = $user->empresa_id;

        $facturas = Factura::with(['cliente', 'servicio.cotizacion'])
            ->where('empresa_id', $empresaId)
            ->latest()
            ->get()
            ->map(fn ($f) => [
                'id' => $f->id,
                'folio' => $f->folio_factura ?? 'FAC-' . str_pad($f->id, 5, '0', STR_PAD_LEFT),
                'cliente' => $f->cliente?->nombre ?? '—',
                'correo_envio' => $f->correo_envio_factura ?? '—',
                'servicio' => $f->servicio_id ? 'SVC-' . str_pad($f->servicio_id, 5, '0', STR_PAD_LEFT) : '—',
                'subtotal' => (float) ($f->subtotal ?? 0),
                'iva' => (float) ($f->iva ?? 0),
                'total' => (float) ($f->total ?? 0),
                'estatus' => $f->estatus ?? 'vigente',
                'fecha' => $f->created_at?->format('d/m/Y'),
                'enviada' => !empty($f->correo_envio_factura),
            ]);

        return Inertia::render('Panel/Facturacion/Index', [
            'facturas' => $facturas,
            'servicios' => Servicio::with('cotizacion.cliente.aseguradora', 'cotizacion.tipoServicio')
                ->where('empresa_id', $empresaId)
                ->whereDoesntHave('factura')
                ->get()
                ->map(function ($s) {
                    // Obtener KM incluidos de la cotización o del convenio
                    $cot = $s->cotizacion;
                    $kmIncluidos = (float) ($cot?->km_incluidos ?? 0);
                    if ($kmIncluidos <= 0 && $cot?->convenio_aplicado_id) {
                        $tarifaConv = \App\Models\ConvenioTarifa::where('convenio_id', $cot->convenio_aplicado_id)
                            ->where('servicio_id', $cot->tipo_servicio_id)->first();
                        $kmIncluidos = (float) ($tarifaConv?->km_incluidos ?? 0);
                    }
                    if ($kmIncluidos <= 0) {
                        $tarifaPropia = \App\Models\TarifasEmpresa::where('tipo_servicio_id', $cot?->tipo_servicio_id)->where('activo', true)->first();
                        $kmIncluidos = (float) ($tarifaPropia?->km_incluidos ?? 0);
                    }

                    return [
                        'id' => $s->id,
                        'folio' => 'SVC-' . str_pad($s->id, 5, '0', STR_PAD_LEFT),
                        'cliente_id' => $cot?->cliente_id,
                        'cliente_nombre' => $cot?->cliente?->nombre ?? '—',
                        'cliente_email' => $cot?->cliente?->email ?? '',
                        'tipo_servicio' => $cot?->tipoServicio?->nombre ?? '—',
                        'aseguradora' => $cot?->cliente?->aseguradora?->nombre_comercial ?? ($cot?->cliente?->aseguradora?->nombre ?? 'Particular'),
                        'numero_poliza' => $cot?->cliente?->numero_poliza ?? '—',
                        'tipo_cobertura' => $cot?->cliente?->tipo_cobertura_poliza ?? '—',
                        'costo_banderazo' => (float) ($cot?->costo_banderazo ?? 0),
                        'costo_km' => (float) ($cot?->costo_km ?? 0),
                        'km_incluidos' => $kmIncluidos,
                        'distancia_km' => (float) ($cot?->distancia_km ?? 0),
                        'subtotal' => (float) ($cot?->subtotal ?? $cot?->costo_total ?? 0),
                        'monto_iva' => (float) ($cot?->monto_iva ?? 0),
                        'descuento_pct' => (float) ($cot?->descuento_pct ?? 0),
                        'costo_final_real' => (float) ($s->costo_final_real ?? $cot?->costo_total ?? 0),
                        'kms_termino' => $s->kms_termino_servicio,
                    ];
                }),
        ]);
    }

    // Generar nueva factura
    public function store(Request $request)
    {
        $user = Auth::user();

        $data = $request->validate([
            'servicio_id' => 'required|exists:servicios,id',
            'cliente_id' => 'required|exists:clientes,id',
            'subtotal' => 'required|numeric',
            'iva' => 'nullable|numeric',
            'total' => 'required|numeric',
            'correo_envio_factura' => 'nullable|email|max:150',
        ]);

        $data['empresa_id'] = $user->empresa_id;
        $data['estatus'] = 'vigente';

        $factura = DB::transaction(function () use ($data) {
            $data['folio_factura'] = 'FAC-' . str_pad((Factura::query()->lockForUpdate()->max('id') ?? 0) + 1, 5, '0', STR_PAD_LEFT);

            return Factura::create($data);
        });

        // Enviar factura por email automáticamente si se proporcionó correo
        $this->enviarFacturaPorEmail($factura);

        return back()->with('success', 'Factura generada correctamente');
    }

    // Enviar factura por correo electrónico
    public function enviarEmail($id)
    {
        $factura = Factura::with('cliente')->where('empresa_id', auth()->user()->empresa_id)->findOrFail($id);

        if (empty($factura->correo_envio_factura)) {
            return back()->with('error', 'Esta factura no tiene un correo de envío registrado.');
        }

        $this->enviarFacturaPorEmail($factura);

        return back()->with('success', 'Factura enviada por correo a ' . $factura->correo_envio_factura);
    }

    // Método interno para enviar la factura por email
    protected function enviarFacturaPorEmail(Factura $factura): void
    {
        if (empty($factura->correo_envio_factura)) {
            return;
        }

        try {
            Mail::to($factura->correo_envio_factura)->send(new FacturaMail($factura));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error al enviar factura por email: ' . $e->getMessage());
        }
    }

    // Ver detalle de factura
    // Ver detalle de factura
    public function show($id)
    {
        $user = auth()->user();

        $query = Factura::with(['cliente.aseguradora', 'servicio.cotizacion.tipoServicio'])->where('empresa_id', $user->empresa_id);

        if ($user->rol === 'cliente') {
            $cliente = \App\Models\Cliente::where('usuario_id', $user->id)->first();
            if (!$cliente) {
                abort(404);
            }
            $query->where('cliente_id', $cliente->id);
        }

        $factura = $query->findOrFail($id);

        $cot = $factura->servicio?->cotizacion;

        return Inertia::render('Panel/Facturacion/Show', [
            'factura' => [
                'id' => $factura->id,
                'folio_factura' => $factura->folio_factura,
                'estatus' => $factura->estatus,
                'correo_envio_factura' => $factura->correo_envio_factura,
                'subtotal' => (float) $factura->subtotal,
                'iva' => (float) $factura->iva,
                'total' => (float) $factura->total,
                'fecha' => $factura->created_at?->format('d/m/Y H:i'),
                'cliente' => [
                    'nombre' => $factura->cliente?->nombre ?? '—',
                    'email' => $factura->cliente?->email ?? '—',
                    'telefono' => $factura->cliente?->telefono ?? '—',
                    'aseguradora' => $factura->cliente?->aseguradora?->nombre_comercial ?? 'Particular',
                    'poliza' => $factura->cliente?->numero_poliza ?? '—',
                    'cobertura' => $factura->cliente?->tipo_cobertura_poliza ?? '—',
                ],
                'servicio' => [
                    'id' => $factura->servicio_id,
                    'folio' => 'SVC-' . str_pad($factura->servicio_id, 5, '0', STR_PAD_LEFT),
                    'tipo' => $cot?->tipoServicio?->nombre ?? '—',
                    'origen' => $cot?->origen_direccion ?? '—',
                    'destino' => $cot?->destino_direccion ?? '—',
                    'distancia' => (float) ($cot?->distancia_km ?? 0),
                    'km_incluidos' => (float) ($cot?->km_incluidos ?? 0),
                    'banderazo' => (float) ($cot?->costo_banderazo ?? 0),
                    'costo_km' => (float) ($cot?->costo_km ?? 0),
                ],
            ],
        ]);
    }

    // Vista de facturas para el cliente
    public function misFacturas()
    {
        $cliente = \App\Models\Cliente::where('usuario_id', auth()->id())->first();
        $facturas = Factura::where('cliente_id', $cliente?->id)->latest()->get()
            ->map(fn ($f) => [
                'id' => $f->id,
                'folio' => $f->folio_factura ?? '—',
                'total' => '$' . number_format($f->total ?? 0, 2),
                'estatus' => $f->estatus ?? '—',
                'fecha' => $f->created_at?->format('d/m/Y'),
            ]);

        return Inertia::render('Panel/ClienteFacturas', ['facturas' => $facturas]);
    }
}

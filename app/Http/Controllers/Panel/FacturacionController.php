<?php

// Controlador de facturación

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Mail\FacturaMail;
use App\Models\Factura;
use App\Models\Servicio;
use App\Services\FacturaCalculator;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
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
                ->where('estado', 'finalizado')
                ->whereDoesntHave('factura')
                ->get()
                ->map(function ($s) {
                    $cot = $s->cotizacion;
                    $calculo = app(FacturaCalculator::class)->calcular($s);

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
                        'costo_banderazo' => $calculo['banderazo'],
                        'costo_km' => $calculo['costo_km'],
                        'km_incluidos' => $calculo['km_incluidos'],
                        'distancia_km' => $calculo['km'],
                        'subtotal' => $calculo['subtotal'],
                        'monto_iva' => $calculo['iva'],
                        'total' => $calculo['total'],
                        'descuento' => $calculo['descuento'],
                        'casetas' => $calculo['casetas'],
                        'kms_cobrados_reales' => $s->kms_cobrados_reales,
                        'descuento_pct' => (float) ($cot?->descuento_pct ?? 0),
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
            'servicio_id' => [
                'required',
                Rule::exists('servicios', 'id')
                    ->where('empresa_id', $user->empresa_id)
                    ->where('estado', 'finalizado')
                    ->where('eliminado', false),
            ],
            'correo_envio_factura' => 'required|email|max:150',
        ]);

        $servicio = Servicio::with('cotizacion')
            ->where('empresa_id', $user->empresa_id)
            ->where('estado', 'finalizado')
            ->whereDoesntHave('factura')
            ->findOrFail($data['servicio_id']);
        $calculo = app(FacturaCalculator::class)->calcular($servicio);
        $data['empresa_id'] = $user->empresa_id;
        $data['cliente_id'] = $servicio->cotizacion->cliente_id;
        $data['estatus'] = 'vigente';
        $data['subtotal'] = $calculo['subtotal'];
        $data['iva'] = $calculo['iva'];
        $data['total'] = $calculo['total'];

        $factura = DB::transaction(function () use ($data) {
            $data['folio_factura'] = 'FAC-' . str_pad((Factura::conEliminados()->lockForUpdate()->max('id') ?? 0) + 1, 5, '0', STR_PAD_LEFT);

            return Factura::create($data);
        });

        // Enviar factura por email automáticamente si se proporcionó correo
        $enviada = $this->enviarFacturaPorEmail($factura);

        return back()->with('success', $enviada
            ? 'Factura generada y enviada por correo.'
            : 'Factura generada. No se pudo enviar el correo; descarga el PDF desde el detalle.');
    }

    // Enviar factura por correo electrónico
    public function enviarEmail($id)
    {
        $factura = Factura::with('cliente')->where('empresa_id', auth()->user()->empresa_id)->findOrFail($id);

        if (empty($factura->correo_envio_factura)) {
            return back()->with('error', 'Esta factura no tiene un correo de envío registrado.');
        }

        if (!$this->enviarFacturaPorEmail($factura)) {
            return back()->with('error', 'No se pudo enviar el correo. Revisa la configuración de correo y vuelve a intentarlo.');
        }

        return back()->with('success', 'Factura enviada por correo a ' . $factura->correo_envio_factura);
    }

    // Método interno para enviar la factura por email
    protected function enviarFacturaPorEmail(Factura $factura): bool
    {
        if (empty($factura->correo_envio_factura)) {
            return false;
        }

        try {
            Mail::to($factura->correo_envio_factura)->send(new FacturaMail($factura->loadMissing('cliente', 'servicio.cotizacion.tipoServicio')));
            return true;
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Error al enviar factura por email.', [
                'factura_id' => $factura->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    public function pdf($id)
    {
        $user = auth()->user();
        abort_unless(in_array($user->rol, ['admin', 'cotizador', 'cliente'], true), 403);

        $query = Factura::with(['cliente.aseguradora', 'servicio.cotizacion.tipoServicio'])
            ->where('empresa_id', $user->empresa_id);
        if ($user->rol === 'cliente') {
            $cliente = \App\Models\Cliente::where('usuario_id', $user->id)->firstOrFail();
            $query->where('cliente_id', $cliente->id);
        }

        $factura = $query->findOrFail($id);

        return Pdf::loadView('emails.factura_pdf', (new FacturaMail($factura))->pdfData())
            ->download($factura->folio_factura . '.pdf');
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
                'es_cliente' => $user->rol === 'cliente',
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
        $facturas = Factura::where('empresa_id', auth()->user()->empresa_id)
            ->where('cliente_id', $cliente?->id)->latest()->get()
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

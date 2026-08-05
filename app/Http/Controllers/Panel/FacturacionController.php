<?php

// Controlador de facturación

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Mail\FacturaMail;
use App\Models\Factura;
use App\Models\Servicio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
            'servicios' => Servicio::with('cotizacion.cliente')
                ->where('empresa_id', $empresaId)
                ->whereDoesntHave('factura')
                ->get()
                ->map(fn ($s) => [
                    'id' => $s->id,
                    'folio' => 'SVC-' . str_pad($s->id, 5, '0', STR_PAD_LEFT),
                    'cliente_id' => $s->cotizacion?->cliente_id,
                ]),
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
        $data['folio_factura'] = 'FAC-' . str_pad(Factura::max('id') + 1, 5, '0', STR_PAD_LEFT);

        $factura = Factura::create($data);

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
    public function show($id)
    {
        $factura = Factura::with(['cliente', 'servicio.cotizacion'])->where('empresa_id', auth()->user()->empresa_id)->findOrFail($id);

        return Inertia::render('Panel/Facturacion/Show', [
            'factura' => $factura,
        ]);
    }
}

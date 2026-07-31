<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Factura;
use App\Models\Servicio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class FacturacionController extends Controller
{
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
                'servicio' => $f->servicio_id ? 'SVC-' . str_pad($f->servicio_id, 5, '0', STR_PAD_LEFT) : '—',
                'subtotal' => (float) ($f->subtotal ?? 0),
                'iva' => (float) ($f->iva ?? 0),
                'total' => (float) ($f->total ?? 0),
                'estatus' => $f->estatus ?? 'vigente',
                'fecha' => $f->created_at?->format('d/m/Y'),
            ]);

        return Inertia::render('Panel/Facturacion/Index', [
            'facturas' => $facturas,
            'servicios' => Servicio::with('cotizacion.cliente')
                ->where('empresa_id', $empresaId)
                ->whereDoesntHave('factura')
                ->get()
                ->map(fn ($s) => ['id' => $s->id, 'folio' => 'SVC-' . str_pad($s->id, 5, '0', STR_PAD_LEFT)]),
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $data = $request->validate([
            'servicio_id' => 'required|exists:servicios,id',
            'cliente_id' => 'required|exists:clientes,id',
            'subtotal' => 'required|numeric',
            'iva' => 'nullable|numeric',
            'total' => 'required|numeric',
        ]);

        $data['empresa_id'] = $user->empresa_id;
        $data['estatus'] = 'vigente';
        $data['folio_factura'] = 'FAC-' . str_pad(Factura::max('id') + 1, 5, '0', STR_PAD_LEFT);

        Factura::create($data);

        return back()->with('success', 'Factura generada correctamente');
    }

    public function show($id)
    {
        $factura = Factura::with(['cliente', 'servicio.cotizacion'])->where('empresa_id', auth()->user()->empresa_id)->findOrFail($id);

        return Inertia::render('Panel/Facturacion/Show', [
            'factura' => $factura,
        ]);
    }
}

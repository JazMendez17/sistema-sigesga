<?php

// Controlador de consulta de pagos con tarjeta para superadmin, admin y cotizador

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\PagoCotizacion;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class PagosController extends Controller
{
    public function comprobante($id)
    {
        $user = Auth::user();
        abort_unless(in_array($user->rol, ['superadmin', 'admin', 'cotizador', 'cliente'], true), 403);

        $query = PagoCotizacion::with(['cotizacion.cliente.usuario', 'cotizacion.tipoServicio', 'cliente'])
            ->where('estatus', 'pagado');

        if (!$user->isSuperAdmin()) {
            $query->where('empresa_id', $user->empresa_id);
        }

        if ($user->rol === 'cliente') {
            $cliente = Cliente::where('empresa_id', $user->empresa_id)
                ->where('usuario_id', $user->id)
                ->firstOrFail();
            $query->where('cliente_id', $cliente->id);
        }

        $pago = $query->findOrFail($id);
        $folio = $pago->cotizacion?->folio
            ?? ('COT-' . str_pad((string) $pago->cotizacion_id, 5, '0', STR_PAD_LEFT));

        return Pdf::loadView('emails.pago_comprobante_pdf', [
            'cotizacion' => $pago->cotizacion,
            'pago' => $pago,
        ])->download("Comprobante-{$folio}.pdf");
    }

    public function index()
    {
        $user = Auth::user();

        $query = PagoCotizacion::with(['cotizacion', 'cliente', 'usuario'])
            ->latest('pagado_en')
            ->latest('id');

        if (!$user->isSuperAdmin()) {
            $query->where('empresa_id', $user->empresa_id);
        }

        $registros = $query->get();

        $pagos = $registros->map(fn (PagoCotizacion $p) => [
            'id' => $p->id,
            'cotizacion_id' => $p->cotizacion_id,
            'folio_cotizacion' => $p->cotizacion?->folio ?? ('COT-' . str_pad((string) $p->cotizacion_id, 5, '0', STR_PAD_LEFT)),
            'cliente' => $p->cliente?->nombre ?? '—',
            'titular' => $p->tarjeta_titular,
            'tarjeta_marca' => $p->tarjeta_marca,
            'tarjeta_ultimos4' => $p->tarjeta_ultimos4,
            'tarjeta_resumen' => trim($p->tarjeta_marca . ' •••• ' . $p->tarjeta_ultimos4),
            'tarjeta_vigencia' => str_pad((string) $p->tarjeta_exp_mes, 2, '0', STR_PAD_LEFT) . '/' . $p->tarjeta_exp_anio,
            'monto' => (float) $p->monto,
            'moneda' => $p->moneda ?? 'MXN',
            'referencia' => $p->referencia,
            'clave_rastreo' => $p->pasarela_transaccion_id ?? '—',
            'pasarela' => match ($p->pasarela) {
                'mercadopago_orders' => 'Mercado Pago',
                'stripe' => 'Stripe',
                default => 'Pasarela interna',
            },
            'estatus' => $p->estatus,
            'registrado_por' => $p->usuario?->name ?? '—',
            'pagado_en' => $p->pagado_en?->format('d/m/Y H:i') ?? $p->created_at?->format('d/m/Y H:i') ?? '—',
        ]);

        $pagados = $registros->where('estatus', 'pagado');
        $inicioMes = now()->startOfMonth();

        return Inertia::render('Panel/Pagos/Index', [
            'pagos' => $pagos,
            'resumen' => [
                'total_cobrado' => round((float) $pagados->sum('monto'), 2),
                'total_pagos' => $pagados->count(),
                'cobrado_mes' => round((float) $pagados->filter(fn ($p) => $p->pagado_en && $p->pagado_en->gte($inicioMes))->sum('monto'), 2),
            ],
        ]);
    }
}

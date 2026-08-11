<?php

namespace App\Mail;

use App\Models\Factura;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class FacturaMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Factura $factura,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Factura ' . $this->factura->folio_factura . ' - SIGESGA',
        );
    }

    public function content(): Content
    {
        $servicio = $this->factura->servicio;
        $cotizacion = $servicio?->cotizacion;
        $cliente = $this->factura->cliente;

        $kmRecorridos = $servicio?->kms_termino_servicio ?? $cotizacion?->distancia_km ?? 0;
        $kmIncluidos = $cotizacion?->km_incluidos ?? 0;
        $kmExcedentes = max(0, $kmRecorridos - $kmIncluidos);
        $costoKm = $cotizacion?->costo_km ?? 0;

        return new Content(
            view: 'emails.factura_pdf',
            with: [
                'folio' => $this->factura->folio_factura,
                'fecha' => $this->factura->created_at?->format('d/m/Y'),
                'cliente' => $cliente?->nombre ?? 'Cliente',
                'email_cliente' => $cliente?->email ?? '',
                'aseguradora' => $cliente?->aseguradora?->nombre_comercial ?? 'Particular',
                'poliza' => $cliente?->numero_poliza ?? '—',
                'cobertura' => $cliente?->tipo_cobertura_poliza ?? '—',
                'servicio' => 'SVC-' . str_pad($servicio?->id ?? 0, 5, '0', STR_PAD_LEFT),
                'tipo_servicio' => $cotizacion?->tipoServicio?->nombre ?? '—',
                'origen' => $cotizacion?->origen_direccion ?? '—',
                'destino' => $cotizacion?->destino_direccion ?? '—',
                'banderazo' => number_format($cotizacion?->costo_banderazo ?? 0, 2),
                'km_incluidos' => $kmIncluidos,
                'km_recorridos' => $kmRecorridos,
                'km_excedentes' => $kmExcedentes,
                'costo_km' => number_format($costoKm, 2),
                'costo_km_extra_total' => number_format($kmExcedentes * $costoKm, 2),
                'descuento_pct' => $cotizacion?->descuento_pct ?? 0,
                'monto_descuento' => number_format($this->factura->subtotal * ($cotizacion?->descuento_pct ?? 0) / 100, 2),
                'subtotal' => number_format($this->factura->subtotal, 2),
                'iva' => number_format($this->factura->iva, 2),
                'total' => number_format($this->factura->total, 2),
            ]
        );
    }
}

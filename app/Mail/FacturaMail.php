<?php

namespace App\Mail;

use App\Models\Factura;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
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

    public function pdfData(): array
    {
        $s = $this->factura->servicio;
        $cot = $s?->cotizacion;
        $cli = $this->factura->cliente;
        $kmRec = $s?->kms_termino_servicio ?? $cot?->distancia_km ?? 0;
        $kmInc = $cot?->km_incluidos ?? 0;
        $kmExc = max(0, $kmRec - $kmInc);
        $costoKm = $cot?->costo_km ?? 0;
        $bruto = ($cot?->costo_banderazo ?? 0)
            + ($kmExc * $costoKm)
            + ($s?->cargo_zona_especial ?? 0);
        $casetas = (bool) ($cot?->incluye_peajes ?? false) ? 0 : (float) ($cot?->costo_aprox_casetas ?? 0);

        return [
            'folio' => $this->factura->folio_factura,
            'fecha' => $this->factura->created_at?->format('d/m/Y'),
            'cliente' => $cli?->nombre ?? 'Cliente',
            'email_cliente' => $cli?->email ?? '',
            'aseguradora' => $cli?->aseguradora?->nombre_comercial ?? 'Particular',
            'poliza' => $cli?->numero_poliza ?? '—',
            'cobertura' => $cli?->tipo_cobertura_poliza ?? '—',
            'servicio' => 'SVC-' . str_pad($s?->id ?? 0, 5, '0', STR_PAD_LEFT),
            'tipo_servicio' => $cot?->tipoServicio?->nombre ?? '—',
            'origen' => $cot?->origen_direccion ?? '—',
            'destino' => $cot?->destino_direccion ?? '—',
            'banderazo' => number_format($cot?->costo_banderazo ?? 0, 2),
            'km_incluidos' => $kmInc,
            'km_recorridos' => $kmRec,
            'km_excedentes' => $kmExc,
            'costo_km' => number_format($costoKm, 2),
            'costo_km_extra_total' => number_format($kmExc * $costoKm, 2),
            'cargo_zona_especial' => number_format($s?->cargo_zona_especial ?? 0, 2),
            'casetas' => number_format($casetas, 2),
            'descuento_pct' => $cot?->descuento_pct ?? 0,
            'monto_descuento' => number_format(max(0, $bruto - $this->factura->subtotal), 2),
            'subtotal' => number_format($this->factura->subtotal, 2),
            'iva' => number_format($this->factura->iva, 2),
            'total' => number_format($this->factura->total, 2),
        ];
    }

    public function content(): Content
    {
        return new Content(view: 'emails.factura_pdf', with: $this->pdfData());
    }

    public function attachments(): array
    {
        if (!class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            return [];
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('emails.factura_pdf', $this->pdfData());
        return [
            Attachment::fromData(fn () => $pdf->output(), $this->factura->folio_factura . '.pdf')
                ->withMime('application/pdf'),
        ];
    }
}

<?php

namespace App\Mail;

use App\Models\Factura;
use App\Services\FacturaCalculator;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
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

    public function pdfData(): array
    {
        $s = $this->factura->servicio;
        $cot = $s?->cotizacion;
        $cli = $this->factura->cliente;

        if ($s && $cot) {
            $calc = app(FacturaCalculator::class)->calcular($s);
        } else {
            $calc = [
                'banderazo' => 0.0,
                'km_incluidos' => 0.0,
                'km' => 0.0,
                'km_excedentes' => 0.0,
                'costo_km' => 0.0,
                'cargo_zona_especial' => 0.0,
                'casetas' => 0.0,
                'descuento' => 0.0,
            ];
        }

        return [
            'folio' => $this->factura->folio_factura,
            'fecha' => $this->factura->created_at?->format('d/m/Y'),
            'cliente' => $cli?->nombre ?? 'Cliente',
            'email_cliente' => $cli?->email ?: null,
            'aseguradora' => $cli?->aseguradora?->nombre_comercial ?? $cli?->aseguradora?->nombre ?? 'Particular',
            'poliza' => $cli?->numero_poliza ?? '—',
            'cobertura' => $cli?->tipo_cobertura_poliza ?? '—',
            'servicio' => 'SVC-' . str_pad((string) ($s?->id ?? 0), 5, '0', STR_PAD_LEFT),
            'tipo_servicio' => $cot?->tipoServicio?->nombre ?? '—',
            'origen' => $cot?->origen_direccion ?? '—',
            'destino' => $cot?->destino_direccion ?? '—',
            'banderazo' => number_format($calc['banderazo'], 2),
            'km_incluidos' => $calc['km_incluidos'],
            'km_recorridos' => $calc['km'],
            'km_excedentes' => $calc['km_excedentes'],
            'costo_km' => number_format($calc['costo_km'], 2),
            'costo_km_extra_total' => number_format($calc['km_excedentes'] * $calc['costo_km'], 2),
            'cargo_zona_especial' => number_format($calc['cargo_zona_especial'], 2),
            'cargo_zona_especial_raw' => $calc['cargo_zona_especial'],
            'casetas' => number_format($calc['casetas'], 2),
            'casetas_raw' => $calc['casetas'],
            'descuento_pct' => $cot?->descuento_pct ?? 0,
            'monto_descuento' => number_format($calc['descuento'], 2),
            'subtotal' => number_format((float) $this->factura->subtotal, 2),
            'iva' => number_format((float) $this->factura->iva, 2),
            'total' => number_format((float) $this->factura->total, 2),
        ];
    }

    public function content(): Content
    {
        $view = view()->exists('emails.factura') ? 'emails.factura' : 'emails.factura_pdf';

        return new Content(view: $view, with: $this->pdfData());
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

<?php

namespace App\Mail;

use App\Models\Cotizacione;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;

class CotizacionMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Cotizacione $cotizacion) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Nueva cotización ' . $this->cotizacion->folio . ' - SIGESGA',
        );
    }

    protected function data(): array
    {
        $cli = $this->cotizacion->cliente;
        $kmExc = $this->cotizacion->km_excedente ?? 0;
        $costoKm = $this->cotizacion->costo_km ?? 0;

        return [
            'cotizacion' => $this->cotizacion,
            'urlAprobacion' => url('/panel/cliente/cotizaciones/' . $this->cotizacion->id),
            'folio' => $this->cotizacion->folio,
            'fecha' => $this->cotizacion->created_at?->format('d/m/Y'),
            'cliente' => $cli?->nombre ?? 'Cliente',
            'email_cliente' => $cli?->email ?? '',
            'aseguradora' => $cli?->aseguradora?->nombre_comercial ?? 'Particular',
            'poliza' => $cli?->numero_poliza ?? '—',
            'tipo_servicio' => $this->cotizacion->tipoServicio?->nombre ?? '—',
            'origen' => $this->cotizacion->origen_direccion ?? '—',
            'destino' => $this->cotizacion->destino_direccion ?? '—',
            'distancia' => number_format($this->cotizacion->distancia_km ?? 0, 2),
            'banderazo' => number_format($this->cotizacion->costo_banderazo ?? 0, 2),
            'km_incluidos' => number_format($this->cotizacion->km_incluidos ?? 0, 2),
            'km_excedente' => number_format($kmExc, 2),
            'costo_km' => number_format($costoKm, 2),
            'costo_km_extra_total' => number_format($kmExc * $costoKm, 2),
            'subtotal' => number_format($this->cotizacion->subtotal ?? 0, 2),
            'descuento_pct' => number_format($this->cotizacion->descuento_pct ?? 0, 2),
            'monto_descuento' => number_format($this->cotizacion->monto_descuento ?? 0, 2),
            'iva' => number_format($this->cotizacion->monto_iva ?? 0, 2),
            'casetas' => number_format($this->cotizacion->costo_aprox_casetas ?? 0, 2),
            'incluye_peajes' => (bool) $this->cotizacion->incluye_peajes,
            'total' => number_format($this->cotizacion->costo_total ?? 0, 2),
        ];
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.cotizacion',
            with: $this->data(),
        );
    }

    public function attachments(): array
    {
        if (!class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            return [];
        }

        $data = $this->data();
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('emails.cotizacion_pdf', $data);
        return [
            Attachment::fromData(fn () => $pdf->output(), $this->cotizacion->folio . '.pdf')
                ->withMime('application/pdf'),
        ];
    }
}

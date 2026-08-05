<?php

namespace App\Mail;

use App\Models\Factura;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

// Mailable para enviar facturas por correo electrónico
class FacturaMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Factura $factura,
    ) {}

    // Asunto y remitente del correo
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Factura ' . $this->factura->folio_factura . ' - SIGESGA',
        );
    }

    // Contenido del correo con los datos de la factura
    public function content(): Content
    {
        return new Content(
            view: 'emails.factura',
            with: [
                'factura' => $this->factura,
                'cliente' => $this->factura->cliente?->nombre ?? 'Cliente',
                'folio' => $this->factura->folio_factura,
                'subtotal' => number_format($this->factura->subtotal, 2),
                'iva' => number_format($this->factura->iva, 2),
                'total' => number_format($this->factura->total, 2),
                'fecha' => $this->factura->created_at?->format('d/m/Y'),
            ],
        );
    }
}

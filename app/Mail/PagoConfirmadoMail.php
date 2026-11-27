<?php

namespace App\Mail;

use App\Models\Cotizacione;
use App\Models\PagoCotizacion;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PagoConfirmadoMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Cotizacione $cotizacion,
        public PagoCotizacion $pago,
        public bool $paraEquipo = false
    ) {
    }

    public function envelope(): Envelope
    {
        $folio = $this->cotizacion->folio ?? ('COT-' . str_pad((string) $this->cotizacion->id, 5, '0', STR_PAD_LEFT));
        $asunto = $this->paraEquipo
            ? "Pago confirmado - Cotización {$folio} - SIGESGA"
            : "Confirmación de pago - Cotización {$folio} - SIGESGA";

        return new Envelope(subject: $asunto);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.pago_confirmado');
    }

    public function attachments(): array
    {
        $folio = $this->cotizacion->folio
            ?? ('COT-' . str_pad((string) $this->cotizacion->id, 5, '0', STR_PAD_LEFT));
        $this->cotizacion->loadMissing(['cliente', 'tipoServicio']);
        $pdf = Pdf::loadView('emails.pago_comprobante_pdf', [
            'cotizacion' => $this->cotizacion,
            'pago' => $this->pago,
        ]);

        return [
            Attachment::fromData(fn () => $pdf->output(), "Comprobante-{$folio}.pdf")
                ->withMime('application/pdf'),
        ];
    }
}

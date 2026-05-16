<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NotificacionReservaConductor extends Mailable
{
    use Queueable, SerializesModels;

    public array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function envelope(): Envelope
    {
        $pasajero  = $this->data['pasajero']  ?? 'Pasajero';
        $ubicacion = $this->data['ubicacion'] ?? '';
        $asiento   = $this->data['asiento']   ?? '';

        return new Envelope(
            subject: "Nueva Reserva — {$pasajero} | {$ubicacion} | Asiento {$asiento}",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.reserva.conductor',
            with: ['data' => $this->data],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}

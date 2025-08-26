<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NuevaReservaConductor extends Mailable
{
    use Queueable, SerializesModels;

    public $emailData;

    /**
     * Create a new message instance.
     */
    public function __construct($emailData)
    {
        $this->emailData = $emailData;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Nueva Reserva de Viaje - Mecaza',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.nueva-reserva-conductor',
            with: [
                'conductorNombre' => $this->emailData['conductorNombre'],
                'conductorEmail' => $this->emailData['conductorEmail'],
                'pasajeroNombre' => $this->emailData['pasajeroNombre'],
                'asiento' => $this->emailData['asiento'],
                'ubicacion' => $this->emailData['ubicacion'],
                'placa' => $this->emailData['placa'],
                'destino' => $this->emailData['destino'],
                'fecha' => $this->emailData['fecha'],
                'hora' => $this->emailData['hora'],
                'fechaReserva' => $this->emailData['fechaReserva'],
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}

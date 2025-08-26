<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ConfirmacionReservaPasajero extends Mailable
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
            subject: 'Confirmación de Reserva - Mecaza',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.confirmacion-reserva-pasajero',
            with: [
                'pasajeroNombre' => $this->emailData['pasajeroNombre'],
                'conductorNombre' => $this->emailData['conductorNombre'],
                'asiento' => $this->emailData['asiento'],
                'ubicacion' => $this->emailData['ubicacion'],
                'placa' => $this->emailData['placa'],
                'destino' => $this->emailData['destino'],
                'fecha' => $this->emailData['fecha'],
                'hora' => $this->emailData['hora'],
                'fechaReserva' => $this->emailData['fechaReserva'],
                'telefonoConductor' => $this->emailData['telefonoConductor'] ?? 'No disponible',
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

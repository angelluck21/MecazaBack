<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CorreoReservaConfirmada extends Mailable
{
    use Queueable, SerializesModels;

    public array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function envelope(): Envelope
    {
        $pnr    = $this->data['pnr']    ?? '';
        $origen = $this->data['origen'] ?? '';

        return new Envelope(
            subject: "Tu reserva {$pnr} — {$origen}",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.reserva.confirmada',
            with: ['data' => $this->data],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}

<?php

namespace App\Mail\BuzonSugerencias;

use App\Modules\BuzonSugerencias\Infrastructure\Persistence\BuzonSugerencia;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NuevoTicketAgenteMail extends Mailable
{
    use Queueable, SerializesModels;

    public $sugerencia;

    /**
     * Create a new message instance.
     */
    public function __construct(BuzonSugerencia $sugerencia)
    {
        $this->sugerencia = $sugerencia;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Nuevo Ticket Pendiente: #' . $this->sugerencia->codigo_ticket,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.buzonSugerencias.notificacion_agente',
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

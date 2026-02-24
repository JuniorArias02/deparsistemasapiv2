<?php

namespace App\Mail;

use App\Models\CpPedido;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class GerenciaRejectedNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $pedido;
    public $motivo;

    /**
     * Create a new message instance.
     */
    public function __construct(CpPedido $pedido, string $motivo = null)
    {
        $this->pedido = $pedido;
        $this->motivo = $motivo;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Pedido Rechazado por Gerencia - NexaCore',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.orders.gerencia_rejected',
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

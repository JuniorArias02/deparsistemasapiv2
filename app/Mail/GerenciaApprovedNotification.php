<?php

namespace App\Mail;

use App\Models\CpPedido;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class GerenciaApprovedNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $pedido;

    /**
     * Create a new message instance.
     */
    public function __construct(CpPedido $pedido)
    {
        $this->pedido = $pedido;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Pedido Aprobado por Gerencia - Depart-Sistem',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.orders.gerencia_approved',
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

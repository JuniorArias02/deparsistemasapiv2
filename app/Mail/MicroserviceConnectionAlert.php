<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MicroserviceConnectionAlert extends Mailable
{
    use Queueable, SerializesModels;

    public string $status;
    public string $url;
    public string $errorMessage;

    /**
     * Create a new message instance.
     */
    public function __construct(string $status, string $url, string $errorMessage = '')
    {
        $this->status = $status;
        $this->url = $url;
        $this->errorMessage = $errorMessage;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = $this->status === 'down' 
            ? '🚨 URGENTE: Microservicio de Convertidor DESCONECTADO' 
            : '✅ INFO: Microservicio de Convertidor RECUPERADO';

        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.microservice_alert',
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

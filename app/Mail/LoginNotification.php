<?php

namespace App\Mail;

use App\Models\Usuario;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LoginNotification extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The user instance.
     *
     * @var Usuario
     */
    public $user;

    /**
     * The session details.
     *
     * @var array
     */
    public $details;

    /**
     * Create a new message instance.
     */
    public function __construct(Usuario $user, array $details)
    {
        $this->user = $user;
        $this->details = $details;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Alerta de Inicio de Sesión - NexaCore',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.auth.login_notification',
            with: [
                'browser' => $this->getBrowser($this->details['userAgent']),
                'os' => $this->getOs($this->details['userAgent']),
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

    private function getBrowser($userAgent)
    {
        if (strpos($userAgent, 'Chrome') !== false) return 'Google Chrome';
        if (strpos($userAgent, 'Firefox') !== false) return 'Mozilla Firefox';
        if (strpos($userAgent, 'Safari') !== false) return 'Apple Safari';
        if (strpos($userAgent, 'Edge') !== false) return 'Microsoft Edge';
        if (strpos($userAgent, 'MSIE') !== false || strpos($userAgent, 'Trident') !== false) return 'Internet Explorer';
        return 'Navegador Desconocido';
    }

    private function getOs($userAgent)
    {
        if (strpos($userAgent, 'Windows') !== false) return 'Windows';
        if (strpos($userAgent, 'Mac') !== false) return 'MacOS';
        if (strpos($userAgent, 'Linux') !== false) return 'Linux';
        if (strpos($userAgent, 'Android') !== false) return 'Android';
        if (strpos($userAgent, 'iPhone') !== false || strpos($userAgent, 'iPad') !== false) return 'iOS';
        return 'Sistema Operativo Desconocido';
    }
}

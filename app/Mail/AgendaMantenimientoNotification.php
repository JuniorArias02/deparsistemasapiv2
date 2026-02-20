<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AgendaMantenimientoNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $agenda;
    public $mantenimiento;
    public $assignedUser;
    public $scheduledBy;

    public function __construct($agenda, $mantenimiento, $assignedUser, $scheduledBy)
    {
        $this->agenda = $agenda;
        $this->mantenimiento = $mantenimiento;
        $this->assignedUser = $assignedUser;
        $this->scheduledBy = $scheduledBy;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Nueva Agenda de Mantenimiento Asignada - NexaCore',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.mantenimiento.agenda_asignada',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}

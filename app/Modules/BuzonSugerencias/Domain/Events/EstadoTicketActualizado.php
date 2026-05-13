<?php

namespace App\Modules\BuzonSugerencias\Domain\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EstadoTicketActualizado implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $estado;
    public $codigoTicket;

    public function __construct($estado, $codigoTicket)
    {
        $this->estado = $estado;
        $this->codigoTicket = $codigoTicket;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('buzon.ticket.' . $this->codigoTicket);
    }

    public function broadcastWith()
    {
        return [
            'codigo_ticket' => $this->codigoTicket,
            'estado' => [
                'id' => $this->estado->id,
                'nombre' => $this->estado->nombre,
            ]
        ];
    }
}

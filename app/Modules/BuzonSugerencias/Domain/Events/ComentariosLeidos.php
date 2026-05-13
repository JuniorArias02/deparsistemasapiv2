<?php

namespace App\Modules\BuzonSugerencias\Domain\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ComentariosLeidos implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $sugerencia_id;
    public $codigo_ticket;
    public $leido_por_usuario_id;

    public function __construct($sugerencia_id, $codigo_ticket, $leido_por_usuario_id)
    {
        $this->sugerencia_id = $sugerencia_id;
        $this->codigo_ticket = $codigo_ticket;
        $this->leido_por_usuario_id = $leido_por_usuario_id;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('buzon.ticket.' . $this->codigo_ticket),
        ];
    }
}

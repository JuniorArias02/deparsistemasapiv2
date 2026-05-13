<?php

namespace App\Modules\BuzonSugerencias\Domain\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NuevoComentarioPublicado implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $comentario;
    public $codigoTicket;

    public function __construct($comentario, $codigoTicket)
    {
        $this->comentario = $comentario;
        $this->codigoTicket = $codigoTicket;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('buzon.ticket.' . $this->codigoTicket);
    }

    public function broadcastWith()
    {
        // Evitamos enviar información excesiva, serializamos solo lo necesario
        return [
            'id' => $this->comentario->id,
            'sugerencia_id' => $this->comentario->sugerencia_id,
            'usuario_id' => $this->comentario->usuario_id,
            'mensaje' => $this->comentario->mensaje,
            'fecha_comentario' => $this->comentario->fecha_comentario->toIso8601String(),
            'usuario' => [
                'id' => $this->comentario->usuario->id,
                'nombre_completo' => $this->comentario->usuario->nombre_completo,
            ]
        ];
    }
}

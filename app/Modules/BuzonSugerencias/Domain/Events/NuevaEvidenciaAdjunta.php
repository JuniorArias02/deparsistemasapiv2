<?php

namespace App\Modules\BuzonSugerencias\Domain\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NuevaEvidenciaAdjunta implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $adjuntos;
    public $codigoTicket;

    public function __construct($adjuntos, $codigoTicket)
    {
        $this->adjuntos = $adjuntos;
        $this->codigoTicket = $codigoTicket;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('buzon.ticket.' . $this->codigoTicket);
    }

    public function broadcastWith()
    {
        $mappedAdjuntos = [];
        foreach ($this->adjuntos as $adjunto) {
            $mappedAdjuntos[] = [
                'id' => $adjunto->id,
                'url_imagen' => $adjunto->url_imagen,
            ];
        }

        return [
            'codigo_ticket' => $this->codigoTicket,
            'adjuntos' => $mappedAdjuntos
        ];
    }
}

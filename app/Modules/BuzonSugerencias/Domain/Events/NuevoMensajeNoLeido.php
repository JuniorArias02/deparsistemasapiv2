<?php

namespace App\Modules\BuzonSugerencias\Domain\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NuevoMensajeNoLeido implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $canalDestino;
    public $notificacion;

    public function __construct(string $canalDestino)
    {
        $this->canalDestino = $canalDestino;
        $this->notificacion = [
            'tipo' => 'NUEVO_MENSAJE',
            'timestamp' => now()->toIso8601String()
        ];
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel($this->canalDestino),
        ];
    }
}

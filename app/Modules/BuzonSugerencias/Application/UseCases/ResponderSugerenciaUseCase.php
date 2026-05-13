<?php

namespace App\Modules\BuzonSugerencias\Application\UseCases;

use App\Modules\BuzonSugerencias\Infrastructure\Persistence\BuzonSugerencia;
use App\Modules\BuzonSugerencias\Infrastructure\Persistence\SugerenciaComentario;
use App\Modules\BuzonSugerencias\Domain\Events\NuevoComentarioPublicado;

class ResponderSugerenciaUseCase
{
    public function execute(int $sugerenciaId, int $usuarioId, string $mensaje)
    {
        $sugerencia = BuzonSugerencia::findOrFail($sugerenciaId);
        
        $comentario = SugerenciaComentario::create([
            'sugerencia_id' => $sugerencia->id,
            'usuario_id' => $usuarioId,
            'mensaje' => $mensaje,
            'fecha_comentario' => now(),
        ]);

        $comentario->load('usuario');
        event(new NuevoComentarioPublicado($comentario, $sugerencia->codigo_ticket));

        // Determinar a quién notificar (Notificación Global para el Navbar)
        if ((int)$usuarioId === (int)$sugerencia->creado_por) {
            // El usuario creador respondió. Notificar al agente asignado, o a todos los agentes si no hay asignado.
            if ($sugerencia->asignado_a) {
                event(new \App\Modules\BuzonSugerencias\Domain\Events\NuevoMensajeNoLeido("usuario.{$sugerencia->asignado_a}"));
            } else {
                event(new \App\Modules\BuzonSugerencias\Domain\Events\NuevoMensajeNoLeido("buzon.agentes"));
            }
        } else {
            // Un agente respondió. Notificar al creador.
            event(new \App\Modules\BuzonSugerencias\Domain\Events\NuevoMensajeNoLeido("usuario.{$sugerencia->creado_por}"));
        }

        return $comentario;
    }
}

<?php

namespace App\Modules\BuzonSugerencias\Application\UseCases;

use App\Modules\BuzonSugerencias\Infrastructure\Persistence\BuzonSugerencia;
use App\Modules\BuzonSugerencias\Infrastructure\Persistence\SugerenciaComentario;
use Illuminate\Support\Facades\DB;

class MarcarComentariosLeidosUseCase
{
    public function execute(int $sugerenciaId, int $usuarioId)
    {
        // Marcar como leídos los comentarios de esta sugerencia que no fueron creados por este usuario
        $updatedCount = SugerenciaComentario::where('sugerencia_id', $sugerenciaId)
            ->where('usuario_id', '!=', $usuarioId)
            ->where('leido', false)
            ->update(['leido' => true]);

        if ($updatedCount > 0) {
            $sugerencia = BuzonSugerencia::find($sugerenciaId);
            if ($sugerencia) {
                event(new \App\Modules\BuzonSugerencias\Domain\Events\ComentariosLeidos($sugerenciaId, $sugerencia->codigo_ticket, $usuarioId));
                
                // Disparar evento global para que la campanita se actualice en quien lo lea
                event(new \App\Modules\BuzonSugerencias\Domain\Events\NuevoMensajeNoLeido("usuario.{$usuarioId}"));
            }
        }

        return true;
    }
}

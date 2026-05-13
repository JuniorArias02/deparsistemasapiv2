<?php

namespace App\Modules\BuzonSugerencias\Application\UseCases;

use App\Modules\BuzonSugerencias\Infrastructure\Persistence\SugerenciaComentario;

class ContarMensajesNoLeidosUseCase
{
    public function execute(int $usuarioId, bool $esAgente)
    {
        $query = SugerenciaComentario::join('buzon_sugerencia', 'sugerencia_comentarios.sugerencia_id', '=', 'buzon_sugerencia.id')
            ->where('sugerencia_comentarios.usuario_id', '!=', $usuarioId)
            ->where('sugerencia_comentarios.leido', false);

        if ($esAgente) {
            // El agente ve mensajes no leídos de los tickets que le están asignados
            // y, opcionalmente, de los no asignados. Solo contaremos los asignados a él.
            $query->where(function ($q) use ($usuarioId) {
                $q->where('buzon_sugerencia.asignado_a', $usuarioId)
                  ->orWhere('buzon_sugerencia.creado_por', $usuarioId);
            });
        } else {
            // El usuario normal solo ve mensajes no leídos de los tickets que creó
            $query->where('buzon_sugerencia.creado_por', $usuarioId);
        }

        return $query->count();
    }
}

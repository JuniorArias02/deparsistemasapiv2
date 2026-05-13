<?php

namespace App\Modules\BuzonSugerencias\Application\UseCases;

use App\Modules\BuzonSugerencias\Infrastructure\Persistence\BuzonSugerencia;
use App\Modules\BuzonSugerencias\Infrastructure\Persistence\SugerenciaComentario;
use Illuminate\Support\Facades\DB;

class ObtenerTicketsNoLeidosUseCase
{
    public function execute(int $usuarioId, bool $esAgente)
    {
        // Obtener IDs de sugerencias que tienen comentarios no leídos y no son de este usuario
        $query = SugerenciaComentario::join('buzon_sugerencia', 'sugerencia_comentarios.sugerencia_id', '=', 'buzon_sugerencia.id')
            ->select('buzon_sugerencia.id', 'buzon_sugerencia.codigo_ticket', 'buzon_sugerencia.asunto', DB::raw('COUNT(sugerencia_comentarios.id) as unread_count'), DB::raw('MAX(sugerencia_comentarios.fecha_comentario) as ultimo_mensaje'))
            ->where('sugerencia_comentarios.usuario_id', '!=', $usuarioId)
            ->where('sugerencia_comentarios.leido', false)
            ->groupBy('buzon_sugerencia.id', 'buzon_sugerencia.codigo_ticket', 'buzon_sugerencia.asunto');

        if ($esAgente) {
            $query->where(function ($q) use ($usuarioId) {
                $q->where('buzon_sugerencia.asignado_a', $usuarioId)
                  ->orWhere('buzon_sugerencia.creado_por', $usuarioId);
            });
        } else {
            $query->where('buzon_sugerencia.creado_por', $usuarioId);
        }

        return $query->orderBy('ultimo_mensaje', 'desc')->get();
    }
}

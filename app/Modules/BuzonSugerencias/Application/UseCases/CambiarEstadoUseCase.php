<?php

namespace App\Modules\BuzonSugerencias\Application\UseCases;

use App\Modules\BuzonSugerencias\Infrastructure\Persistence\BuzonSugerencia;
use App\Modules\BuzonSugerencias\Infrastructure\Persistence\EstadoTicket;
use App\Modules\BuzonSugerencias\Domain\Events\EstadoTicketActualizado;

class CambiarEstadoUseCase
{
    public function execute(int $sugerenciaId, int $estadoId)
    {
        $sugerencia = BuzonSugerencia::findOrFail($sugerenciaId);
        $estado = EstadoTicket::findOrFail($estadoId);

        $sugerencia->estado_id = $estado->id;

        if (in_array($estado->nombre, ['Cerrado', 'Resuelto'])) {
            $sugerencia->fecha_cierre = now();
        } else {
            $sugerencia->fecha_cierre = null;
        }

        $sugerencia->save();

        event(new EstadoTicketActualizado($estado, $sugerencia->codigo_ticket));

        return $sugerencia;
    }
}

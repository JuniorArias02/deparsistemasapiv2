<?php

namespace App\Modules\BuzonSugerencias\Application\UseCases;

use App\Modules\BuzonSugerencias\Infrastructure\Persistence\BuzonSugerencia;

class SeguimientoTicketUseCase
{
    public function execute(string $codigoTicket)
    {
        return BuzonSugerencia::with(['estado', 'adjuntos', 'comentarios.usuario', 'creador', 'asignado'])
            ->where('codigo_ticket', $codigoTicket)
            ->firstOrFail();
    }
}

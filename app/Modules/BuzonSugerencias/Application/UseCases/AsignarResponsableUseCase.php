<?php

namespace App\Modules\BuzonSugerencias\Application\UseCases;

use App\Modules\BuzonSugerencias\Infrastructure\Persistence\BuzonSugerencia;

class AsignarResponsableUseCase
{
    public function execute(int $sugerenciaId, int $usuarioId)
    {
        $sugerencia = BuzonSugerencia::findOrFail($sugerenciaId);
        $sugerencia->asignado_a = $usuarioId;
        $sugerencia->save();

        return $sugerencia;
    }
}

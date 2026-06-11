<?php

namespace App\Modules\GestionCompras\Application\UseCases\EntregaActivosFijos;

use App\Models\CpEntregaActivosFijos;

class ObtenerEntregasPorCoordinadorUseCase
{
    public function execute($coordinadorId)
    {
        return CpEntregaActivosFijos::with([
            'personal',
            'sede',
            'procesoSolicitante',
            'coordinador',
            'items.inventario'
        ])
            ->where('coordinador_id', $coordinadorId)
            ->orderBy('id', 'desc')
            ->get();
    }
}
<?php

namespace App\Modules\GestionCompras\Application\UseCases\EntregaActivosFijos;

use App\Models\Personal;

class ObtenerCoordinadoresEntregaUseCase
{
    public function execute()
    {
        return Personal::whereIn('id', function ($query) {
            $query->select('coordinador_id')
                ->from('cp_entrega_activos_fijos');
        })->get();
    }
}
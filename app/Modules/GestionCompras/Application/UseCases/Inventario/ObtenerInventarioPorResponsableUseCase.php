<?php

namespace App\Modules\GestionCompras\Application\UseCases\Inventario;

use App\Models\Inventario;

class ObtenerInventarioPorResponsableUseCase
{
    public function execute($responsable_id, $coordinador_id)
    {
        return Inventario::where('responsable_id', $responsable_id)
            ->where('coordinador_id', $coordinador_id)
            ->get();
    }
}
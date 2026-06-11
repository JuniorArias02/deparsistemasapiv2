<?php

namespace App\Modules\GestionCompras\Application\UseCases\Inventario;

use App\Models\Inventario;

class EliminarInventarioUseCase
{
    public function execute($id)
    {
        $inventario = Inventario::findOrFail($id);
        $inventario->delete();
        return true;
    }
}
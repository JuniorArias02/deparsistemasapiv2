<?php

namespace App\Modules\GestionCompras\Application\UseCases\Inventario;

use App\Models\Inventario;

class ActualizarInventarioUseCase
{
    public function execute($id, array $data)
    {
        $inventario = Inventario::findOrFail($id);
        $inventario->update($data);
        return $inventario;
    }
}
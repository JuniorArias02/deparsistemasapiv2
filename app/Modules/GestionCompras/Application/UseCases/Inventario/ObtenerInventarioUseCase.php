<?php

namespace App\Modules\GestionCompras\Application\UseCases\Inventario;

use App\Models\Inventario;

class ObtenerInventarioUseCase
{
    public function execute($id)
    {
        return Inventario::with(['responsablePersonal', 'coordinadorPersonal', 'sede', 'proceso'])->find($id);
    }
}
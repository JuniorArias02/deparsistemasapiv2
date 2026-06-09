<?php

namespace App\Modules\GestionCompras\Infrastructure\Repositories;

use App\Models\Inventario;
use App\Modules\GestionCompras\Domain\Contracts\InventarioRepositoryInterface;
use App\Modules\GestionCompras\Domain\Entities\InventarioBasico;

class InventarioRepository implements InventarioRepositoryInterface
{
    public function searchByCodigoOrNombre(string $query): array
    {
        $modelos = Inventario::where('codigo', 'LIKE', "%{$query}%")
            ->orWhere('nombre', 'LIKE', "%{$query}%")
            ->limit(20)
            ->get();

        return $modelos->map(function ($modelo) {
            return new InventarioBasico(
                $modelo->id,
                $modelo->codigo,
                $modelo->nombre,
                $modelo->marca,
                $modelo->modelo,
                $modelo->serial
            );
        })->toArray();
    }
}

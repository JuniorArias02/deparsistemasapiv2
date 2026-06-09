<?php

namespace App\Modules\GestionCompras\Domain\Contracts;

interface InventarioRepositoryInterface
{
    /**
     * @param string $query (codigo o nombre)
     * @return \App\Modules\GestionCompras\Domain\Entities\InventarioBasico[]
     */
    public function searchByCodigoOrNombre(string $query): array;
}

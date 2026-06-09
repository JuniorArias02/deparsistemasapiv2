<?php

namespace App\Modules\GestionCompras\Application\UseCases;

use App\Modules\GestionCompras\Domain\Contracts\InventarioRepositoryInterface;

class BuscarInventarioUseCase
{
    private InventarioRepositoryInterface $repository;

    public function __construct(InventarioRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(string $query): array
    {
        if (empty(trim($query))) {
            return [];
        }

        $resultados = $this->repository->searchByCodigoOrNombre($query);
        
        return array_map(function ($inventario) {
            return $inventario->toArray();
        }, $resultados);
    }
}

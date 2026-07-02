<?php

namespace App\Modules\GestionCompras\Application\UseCases\Pedidos;

use App\Modules\GestionCompras\Domain\Contracts\CpPedidoProgramadoRepositoryInterface;

class ObtenerPedidoProgramadoUseCase
{
    private CpPedidoProgramadoRepositoryInterface $repository;

    public function __construct(CpPedidoProgramadoRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(int $id): ?object
    {
        return $this->repository->obtenerPorId($id);
    }
}

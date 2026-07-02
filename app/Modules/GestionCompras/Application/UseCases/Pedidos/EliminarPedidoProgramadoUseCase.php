<?php

namespace App\Modules\GestionCompras\Application\UseCases\Pedidos;

use App\Modules\GestionCompras\Domain\Contracts\CpPedidoProgramadoRepositoryInterface;

class EliminarPedidoProgramadoUseCase
{
    private CpPedidoProgramadoRepositoryInterface $repository;

    public function __construct(CpPedidoProgramadoRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(int $id): bool
    {
        return $this->repository->eliminar($id);
    }
}

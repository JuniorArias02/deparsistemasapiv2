<?php

namespace App\Modules\GestionCompras\Application\UseCases\Pedidos;

use App\Modules\GestionCompras\Domain\Contracts\CpPedidoProgramadoRepositoryInterface;

class ListarPedidosProgramadosUseCase
{
    private CpPedidoProgramadoRepositoryInterface $repository;

    public function __construct(CpPedidoProgramadoRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(array $filtros = []): array
    {
        return $this->repository->listarConFiltros($filtros);
    }
}

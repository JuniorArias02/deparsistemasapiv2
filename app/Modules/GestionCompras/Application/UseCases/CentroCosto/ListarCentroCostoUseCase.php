<?php

namespace App\Modules\GestionCompras\Application\UseCases\CentroCosto;

use App\Modules\GestionCompras\Infrastructure\Repositories\CpCentroCostoRepository;

class ListarCentroCostoUseCase
{
    public function __construct(protected CpCentroCostoRepository $repository) {}

    public function execute()
    {
        return $this->repository->getAll(func_get_args() ? func_get_arg(0) : null);
    }
}
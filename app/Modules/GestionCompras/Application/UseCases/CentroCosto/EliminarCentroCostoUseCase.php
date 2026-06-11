<?php

namespace App\Modules\GestionCompras\Application\UseCases\CentroCosto;

use App\Modules\GestionCompras\Infrastructure\Repositories\CpCentroCostoRepository;

class EliminarCentroCostoUseCase
{
    public function __construct(protected CpCentroCostoRepository $repository) {}

    public function execute($id)
    {
        return $this->repository->delete($id);
    }
}
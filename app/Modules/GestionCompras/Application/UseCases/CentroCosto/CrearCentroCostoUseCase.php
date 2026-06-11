<?php

namespace App\Modules\GestionCompras\Application\UseCases\CentroCosto;

use App\Modules\GestionCompras\Infrastructure\Repositories\CpCentroCostoRepository;

class CrearCentroCostoUseCase
{
    public function __construct(protected CpCentroCostoRepository $repository) {}

    public function execute(array $data)
    {
        return $this->repository->create($data);
    }
}
<?php

namespace App\Modules\GestionCompras\Application\UseCases\CentroCosto;

use App\Modules\GestionCompras\Infrastructure\Repositories\CpCentroCostoRepository;

class ActualizarCentroCostoUseCase
{
    public function __construct(protected CpCentroCostoRepository $repository) {}

    public function execute($id, array $data)
    {
        return $this->repository->update($id, $data);
    }
}
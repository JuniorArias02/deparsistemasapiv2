<?php

namespace App\Modules\GestionCompras\Application\UseCases\Dependencia;

use App\Modules\GestionCompras\Infrastructure\Repositories\CpDependenciaRepository;

class EliminarDependenciaUseCase
{
    public function __construct(protected CpDependenciaRepository $repository) {}

    public function execute($id)
    {
        return $this->repository->delete($id);
    }
}
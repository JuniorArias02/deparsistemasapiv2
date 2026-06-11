<?php

namespace App\Modules\GestionCompras\Application\UseCases\Dependencia;

use App\Modules\GestionCompras\Infrastructure\Repositories\CpDependenciaRepository;

class CrearDependenciaUseCase
{
    public function __construct(protected CpDependenciaRepository $repository) {}

    public function execute(array $data)
    {
        return $this->repository->create($data);
    }
}
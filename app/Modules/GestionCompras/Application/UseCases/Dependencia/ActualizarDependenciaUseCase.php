<?php

namespace App\Modules\GestionCompras\Application\UseCases\Dependencia;

use App\Modules\GestionCompras\Infrastructure\Repositories\CpDependenciaRepository;

class ActualizarDependenciaUseCase
{
    public function __construct(protected CpDependenciaRepository $repository) {}

    public function execute($id, array $data)
    {
        return $this->repository->update($id, $data);
    }
}
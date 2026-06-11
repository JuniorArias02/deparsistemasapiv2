<?php

namespace App\Modules\GestionCompras\Application\UseCases\Dependencia;

use App\Modules\GestionCompras\Infrastructure\Repositories\CpDependenciaRepository;

class ListarDependenciaUseCase
{
    public function __construct(protected CpDependenciaRepository $repository) {}

    public function execute($sede_id = null)
    {
        return $this->repository->getAll(func_get_args() ? func_get_arg(0) : null);
    }
}
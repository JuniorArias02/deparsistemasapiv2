<?php

namespace App\Modules\GestionSistemas\Application\UseCases\ActasEntrega;

use App\Modules\GestionSistemas\Domain\Contracts\ActaEntregaRepositoryInterface;
use App\Modules\GestionSistemas\Domain\Entities\ActaEntrega;

class ObtenerActaEntregaUseCase
{
    private ActaEntregaRepositoryInterface $repository;

    public function __construct(ActaEntregaRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(int $id): ?ActaEntrega
    {
        return $this->repository->findById($id);
    }
}

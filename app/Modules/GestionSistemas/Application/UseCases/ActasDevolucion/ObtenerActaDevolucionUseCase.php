<?php

namespace App\Modules\GestionSistemas\Application\UseCases\ActasDevolucion;

use App\Modules\GestionSistemas\Domain\Entities\ActaDevolucion;
use App\Modules\GestionSistemas\Infrastructure\Repositories\ActaDevolucionRepository;

class ObtenerActaDevolucionUseCase
{
    private ActaDevolucionRepository $repository;

    public function __construct(ActaDevolucionRepository $repository)
    {
        $this->repository = $repository;
    }

    public function execute(int $id): ?ActaDevolucion
    {
        return $this->repository->findById($id);
    }
}

<?php

namespace App\Modules\GestionSistemas\Application\UseCases\EquiposComputo;

use App\Modules\GestionSistemas\Domain\Contracts\PcEquipoRepositoryInterface;
use App\Models\PcEquipo;

class ObtenerPcEquipoUseCase
{
    private PcEquipoRepositoryInterface $repository;

    public function __construct(PcEquipoRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(int $id): ?PcEquipo
    {
        return $this->repository->find($id);
    }
}

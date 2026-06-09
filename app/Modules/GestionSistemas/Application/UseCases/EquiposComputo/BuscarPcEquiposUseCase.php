<?php

namespace App\Modules\GestionSistemas\Application\UseCases\EquiposComputo;

use App\Modules\GestionSistemas\Domain\Contracts\PcEquipoRepositoryInterface;

class BuscarPcEquiposUseCase
{
    private PcEquipoRepositoryInterface $repository;

    public function __construct(PcEquipoRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(string $query)
    {
        return $this->repository->buscar($query);
    }
}

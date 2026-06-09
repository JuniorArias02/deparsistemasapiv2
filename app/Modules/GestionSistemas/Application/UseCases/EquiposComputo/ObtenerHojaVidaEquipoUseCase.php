<?php

namespace App\Modules\GestionSistemas\Application\UseCases\EquiposComputo;

use App\Modules\GestionSistemas\Domain\Contracts\PcEquipoRepositoryInterface;

class ObtenerHojaVidaEquipoUseCase
{
    private PcEquipoRepositoryInterface $repository;

    public function __construct(PcEquipoRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(int $id): ?array
    {
        return $this->repository->getHojaVidaCompleta($id);
    }
}

<?php

namespace App\Modules\GestionSistemas\Application\UseCases\MantenimientoEquipos;

use App\Modules\GestionSistemas\Domain\Contracts\PcMantenimientoRepositoryInterface;
use Exception;

class ObtenerMantenimientoEquipoUseCase
{
    private PcMantenimientoRepositoryInterface $repository;

    public function __construct(PcMantenimientoRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(int $id)
    {
        $mantenimiento = $this->repository->find($id);

        if (!$mantenimiento) {
            throw new Exception('Mantenimiento no encontrado', 404);
        }

        return $mantenimiento;
    }
}

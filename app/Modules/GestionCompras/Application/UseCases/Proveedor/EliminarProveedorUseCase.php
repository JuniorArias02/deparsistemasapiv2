<?php

namespace App\Modules\GestionCompras\Application\UseCases\Proveedor;

use App\Modules\GestionCompras\Infrastructure\Repositories\CpProveedorRepository;

class EliminarProveedorUseCase
{
    public function __construct(protected CpProveedorRepository $repository) {}

    public function execute($id)
    {
        return $this->repository->delete($id);
    }
}
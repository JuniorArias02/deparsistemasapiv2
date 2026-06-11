<?php

namespace App\Modules\GestionCompras\Application\UseCases\Proveedor;

use App\Modules\GestionCompras\Infrastructure\Repositories\CpProveedorRepository;

class ListarProveedorUseCase
{
    public function __construct(protected CpProveedorRepository $repository) {}

    public function execute()
    {
        return $this->repository->getAll(func_get_args() ? func_get_arg(0) : null);
    }
}
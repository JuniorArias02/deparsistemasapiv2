<?php

namespace App\Modules\GestionCompras\Application\UseCases\Proveedor;

use App\Modules\GestionCompras\Infrastructure\Repositories\CpProveedorRepository;

class CrearProveedorUseCase
{
    public function __construct(protected CpProveedorRepository $repository) {}

    public function execute(array $data)
    {
        return $this->repository->create($data);
    }
}
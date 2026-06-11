<?php

namespace App\Modules\GestionCompras\Application\UseCases\Proveedor;

use App\Modules\GestionCompras\Infrastructure\Repositories\CpProveedorRepository;

class ActualizarProveedorUseCase
{
    public function __construct(protected CpProveedorRepository $repository) {}

    public function execute($id, array $data)
    {
        return $this->repository->update($id, $data);
    }
}
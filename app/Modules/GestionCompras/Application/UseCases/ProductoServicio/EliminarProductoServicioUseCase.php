<?php

namespace App\Modules\GestionCompras\Application\UseCases\ProductoServicio;

use App\Modules\GestionCompras\Infrastructure\Repositories\CpProductoServicioRepository;

class EliminarProductoServicioUseCase
{
    public function __construct(protected CpProductoServicioRepository $repository) {}

    public function execute($id)
    {
        return $this->repository->delete($id);
    }
}
<?php

namespace App\Modules\GestionCompras\Application\UseCases\Producto;

use App\Modules\GestionCompras\Infrastructure\Repositories\CpProductoRepository;

class EliminarProductoUseCase
{
    public function __construct(protected CpProductoRepository $repository) {}

    public function execute($id)
    {
        return $this->repository->delete($id);
    }
}
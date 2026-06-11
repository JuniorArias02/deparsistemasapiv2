<?php

namespace App\Modules\GestionCompras\Application\UseCases\ProductoServicio;

use App\Modules\GestionCompras\Infrastructure\Repositories\CpProductoServicioRepository;

class ListarProductoServicioUseCase
{
    public function __construct(protected CpProductoServicioRepository $repository) {}

    public function execute()
    {
        return $this->repository->getAll(func_get_args() ? func_get_arg(0) : null);
    }
}
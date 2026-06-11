<?php

namespace App\Modules\GestionCompras\Application\UseCases\Producto;

use App\Modules\GestionCompras\Infrastructure\Repositories\CpProductoRepository;

class ListarProductoUseCase
{
    public function __construct(protected CpProductoRepository $repository) {}

    public function execute()
    {
        return $this->repository->getAll(func_get_args() ? func_get_arg(0) : null);
    }
}
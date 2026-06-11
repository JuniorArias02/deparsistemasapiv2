<?php

namespace App\Modules\GestionCompras\Application\UseCases\Producto;

use App\Modules\GestionCompras\Infrastructure\Repositories\CpProductoRepository;

class CrearProductoUseCase
{
    public function __construct(protected CpProductoRepository $repository) {}

    public function execute(array $data)
    {
        return $this->repository->create($data);
    }
}
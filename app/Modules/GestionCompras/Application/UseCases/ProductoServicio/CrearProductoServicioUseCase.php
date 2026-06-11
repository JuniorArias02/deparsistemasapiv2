<?php

namespace App\Modules\GestionCompras\Application\UseCases\ProductoServicio;

use App\Modules\GestionCompras\Infrastructure\Repositories\CpProductoServicioRepository;

class CrearProductoServicioUseCase
{
    public function __construct(protected CpProductoServicioRepository $repository) {}

    public function execute(array $data)
    {
        return $this->repository->create($data);
    }
}
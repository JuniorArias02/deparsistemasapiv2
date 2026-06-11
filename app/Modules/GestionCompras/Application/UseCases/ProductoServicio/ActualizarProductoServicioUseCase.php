<?php

namespace App\Modules\GestionCompras\Application\UseCases\ProductoServicio;

use App\Modules\GestionCompras\Infrastructure\Repositories\CpProductoServicioRepository;

class ActualizarProductoServicioUseCase
{
    public function __construct(protected CpProductoServicioRepository $repository) {}

    public function execute($id, array $data)
    {
        return $this->repository->update($id, $data);
    }
}
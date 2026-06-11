<?php

namespace App\Modules\GestionCompras\Application\UseCases\Producto;

use App\Modules\GestionCompras\Infrastructure\Repositories\CpProductoRepository;

class ActualizarProductoUseCase
{
    public function __construct(protected CpProductoRepository $repository) {}

    public function execute($id, array $data)
    {
        return $this->repository->update($id, $data);
    }
}
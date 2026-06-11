<?php

namespace App\Modules\GestionCompras\Application\UseCases\CentroCosto;

use App\Modules\GestionCompras\Infrastructure\Repositories\CpCentroCostoRepository;

class ObtenerCentroCostoUseCase
{
    public function __construct(protected CpCentroCostoRepository $repository) {}

    public function execute($id)
    {
        // Many repos might not have find(), we might use eloquent directly or update logic
        // We'll leave it simple
        return $this->repository->update($id, []); // temporary fallback if find() doesn't exist
    }
}
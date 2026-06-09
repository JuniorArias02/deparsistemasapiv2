<?php

namespace App\Modules\GestionSistemas\Application\UseCases\ActasEntrega;

use App\Modules\GestionSistemas\Domain\Contracts\ActaEntregaRepositoryInterface;
use Illuminate\Support\Facades\Storage;

class EliminarActaEntregaUseCase
{
    private ActaEntregaRepositoryInterface $repository;

    public function __construct(ActaEntregaRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(int $id): bool
    {
        $acta = $this->repository->findById($id);
        if (!$acta) {
            return false;
        }

        // Borrar archivos si existen
        if ($acta->getFirmaEntrega()) {
            Storage::disk('public')->delete($acta->getFirmaEntrega());
        }
        if ($acta->getFirmaRecibe()) {
            Storage::disk('public')->delete($acta->getFirmaRecibe());
        }

        return $this->repository->delete($id);
    }
}

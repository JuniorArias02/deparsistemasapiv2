<?php

namespace App\Modules\GestionSistemas\Application\UseCases\ActasDevolucion;

use App\Modules\GestionSistemas\Infrastructure\Repositories\ActaDevolucionRepository;
use Illuminate\Support\Facades\Storage;

class EliminarActaDevolucionUseCase
{
    private ActaDevolucionRepository $repository;

    public function __construct(ActaDevolucionRepository $repository)
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

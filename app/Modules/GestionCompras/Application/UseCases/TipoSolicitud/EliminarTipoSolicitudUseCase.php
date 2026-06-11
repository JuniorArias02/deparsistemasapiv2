<?php

namespace App\Modules\GestionCompras\Application\UseCases\TipoSolicitud;

use App\Modules\GestionCompras\Infrastructure\Repositories\CpTipoSolicitudRepository;

class EliminarTipoSolicitudUseCase
{
    public function __construct(protected CpTipoSolicitudRepository $repository) {}

    public function execute($id)
    {
        return $this->repository->delete($id);
    }
}
<?php

namespace App\Modules\GestionCompras\Application\UseCases\TipoSolicitud;

use App\Modules\GestionCompras\Infrastructure\Repositories\CpTipoSolicitudRepository;

class ListarTipoSolicitudUseCase
{
    public function __construct(protected CpTipoSolicitudRepository $repository) {}

    public function execute()
    {
        return $this->repository->getAll(func_get_args() ? func_get_arg(0) : null);
    }
}
<?php

namespace App\Modules\GestionCompras\Application\UseCases\TipoSolicitud;

use App\Modules\GestionCompras\Infrastructure\Repositories\CpTipoSolicitudRepository;

class CrearTipoSolicitudUseCase
{
    public function __construct(protected CpTipoSolicitudRepository $repository) {}

    public function execute(array $data)
    {
        return $this->repository->create($data);
    }
}
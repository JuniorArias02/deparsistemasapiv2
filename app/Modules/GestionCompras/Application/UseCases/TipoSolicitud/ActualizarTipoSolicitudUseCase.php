<?php

namespace App\Modules\GestionCompras\Application\UseCases\TipoSolicitud;

use App\Modules\GestionCompras\Infrastructure\Repositories\CpTipoSolicitudRepository;

class ActualizarTipoSolicitudUseCase
{
    public function __construct(protected CpTipoSolicitudRepository $repository) {}

    public function execute($id, array $data)
    {
        return $this->repository->update($id, $data);
    }
}
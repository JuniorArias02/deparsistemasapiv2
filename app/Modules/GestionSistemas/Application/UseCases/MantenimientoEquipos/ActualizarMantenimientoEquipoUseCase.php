<?php

namespace App\Modules\GestionSistemas\Application\UseCases\MantenimientoEquipos;

use App\Modules\GestionSistemas\Domain\Contracts\PcMantenimientoRepositoryInterface;
use App\Services\PcMantenimientoFirmaService;

class ActualizarMantenimientoEquipoUseCase
{
    private PcMantenimientoRepositoryInterface $repository;
    private PcMantenimientoFirmaService $firmaService;

    public function __construct(PcMantenimientoRepositoryInterface $repository, PcMantenimientoFirmaService $firmaService)
    {
        $this->repository = $repository;
        $this->firmaService = $firmaService;
    }

    public function execute(int $id, array $data)
    {
        // Procesar Firmas si vienen en la data de actualización
        if (isset($data['firma_personal_cargo'])) {
            $data['firma_personal_cargo'] = $this->firmaService->saveBase64Signature($data['firma_personal_cargo']);
        }
        
        if (isset($data['firma_sistemas'])) {
            $data['firma_sistemas'] = $this->firmaService->saveBase64Signature($data['firma_sistemas']);
        }

        return $this->repository->update($id, $data);
    }
}

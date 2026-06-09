<?php

namespace App\Modules\GestionSistemas\Domain\Contracts;

use App\Modules\GestionSistemas\Domain\Entities\ActaEntrega;

interface ActaEntregaRepositoryInterface
{
    public function save(ActaEntrega $actaEntrega): ActaEntrega;
    public function findById(int $id): ?ActaEntrega;
    public function findAll(): array;
    public function delete(int $id): bool;
}

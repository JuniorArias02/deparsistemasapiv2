<?php

namespace App\Modules\GestionSistemas\Domain\Contracts;

interface PcEquipoRepositoryInterface
{
    /**
     * Obtiene la hoja de vida completa de un equipo.
     * @param int $id
     * @return array|null
     */
    public function getHojaVidaCompleta(int $id): ?array;
    public function create(array $data): \App\Models\PcEquipo;
    public function find(int $id): ?\App\Models\PcEquipo;
    public function update(int $id, array $data): ?\App\Models\PcEquipo;
    public function delete(int $id): bool;
    public function buscar(string $query);
}
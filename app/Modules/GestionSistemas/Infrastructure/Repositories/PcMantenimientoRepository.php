<?php

namespace App\Modules\GestionSistemas\Infrastructure\Repositories;

use App\Models\PcMantenimiento;
use App\Modules\GestionSistemas\Domain\Contracts\PcMantenimientoRepositoryInterface;

class PcMantenimientoRepository implements PcMantenimientoRepositoryInterface
{
    public function getAll()
    {
        return PcMantenimiento::with(['equipo', 'empresaResponsable', 'creador:id,nombre_completo'])->get();
    }

    public function find(int $id)
    {
        return PcMantenimiento::with(['equipo', 'empresaResponsable', 'creador:id,nombre_completo'])->find($id);
    }

    public function getByEquipo(int $equipoId)
    {
        return PcMantenimiento::with(['empresaResponsable', 'creador:id,nombre_completo'])
            ->where('equipo_id', $equipoId)
            ->get();
    }

    public function create(array $data)
    {
        if (!isset($data['fecha_creacion'])) {
            $data['fecha_creacion'] = now();
        }
        if (!isset($data['estado'])) {
            $data['estado'] = 'pendiente';
        }

        return PcMantenimiento::create($data);
    }

    public function update(int $id, array $data)
    {
        $item = PcMantenimiento::find($id);
        if ($item) {
            $item->update($data);
            return $item;
        }
        return null;
    }

    public function delete(int $id): bool
    {
        $item = PcMantenimiento::find($id);
        if ($item) {
            return $item->delete();
        }
        return false;
    }
}

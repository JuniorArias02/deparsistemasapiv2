<?php

namespace App\Services;

use App\Models\PcMantenimiento;

class PcMantenimientoService
{
    public function getAll()
    {
        return PcMantenimiento::with(['equipo', 'empresaResponsable', 'creador'])->get();
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

    public function find($id)
    {
        return PcMantenimiento::with(['equipo', 'empresaResponsable', 'creador'])->find($id);
    }

    public function getByEquipo($equipoId)
    {
        return PcMantenimiento::with(['empresaResponsable', 'creador'])
            ->where('equipo_id', $equipoId)
            ->get();
    }

    public function update($id, array $data)
    {
        $item = PcMantenimiento::find($id);
        if ($item) {
            $item->update($data);
        }
        return $item;
    }

    public function delete($id)
    {
        $item = PcMantenimiento::find($id);
        if ($item) {
            $item->delete();
            return true;
        }
        return false;
    }
}

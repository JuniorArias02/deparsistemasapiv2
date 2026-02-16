<?php

namespace App\Services;

use App\Models\PcCaracteristicasTecnicas;

class PcCaracteristicasTecnicasService
{
    public function getAll()
    {
        return PcCaracteristicasTecnicas::with(['equipo', 'monitorInventario', 'tecladoInventario', 'mouseInventario'])->get();
    }

    public function create(array $data)
    {
        return PcCaracteristicasTecnicas::create($data);
    }

    public function find($id)
    {
        return PcCaracteristicasTecnicas::with(['equipo', 'monitorInventario', 'tecladoInventario', 'mouseInventario'])->find($id);
    }

    public function getByEquipo($equipoId)
    {
        return PcCaracteristicasTecnicas::where('equipo_id', $equipoId)->first();
    }

    public function update($id, array $data)
    {
        $item = PcCaracteristicasTecnicas::find($id);
        if ($item) {
            $item->update($data);
        }
        return $item;
    }

    public function updateByEquipo($equipoId, array $data)
    {
        $item = PcCaracteristicasTecnicas::where('equipo_id', $equipoId)->first();
        if ($item) {
            $item->update($data);
        } else {
            $data['equipo_id'] = $equipoId;
            $item = PcCaracteristicasTecnicas::create($data);
        }
        return $item;
    }

    public function delete($id)
    {
        $item = PcCaracteristicasTecnicas::find($id);
        if ($item) {
            $item->delete();
            return true;
        }
        return false;
    }
}

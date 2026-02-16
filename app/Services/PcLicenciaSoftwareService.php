<?php

namespace App\Services;

use App\Models\PcLicenciaSoftware;

class PcLicenciaSoftwareService
{
    public function getAll()
    {
        return PcLicenciaSoftware::with('equipo')->get();
    }

    public function create(array $data)
    {
        return PcLicenciaSoftware::create($data);
    }

    public function find($id)
    {
        return PcLicenciaSoftware::with('equipo')->find($id);
    }

    public function getByEquipo($equipoId)
    {
        return PcLicenciaSoftware::where('equipo_id', $equipoId)->first();
    }

    public function update($id, array $data)
    {
        $item = PcLicenciaSoftware::find($id);
        if ($item) {
            $item->update($data);
        }
        return $item;
    }

    public function updateByEquipo($equipoId, array $data)
    {
        $item = PcLicenciaSoftware::where('equipo_id', $equipoId)->first();
        if ($item) {
            $item->update($data);
        } else {
            $data['equipo_id'] = $equipoId;
            $item = PcLicenciaSoftware::create($data);
        }
        return $item;
    }

    public function delete($id)
    {
        $item = PcLicenciaSoftware::find($id);
        if ($item) {
            $item->delete();
            return true;
        }
        return false;
    }
}

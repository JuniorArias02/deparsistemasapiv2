<?php

namespace App\Modules\GestionSistemas\Infrastructure\Repositories;

use App\Models\PcLicenciaSoftware;

class PcLicenciaSoftwareRepository
{
    public function getByEquipo($equipoId)
    {
        return PcLicenciaSoftware::where('equipo_id', $equipoId)->first();
    }

    public function create(array $data)
    {
        return PcLicenciaSoftware::create($data);
    }

    public function update($id, array $data)
    {
        $item = PcLicenciaSoftware::find($id);
        if ($item) {
            $item->update($data);
            return $item;
        }
        return null;
    }

    public function delete($id)
    {
        $item = PcLicenciaSoftware::find($id);
        if ($item) {
            return $item->delete();
        }
        return false;
    }
}

<?php

namespace App\Modules\GestionCompras\Infrastructure\Repositories;

use App\Models\CpTipoSolicitud;

class CpTipoSolicitudRepository
{
    public function getAll()
    {
        return CpTipoSolicitud::all();
    }

    public function create(array $data)
    {
        return CpTipoSolicitud::create($data);
    }

    public function find($id)
    {
        return CpTipoSolicitud::find($id);
    }

    public function update($id, array $data)
    {
        $item = CpTipoSolicitud::find($id);
        if ($item) {
            $item->update($data);
        }
        return $item;
    }

    public function delete($id)
    {
        $item = CpTipoSolicitud::find($id);
        if ($item) {
            $item->delete();
            return true;
        }
        return false;
    }
}

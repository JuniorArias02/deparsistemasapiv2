<?php

namespace App\Services;

use App\Models\CpTipoSolicitud;

class CpTipoSolicitudService
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
        $tipoSolicitud = CpTipoSolicitud::find($id);
        if ($tipoSolicitud) {
            $tipoSolicitud->update($data);
        }
        return $tipoSolicitud;
    }

    public function delete($id)
    {
        $tipoSolicitud = CpTipoSolicitud::find($id);
        if ($tipoSolicitud) {
            $tipoSolicitud->delete();
            return true;
        }
        return false;
    }
}

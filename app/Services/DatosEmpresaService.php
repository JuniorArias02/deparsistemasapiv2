<?php

namespace App\Services;

use App\Models\DatosEmpresa;

class DatosEmpresaService
{
    public function getAll()
    {
        return DatosEmpresa::all();
    }

    public function create(array $data)
    {
        return DatosEmpresa::create($data);
    }

    public function find($id)
    {
        return DatosEmpresa::find($id);
    }

    public function update($id, array $data)
    {
        $item = DatosEmpresa::find($id);
        if ($item) {
            $item->update($data);
        }
        return $item;
    }

    public function delete($id)
    {
        $item = DatosEmpresa::find($id);
        if ($item) {
            $item->delete();
            return true;
        }
        return false;
    }
}

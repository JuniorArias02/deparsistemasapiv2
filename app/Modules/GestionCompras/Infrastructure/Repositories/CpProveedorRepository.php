<?php

namespace App\Modules\GestionCompras\Infrastructure\Repositories;

use App\Models\CpProveedor;

class CpProveedorRepository
{
    public function getAll()
    {
        return CpProveedor::all();
    }

    public function create(array $data)
    {
        return CpProveedor::create($data);
    }

    public function find($id)
    {
        return CpProveedor::find($id);
    }

    public function update($id, array $data)
    {
        $item = CpProveedor::find($id);
        if ($item) {
            $item->update($data);
        }
        return $item;
    }

    public function delete($id)
    {
        $item = CpProveedor::find($id);
        if ($item) {
            $item->delete();
            return true;
        }
        return false;
    }
}

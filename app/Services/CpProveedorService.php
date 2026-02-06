<?php

namespace App\Services;

use App\Models\CpProveedor;

class CpProveedorService
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
        $proveedor = CpProveedor::find($id);
        if ($proveedor) {
            $proveedor->update($data);
        }
        return $proveedor;
    }

    public function delete($id)
    {
        $proveedor = CpProveedor::find($id);
        if ($proveedor) {
            $proveedor->delete();
            return true;
        }
        return false;
    }
}

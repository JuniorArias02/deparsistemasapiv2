<?php

namespace App\Services;

use App\Models\CpProducto;

class CpProductoService
{
    public function getAll()
    {
        return CpProducto::all();
    }

    public function create(array $data)
    {
        return CpProducto::create($data);
    }

    public function find($id)
    {
        return CpProducto::find($id);
    }

    public function update($id, array $data)
    {
        $producto = CpProducto::find($id);
        if ($producto) {
            $producto->update($data);
        }
        return $producto;
    }

    public function delete($id)
    {
        $producto = CpProducto::find($id);
        if ($producto) {
            $producto->delete();
            return true;
        }
        return false;
    }
}

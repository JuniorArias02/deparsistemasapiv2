<?php

namespace App\Services;

use App\Models\CpProductoServicio;

class CpProductoServicioService
{
    public function getAll()
    {
        return CpProductoServicio::all();
    }

    public function create(array $data)
    {
        return CpProductoServicio::create($data);
    }

    public function find($id)
    {
        return CpProductoServicio::find($id);
    }

    public function update($id, array $data)
    {
        $item = CpProductoServicio::find($id);
        if ($item) {
            $item->update($data);
        }
        return $item;
    }

    public function delete($id)
    {
        $item = CpProductoServicio::find($id);
        if ($item) {
            $item->delete();
            return true;
        }
        return false;
    }
}

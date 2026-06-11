<?php

namespace App\Modules\GestionCompras\Infrastructure\Repositories;

use App\Models\CpProducto;

class CpProductoRepository
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
        $item = CpProducto::find($id);
        if ($item) {
            $item->update($data);
        }
        return $item;
    }

    public function delete($id)
    {
        $item = CpProducto::find($id);
        if ($item) {
            $item->delete();
            return true;
        }
        return false;
    }
}

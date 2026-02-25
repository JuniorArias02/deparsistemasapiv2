<?php

namespace App\Services;

use App\Models\CpProductoServicio;

class CpProductoServicioService
{
    public function getAll($search = null, $perPage = 15)
    {
        $query = CpProductoServicio::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('codigo_producto', 'like', "%{$search}%")
                    ->orWhere('nombre', 'like', "%{$search}%");
            });
        }

        return $query->paginate($perPage);
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

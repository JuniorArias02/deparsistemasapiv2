<?php

namespace App\Modules\GestionCompras\Infrastructure\Repositories;

use App\Models\CpCentroCosto;

class CpCentroCostoRepository
{
    public function getAll()
    {
        return CpCentroCosto::all();
    }

    public function create(array $data)
    {
        return CpCentroCosto::create($data);
    }

    public function find($id)
    {
        return CpCentroCosto::find($id);
    }

    public function update($id, array $data)
    {
        $item = CpCentroCosto::find($id);
        if ($item) {
            $item->update($data);
        }
        return $item;
    }

    public function delete($id)
    {
        $item = CpCentroCosto::find($id);
        if ($item) {
            $item->delete();
            return true;
        }
        return false;
    }
}

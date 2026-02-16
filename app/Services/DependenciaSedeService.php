<?php

namespace App\Services;

use App\Models\DependenciaSede;

class DependenciaSedeService
{
    public function getAll($sede_id = null)
    {
        $query = DependenciaSede::with('sede');
        if ($sede_id) {
            $query->where('sede_id', $sede_id);
        }
        return $query->get();
    }

    public function create(array $data)
    {
        return DependenciaSede::create($data);
    }

    public function find($id)
    {
        return DependenciaSede::with('sede')->find($id);
    }

    public function update($id, array $data)
    {
        $item = DependenciaSede::find($id);
        if ($item) {
            $item->update($data);
        }
        return $item;
    }

    public function delete($id)
    {
        $item = DependenciaSede::find($id);
        if ($item) {
            $item->delete();
            return true;
        }
        return false;
    }
}

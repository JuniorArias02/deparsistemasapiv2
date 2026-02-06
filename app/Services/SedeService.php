<?php

namespace App\Services;

use App\Models\Sede;

class SedeService
{
    public function getAll()
    {
        return Sede::all();
    }

    public function create(array $data)
    {
        return Sede::create($data);
    }

    public function find($id)
    {
        return Sede::find($id);
    }

    public function update($id, array $data)
    {
        $sede = Sede::find($id);
        if ($sede) {
            $sede->update($data);
        }
        return $sede;
    }

    public function delete($id)
    {
        $sede = Sede::find($id);
        if ($sede) {
            $sede->delete();
            return true;
        }
        return false;
    }
}

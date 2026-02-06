<?php

namespace App\Services;

use App\Models\PCargo;

class PCargoService
{
    public function getAll()
    {
        return PCargo::all();
    }

    public function create(array $data)
    {
        return PCargo::create($data);
    }

    public function find($id)
    {
        return PCargo::find($id);
    }

    public function update($id, array $data)
    {
        $cargo = PCargo::find($id);
        if ($cargo) {
            $cargo->update($data);
        }
        return $cargo;
    }

    public function delete($id)
    {
        $cargo = PCargo::find($id);
        if ($cargo) {
            $cargo->delete();
            return true;
        }
        return false;
    }
}

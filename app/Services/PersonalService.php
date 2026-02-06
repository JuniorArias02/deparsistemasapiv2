<?php

namespace App\Services;

use App\Models\Personal;

class PersonalService
{
    public function getAll()
    {
        return Personal::with('cargo')->get();
    }

    public function create(array $data)
    {
        return Personal::create($data);
    }

    public function find($id)
    {
        return Personal::with('cargo')->find($id);
    }

    public function update($id, array $data)
    {
        $personal = Personal::find($id);
        if ($personal) {
            $personal->update($data);
        }
        return $personal;
    }

    public function delete($id)
    {
        $personal = Personal::find($id);
        if ($personal) {
            $personal->delete();
            return true;
        }
        return false;
    }
}

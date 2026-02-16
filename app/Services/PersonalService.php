<?php

namespace App\Services;

use App\Models\Personal;

class PersonalService
{
    public function getAll($search = null)
    {
        $query = Personal::with('cargo');
        if ($search) {
            $query->where('cedula', 'like', "%{$search}%")
                  ->orWhere('nombre', 'like', "%{$search}%");
        }
        return $query->limit(20)->get();
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

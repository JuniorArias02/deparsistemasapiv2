<?php

namespace App\Services;

use App\Models\PcEquipo;

class PcEquipoService
{
    public function getAll($search = null)
    {
        $query = PcEquipo::with(['sede', 'area', 'responsable', 'creador']);

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('serial', 'like', "%{$search}%")
                  ->orWhere('marca', 'like', "%{$search}%")
                  ->orWhere('modelo', 'like', "%{$search}%")
                  ->orWhere('numero_inventario', 'like', "%{$search}%");
            });
        }

        return $query->get();
    }

    public function create(array $data)
    {
        
        if (!isset($data['propiedad'])) {
            $data['propiedad'] = 'empresa';
        }

        return PcEquipo::create($data);
    }

    public function find($id)
    {
        return PcEquipo::with(['sede', 'area', 'responsable', 'creador'])->find($id);
    }

    public function update($id, array $data)
    {
        $equipo = PcEquipo::find($id);
        if ($equipo) {
            $equipo->update($data);
        }
        return $equipo;
    }

    public function delete($id)
    {
        $equipo = PcEquipo::find($id);
        if ($equipo) {
            $equipo->delete();
            return true;
        }
        return false;
    }
}

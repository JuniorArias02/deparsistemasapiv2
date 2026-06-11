<?php

namespace App\Modules\GestionCompras\Infrastructure\Repositories;

use App\Models\CpDependencia;

class CpDependenciaRepository
{
    public function getAll(?int $sedeId = null)
    {
        $query = CpDependencia::with('sede');
        if ($sedeId) {
            $query->where('sede_id', $sedeId);
        }
        return $query->get();
    }

    public function create(array $data)
    {
        return CpDependencia::create($data);
    }

    public function find($id)
    {
        return CpDependencia::find($id);
    }

    public function update($id, array $data)
    {
        $item = CpDependencia::find($id);
        if ($item) {
            $item->update($data);
        }
        return $item;
    }

    public function delete($id)
    {
        $item = CpDependencia::find($id);
        if ($item) {
            $item->delete();
            return true;
        }
        return false;
    }
}

<?php

namespace App\Services;

use App\Models\CpDependencia;
use Illuminate\Database\Eloquent\Collection;

class CpDependenciaService
{
    public function getAll(?int $sedeId = null): Collection
    {
        $query = CpDependencia::with('sede');
        
        if ($sedeId) {
            $query->where('sede_id', $sedeId);
        }

        return $query->get();
    }

    public function create(array $data): CpDependencia
    {
        return CpDependencia::create($data);
    }

    public function update(int $id, array $data): CpDependencia
    {
        $dependencia = CpDependencia::findOrFail($id);
        $dependencia->update($data);
        return $dependencia;
    }

    public function delete(int $id): bool
    {
        $dependencia = CpDependencia::findOrFail($id);
        return $dependencia->delete();
    }
}

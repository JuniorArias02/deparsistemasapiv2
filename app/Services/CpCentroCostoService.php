<?php

namespace App\Services;

use App\Models\CpCentroCosto;
use Illuminate\Database\Eloquent\Collection;

class CpCentroCostoService
{
    public function getAll(): Collection
    {
        return CpCentroCosto::all();
    }

    public function create(array $data): CpCentroCosto
    {
        return CpCentroCosto::create($data);
    }

    public function update(int $id, array $data): CpCentroCosto
    {
        $centroCosto = CpCentroCosto::findOrFail($id);
        $centroCosto->update($data);
        return $centroCosto;
    }

    public function delete(int $id): bool
    {
        $centroCosto = CpCentroCosto::findOrFail($id);
        return $centroCosto->delete();
    }
}

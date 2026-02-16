<?php

namespace App\Services;

use App\Models\Area;
use Illuminate\Database\Eloquent\Collection;

class AreaService
{
    public function getAll(): Collection
    {
        return Area::with('sede')->get();
    }

    public function create(array $data): Area
    {
        return Area::create($data);
    }

    public function update(int $id, array $data): Area
    {
        $area = Area::findOrFail($id);
        $area->update($data);
        return $area;
    }

    public function delete(int $id): bool
    {
        $area = Area::findOrFail($id);
        return $area->delete();
    }
}

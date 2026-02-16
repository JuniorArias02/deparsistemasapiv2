<?php

namespace App\Services;

use App\Models\PcConfigCronograma;
use Illuminate\Database\Eloquent\Collection;

class PcConfigCronogramaService
{
    public function getAll(): Collection
    {
        return PcConfigCronograma::all();
    }

    public function find(int $id): ?PcConfigCronograma
    {
        return PcConfigCronograma::find($id);
    }

    public function create(array $data): PcConfigCronograma
    {
        return PcConfigCronograma::create($data);
    }

    public function update(int $id, array $data): ?PcConfigCronograma
    {
        $item = $this->find($id);
        if (!$item) {
            return null;
        }
        $item->update($data);
        return $item;
    }

    public function delete(int $id): bool
    {
        $item = $this->find($id);
        if (!$item) {
            return false;
        }
        return $item->delete();
    }
}

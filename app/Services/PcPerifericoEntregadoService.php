<?php

namespace App\Services;

use App\Models\PcPerifericoEntregado;

class PcPerifericoEntregadoService
{
    public function getAll()
    {
        return PcPerifericoEntregado::with(['entrega', 'inventario'])->get();
    }

    public function create(array $data)
    {
        return PcPerifericoEntregado::create($data);
    }

    public function find($id)
    {
        return PcPerifericoEntregado::with(['entrega', 'inventario'])->find($id);
    }

    public function getByEntrega($entregaId)
    {
        return PcPerifericoEntregado::with('inventario')->where('entrega_id', $entregaId)->get();
    }

    public function update($id, array $data)
    {
        $item = PcPerifericoEntregado::find($id);
        if ($item) {
            $item->update($data);
        }
        return $item;
    }

    public function delete($id)
    {
        $item = PcPerifericoEntregado::find($id);
        if ($item) {
            $item->delete();
            return true;
        }
        return false;
    }
}

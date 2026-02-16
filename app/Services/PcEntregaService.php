<?php

namespace App\Services;

use App\Models\PcEntrega;

class PcEntregaService
{
    public function getAll()
    {
        return PcEntrega::with(['equipo', 'funcionario'])->orderBy('id', 'desc')->get();
    }

    public function create(array $data)
    {
        if (!isset($data['estado'])) {
            $data['estado'] = 'entregado';
        }
        return PcEntrega::create($data);
    }

    public function find($id)
    {
        return PcEntrega::with(['equipo', 'funcionario', 'perifericos.inventario'])->find($id);
    }

    public function getByEquipo($equipoId)
    {
        return PcEntrega::where('equipo_id', $equipoId)->get();
    }
    
    public function getByFuncionario($funcionarioId)
    {
        return PcEntrega::where('funcionario_id', $funcionarioId)->get();
    }

    public function update($id, array $data)
    {
        $item = PcEntrega::find($id);
        if ($item) {
            $item->update($data);
        }
        return $item;
    }

    public function delete($id)
    {
        $item = PcEntrega::find($id);
        if ($item) {
            $item->delete();
            return true;
        }
        return false;
    }
}

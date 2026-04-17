<?php

namespace App\Services;

use App\Models\PcEntrega;

class PcEntregaService
{
    public function getAll()
    {
        return PcEntrega::with(['equipo', 'funcionario'])->orderBy('id', 'desc')->get();
    }

    /**
     * Search for deliveries that have not been returned yet.
     */
    public function search($query)
    {
        return PcEntrega::with(['equipo', 'funcionario'])
            ->where('estado', 'entregado')
            ->where(function($q) use ($query) {
                $q->whereHas('equipo', function($eq) use ($query) {
                    $eq->where('serial', 'like', "%{$query}%")
                       ->orWhere('nombre_equipo', 'like', "%{$query}%")
                       ->orWhere('numero_inventario', 'like', "%{$query}%");
                })
                ->orWhereHas('funcionario', function($per) use ($query) {
                    $per->where('nombre', 'like', "%{$query}%");
                });
            })
            ->limit(10)
            ->get();
    }

    public function create(array $data)
    {
        // Si el equipo ya estaba entregado a otro funcionario, lo marcamos como devuelto automáticamente
        PcEntrega::where('equipo_id', $data['equipo_id'])
            ->where('estado', 'entregado')
            ->update([
                'estado' => 'devuelto',
                'devuelto' => now()
            ]);

        $data['estado'] = 'entregado';
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

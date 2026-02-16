<?php

namespace App\Services;

use App\Models\PcDevuelto;
use App\Models\PcEntrega;

class PcDevueltoService
{
    public function getAll()
    {
        return PcDevuelto::with(['entrega.equipo', 'entrega.funcionario'])->get();
    }

    public function create(array $data)
    {
        if (!isset($data['fecha_devolucion'])) {
            $data['fecha_devolucion'] = now();
        }
        
        $devuelto = PcDevuelto::create($data);

        // Update the related delivery status if needed
        if ($devuelto->entrega_id) {
            $entrega = PcEntrega::find($devuelto->entrega_id);
            if ($entrega) {
                $entrega->update([
                    'estado' => 'devuelto',
                    'devuelto' => now()
                ]);
            }
        }

        return $devuelto;
    }

    public function find($id)
    {
        return PcDevuelto::with(['entrega.equipo', 'entrega.funcionario'])->find($id);
    }

    public function update($id, array $data)
    {
        $item = PcDevuelto::find($id);
        if ($item) {
            $item->update($data);
        }
        return $item;
    }

    public function delete($id)
    {
        $item = PcDevuelto::find($id);
        if ($item) {
            $item->delete();
            return true;
        }
        return false;
    }
}

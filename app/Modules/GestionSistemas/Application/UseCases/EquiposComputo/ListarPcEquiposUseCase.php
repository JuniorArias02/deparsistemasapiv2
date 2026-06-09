<?php

namespace App\Modules\GestionSistemas\Application\UseCases\EquiposComputo;

use App\Models\PcEquipo;

class ListarPcEquiposUseCase
{
    public function execute(?string $search = null, ?int $sedeId = null)
    {
        $query = PcEquipo::with(['sede', 'area', 'responsable', 'creador']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('serial', 'like', "%{$search}%")
                    ->orWhere('marca', 'like', "%{$search}%")
                    ->orWhere('modelo', 'like', "%{$search}%")
                    ->orWhere('numero_inventario', 'like', "%{$search}%");
            });
        }

        if ($sedeId) {
            $query->where('sede_id', $sedeId);
        }

        return $query->orderBy('id', 'desc')->get();
    }
}

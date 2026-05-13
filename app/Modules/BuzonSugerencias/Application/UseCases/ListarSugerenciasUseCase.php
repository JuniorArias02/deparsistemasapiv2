<?php

namespace App\Modules\BuzonSugerencias\Application\UseCases;

use App\Modules\BuzonSugerencias\Infrastructure\Persistence\BuzonSugerencia;
use App\Modules\BuzonSugerencias\Infrastructure\Persistence\EstadoTicket;

class ListarSugerenciasUseCase
{
    public function execute(array $filters = [])
    {
        $query = BuzonSugerencia::with(['estado', 'creador', 'asignado']);

        if (isset($filters['creado_por'])) {
            $query->where('creado_por', $filters['creado_por']);
        }

        if (isset($filters['asignado_a'])) {
            $query->where('asignado_a', $filters['asignado_a']);
        }

        if (isset($filters['pendientes']) && $filters['pendientes']) {
            $estadoCerrado = EstadoTicket::whereIn('nombre', ['Cerrado', 'Resuelto'])->pluck('id');
            $query->whereNotIn('estado_id', $estadoCerrado);
        }

        return $query->orderBy('fecha_creacion', 'desc')->get();
    }
}

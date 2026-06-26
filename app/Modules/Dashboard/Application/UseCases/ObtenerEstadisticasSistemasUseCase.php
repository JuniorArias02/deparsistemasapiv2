<?php

namespace App\Modules\Dashboard\Application\UseCases;

use App\Models\PcEquipo;
use App\Models\PcEntrega;
use App\Models\PcMantenimiento;
use App\Models\PcDevuelto;

class ObtenerEstadisticasSistemasUseCase
{
    public function execute(): array
    {
        return [
            'total_equipos' => PcEquipo::count(),
            'equipos_disponibles' => PcEquipo::where('estado', 'disponible')->count(),
            'equipos_asignados' => PcEquipo::where('estado', 'asignado')->count(),
            'equipos_mantenimiento' => PcEquipo::where('estado', 'mantenimiento')->count(),
            'total_entregas' => PcEntrega::count(),
            'total_devoluciones' => PcDevuelto::count(),
            'total_mantenimientos' => PcMantenimiento::count(),
            'ultimas_entregas' => PcEntrega::with(['equipo', 'funcionario'])->latest('fecha_entrega')->take(5)->get(),
        ];
    }
}

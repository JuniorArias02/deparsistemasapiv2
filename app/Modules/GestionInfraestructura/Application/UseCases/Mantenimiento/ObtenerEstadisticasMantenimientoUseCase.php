<?php
namespace App\Modules\GestionInfraestructura\Application\UseCases\Mantenimiento;

use App\Models\Mantenimiento;
use App\Models\AgendaMantenimiento;
use Illuminate\Support\Facades\DB;

class ObtenerEstadisticasMantenimientoUseCase
{
    public function execute()
    {
        $topCreators = Mantenimiento::join('usuarios', 'mantenimientos.creado_por', '=', 'usuarios.id')
            ->where('usuarios.estado', 1)
            ->select('mantenimientos.creado_por', DB::raw('count(mantenimientos.id) as total'))
            ->with('creador:id,nombre_completo')
            ->groupBy('mantenimientos.creado_por')
            ->orderBy('total', 'desc')
            ->get();

        $bySede = Mantenimiento::join('usuarios', 'mantenimientos.creado_por', '=', 'usuarios.id')
            ->where('usuarios.estado', 1)
            ->select('mantenimientos.sede_id', DB::raw('count(mantenimientos.id) as total'))
            ->with('sede:id,nombre')
            ->groupBy('mantenimientos.sede_id')
            ->get();

        $reviewStatus = Mantenimiento::join('usuarios', 'mantenimientos.creado_por', '=', 'usuarios.id')
            ->where('usuarios.estado', 1)
            ->select('mantenimientos.esta_revisado', DB::raw('count(mantenimientos.id) as total'))
            ->groupBy('mantenimientos.esta_revisado')
            ->get()
            ->map(function ($item) {
                return [
                    'label' => $item->esta_revisado ? 'Revisados' : 'Pendientes',
                    'total' => $item->total,
                    'value' => (bool)$item->esta_revisado
                ];
            });

        $technicianWorkload = AgendaMantenimiento::join('usuarios', 'agenda_mantenimientos.tecnico_id', '=', 'usuarios.id')
            ->where('usuarios.estado', 1)
            ->select('agenda_mantenimientos.tecnico_id', DB::raw('count(agenda_mantenimientos.id) as total'))
            ->with('tecnico:id,nombre_completo')
            ->groupBy('agenda_mantenimientos.tecnico_id')
            ->orderBy('total', 'desc')
            ->get();

        $monthlyTrends = Mantenimiento::join('usuarios', 'mantenimientos.creado_por', '=', 'usuarios.id')
            ->where('usuarios.estado', 1)
            ->select(
                DB::raw("DATE_FORMAT(mantenimientos.fecha_creacion, '%Y-%m') as mes"),
                DB::raw('count(mantenimientos.id) as total')
            )
            ->groupBy('mes')
            ->orderBy('mes')
            ->get();

        $totalMantenimientos = Mantenimiento::join('usuarios', 'mantenimientos.creado_por', '=', 'usuarios.id')
            ->where('usuarios.estado', 1)
            ->count();

        $totalPendientes = Mantenimiento::join('usuarios', 'mantenimientos.creado_por', '=', 'usuarios.id')
            ->where('usuarios.estado', 1)
            ->where('mantenimientos.esta_revisado', false)
            ->count();

        $totalAgendados = AgendaMantenimiento::join('usuarios', 'agenda_mantenimientos.tecnico_id', '=', 'usuarios.id')
            ->where('usuarios.estado', 1)
            ->count();

        return [
            'summary' => [
                'total_mantenimientos' => $totalMantenimientos,
                'total_pendientes' => $totalPendientes,
                'total_agendados' => $totalAgendados,
            ],
            'top_creators' => $topCreators,
            'by_sede' => $bySede,
            'review_status' => $reviewStatus,
            'technician_workload' => $technicianWorkload,
            'monthly_trends' => $monthlyTrends,
        ];
    }
}

<?php
namespace App\Modules\GestionInfraestructura\Application\UseCases\Mantenimiento;

use App\Models\Mantenimiento;

class ExportarMantenimientosExcelUseCase
{
    public function execute($fechaInicio, $fechaFin, $user, $permissionService, $export)
    {
        $query = Mantenimiento::with(['sede', 'coordinador', 'revisador', 'creador', 'agendas.tecnico']);

        if (!$permissionService->check($user, 'mantenimiento.listar_todos')) {
            $query->where('creado_por', $user->id);
        }

        if ($fechaInicio) {
            $query->whereDate('fecha_creacion', '>=', $fechaInicio);
        }
        if ($fechaFin) {
            $query->whereDate('fecha_creacion', '<=', $fechaFin);
        }

        $maintenances = $query->orderBy('fecha_creacion', 'desc')->get();

        return $export->generate($maintenances, $user);
    }
}

<?php
namespace App\Modules\GestionInfraestructura\Application\UseCases\Mantenimiento;

use App\Models\Mantenimiento;

class ObtenerMantenimientosPorTecnicoUseCase
{
    protected $relations = ['sede', 'coordinador', 'revisador', 'creador'];

    public function execute($userId)
    {
        return Mantenimiento::with($this->relations)
            ->where('creado_por', $userId)
            ->orderBy('fecha_creacion', 'desc')
            ->get();
    }
}
<?php
namespace App\Modules\GestionInfraestructura\Application\UseCases\Mantenimiento;

use App\Models\Mantenimiento;

class ObtenerMantenimientosPorCoordinadorUseCase
{
    protected $relations = ['sede', 'coordinador', 'revisador', 'creador'];

    public function execute($userId)
    {
        return Mantenimiento::with($this->relations)
            ->where('coordinador_id', $userId)
            ->orderBy('fecha_creacion', 'desc')
            ->get();
    }
}
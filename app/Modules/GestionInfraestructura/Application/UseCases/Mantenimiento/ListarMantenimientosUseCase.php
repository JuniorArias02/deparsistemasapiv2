<?php
namespace App\Modules\GestionInfraestructura\Application\UseCases\Mantenimiento;

use App\Models\Mantenimiento;

class ListarMantenimientosUseCase
{
    protected $relations = ['sede', 'coordinador', 'revisador', 'creador'];

    public function execute()
    {
        return Mantenimiento::with($this->relations)->get();
    }
}
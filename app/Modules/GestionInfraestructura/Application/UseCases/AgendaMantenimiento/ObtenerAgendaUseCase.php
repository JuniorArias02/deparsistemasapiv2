<?php
namespace App\Modules\GestionInfraestructura\Application\UseCases\AgendaMantenimiento;

use App\Models\AgendaMantenimiento;

class ObtenerAgendaUseCase
{
    protected $relations = ['mantenimiento', 'sede', 'tecnico', 'coordinador'];

    public function execute($id)
    {
        return AgendaMantenimiento::with($this->relations)->find($id);
    }
}
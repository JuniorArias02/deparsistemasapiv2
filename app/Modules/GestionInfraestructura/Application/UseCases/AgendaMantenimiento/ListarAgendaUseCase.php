<?php
namespace App\Modules\GestionInfraestructura\Application\UseCases\AgendaMantenimiento;

use App\Models\AgendaMantenimiento;

class ListarAgendaUseCase
{
    protected $relations = ['mantenimiento', 'sede', 'tecnico', 'coordinador'];

    public function execute()
    {
        return AgendaMantenimiento::with($this->relations)->get();
    }
}
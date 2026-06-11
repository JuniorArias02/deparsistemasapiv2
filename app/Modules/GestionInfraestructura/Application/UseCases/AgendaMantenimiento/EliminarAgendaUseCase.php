<?php
namespace App\Modules\GestionInfraestructura\Application\UseCases\AgendaMantenimiento;

use App\Models\AgendaMantenimiento;

class EliminarAgendaUseCase
{
    public function execute($id)
    {
        $agenda = AgendaMantenimiento::find($id);
        if ($agenda) {
            $agenda->delete();
            return true;
        }
        return false;
    }
}
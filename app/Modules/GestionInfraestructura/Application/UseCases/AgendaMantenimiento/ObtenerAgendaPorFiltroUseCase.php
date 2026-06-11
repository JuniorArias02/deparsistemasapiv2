<?php
namespace App\Modules\GestionInfraestructura\Application\UseCases\AgendaMantenimiento;

use App\Models\AgendaMantenimiento;

class ObtenerAgendaPorFiltroUseCase
{
    protected $relations = ['mantenimiento', 'sede', 'tecnico', 'coordinador'];

    public function execute($filtro, $valor)
    {
        return AgendaMantenimiento::with($this->relations)
            ->where($filtro, $valor)
            ->get();
    }
}
<?php
namespace App\Modules\GestionInfraestructura\Application\UseCases\Mantenimiento;

use App\Models\Mantenimiento;

class EliminarMantenimientoUseCase
{
    public function execute($id)
    {
        $mantenimiento = Mantenimiento::find($id);
        if ($mantenimiento) {
            $mantenimiento->delete();
            return true;
        }
        return false;
    }
}
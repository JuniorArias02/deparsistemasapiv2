<?php
namespace App\Modules\GestionInfraestructura\Application\UseCases\Mantenimiento;

use App\Models\Mantenimiento;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class MarcarMantenimientoRevisadoUseCase
{
    protected $relations = ['sede', 'coordinador', 'revisador', 'creador'];

    public function execute($id)
    {
        $mantenimiento = Mantenimiento::find($id);
        if (!$mantenimiento) {
            return null;
        }

        $user = Auth::guard('api')->user();
        $mantenimiento->update([
            'esta_revisado' => true,
            'revisado_por' => $user ? $user->id : null,
            'fecha_revisado' => Carbon::now(),
            'fecha_ultima_actualizacion' => Carbon::now(),
        ]);

        return $mantenimiento->load($this->relations);
    }
}
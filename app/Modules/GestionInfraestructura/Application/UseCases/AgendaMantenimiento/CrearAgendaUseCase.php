<?php
namespace App\Modules\GestionInfraestructura\Application\UseCases\AgendaMantenimiento;

use App\Models\AgendaMantenimiento;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class CrearAgendaUseCase
{
    public function __construct(protected ValidarDisponibilidadUseCase $validador) {}

    public function execute(array $data)
    {
        $errorHorario = $this->validador->execute($data['fecha_inicio'], $data['fecha_fin']);
        if ($errorHorario) {
            throw new \Exception($errorHorario);
        }

        $user = Auth::guard('api')->user();
        if (!isset($data['tecnico_id'])) {
            $data['tecnico_id'] = $user ? $user->id : null;
        }

        if (!$this->validador->isTecnicoDisponible($data['tecnico_id'], $data['fecha_inicio'], $data['fecha_fin'])) {
            throw new \Exception('El técnico no está disponible en este horario.');
        }

        $data['coordinador_id'] = $user ? $user->id : null;
        $data['fecha_creacion'] = Carbon::now();

        return AgendaMantenimiento::create($data);
    }
}
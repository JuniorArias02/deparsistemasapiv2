<?php
namespace App\Modules\GestionInfraestructura\Application\UseCases\AgendaMantenimiento;

use App\Models\AgendaMantenimiento;

class ActualizarAgendaUseCase
{
    public function __construct(protected ValidarDisponibilidadUseCase $validador) {}

    protected $relations = ['mantenimiento', 'sede', 'tecnico', 'coordinador'];

    public function execute($id, array $data)
    {
        $agenda = AgendaMantenimiento::find($id);
        if (!$agenda) return null;

        if (isset($data['asignado_a'])) {
            $data['tecnico_id'] = $data['asignado_a'];
        }

        $fechaInicio = $data['fecha_inicio'] ?? $agenda->fecha_inicio;
        $fechaFin    = $data['fecha_fin'] ?? $agenda->fecha_fin;
        $tecnicoId   = $data['tecnico_id'] ?? $agenda->tecnico_id;

        $errorHorario = $this->validador->execute($fechaInicio, $fechaFin);
        if ($errorHorario) {
            throw new \Exception($errorHorario);
        }

        if (!$this->validador->isTecnicoDisponible($tecnicoId, $fechaInicio, $fechaFin, $id)) {
            throw new \Exception('El técnico no está disponible en este horario modificado.');
        }

        $agenda->update($data);
        return $agenda->fresh($this->relations);
    }
}
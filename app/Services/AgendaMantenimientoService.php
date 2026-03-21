<?php

namespace App\Services;

use App\Models\AgendaMantenimiento;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class AgendaMantenimientoService
{
    protected $relations = ['mantenimiento', 'sede', 'tecnico', 'coordinador'];

    public function getAll()
    {
        return AgendaMantenimiento::with($this->relations)->get();
    }

    public function create(array $data)
    {
        $user = Auth::guard('api')->user();
        if (!isset($data['tecnico_id'])) {
            $data['tecnico_id'] = $user ? $user->id : null;
        }

        // Validar disponibilidad
        if (!$this->isTecnicoDisponible($data['tecnico_id'], $data['fecha_inicio'], $data['fecha_fin'])) {
            throw new \Exception('el técnico tiene una agenda para esa fecha o día o hora');
        }

        $data['coordinador_id'] = $user ? $user->id : null;
        $data['fecha_creacion'] = Carbon::now();

        return AgendaMantenimiento::create($data);
    }

    public function isTecnicoDisponible($tecnicoId, $fechaInicio, $fechaFin, $excludeId = null)
    {
        $query = AgendaMantenimiento::where('tecnico_id', $tecnicoId)
            ->where(function ($q) use ($fechaInicio, $fechaFin) {
                $q->where('fecha_inicio', '<', $fechaFin)
                  ->where('fecha_fin', '>', $fechaInicio);
            });

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return !$query->exists();
    }

    public function find($id)
    {
        return AgendaMantenimiento::with($this->relations)->find($id);
    }

    public function update($id, array $data)
    {
        $agenda = AgendaMantenimiento::find($id);
        if (!$agenda) return null;

        $tecnicoId = $data['tecnico_id'] ?? $data['asignado_a'] ?? $agenda->tecnico_id;
        $fechaInicio = $data['fecha_inicio'] ?? $agenda->fecha_inicio;
        $fechaFin = $data['fecha_fin'] ?? $agenda->fecha_fin;

        if (!$this->isTecnicoDisponible($tecnicoId, $fechaInicio, $fechaFin, $id)) {
            throw new \Exception('el técnico tiene una agenda para esa fecha o día o hora');
        }

        if (isset($data['asignado_a'])) {
            $data['tecnico_id'] = $data['asignado_a'];
        }

        $agenda->update($data);
        return $agenda;
    }

    public function delete($id)
    {
        $agenda = AgendaMantenimiento::find($id);
        if ($agenda) {
            $agenda->delete();
            return true;
        }
        return false;
    }

    public function getByMantenimiento($mantenimientoId)
    {
        return AgendaMantenimiento::with($this->relations)
            ->where('mantenimiento_id', $mantenimientoId)
            ->get();
    }

    public function getByTecnico($userId)
    {
        return AgendaMantenimiento::with($this->relations)
            ->where('tecnico_id', $userId)
            ->get();
    }

    public function getByCoordinador($userId)
    {
        return AgendaMantenimiento::with($this->relations)
            ->where('coordinador_id', $userId)
            ->get();
    }
}

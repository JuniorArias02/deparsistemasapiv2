<?php

namespace App\Services;

use App\Models\AgendaMantenimiento;
use App\Models\Usuario;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class AgendaMantenimientoService
{
    protected $relations = ['mantenimiento', 'sede', 'tecnico', 'coordinador'];

    // ─────────────────────────────────────────────────────────────────────────
    // CRUD
    // ─────────────────────────────────────────────────────────────────────────

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

        // Disponibilidad ya se valida en el controller antes de llegar aquí
        $data['coordinador_id'] = $user ? $user->id : null;
        $data['fecha_creacion'] = Carbon::now();

        return AgendaMantenimiento::create($data);
    }

    public function find($id)
    {
        return AgendaMantenimiento::with($this->relations)->find($id);
    }

    public function update($id, array $data)
    {
        $agenda = AgendaMantenimiento::find($id);
        if (!$agenda) return null;

        if (isset($data['asignado_a'])) {
            $data['tecnico_id'] = $data['asignado_a'];
        }

        $agenda->update($data);
        return $agenda->fresh($this->relations);
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

    // ─────────────────────────────────────────────────────────────────────────
    // DISPONIBILIDAD
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * True = el técnico está libre en ese rango.
     * Overlap check: inicio_existente < fin_nuevo  &&  fin_existente > inicio_nuevo
     *
     * @param int|null $excludeId  ID de agenda a ignorar (útil al editar)
     */
    public function isTecnicoDisponible($tecnicoId, $fechaInicio, $fechaFin, $excludeId = null): bool
    {
        // Normalizar a UTC para que el string sea comparable con las fechas en BD
        $inicio = Carbon::parse($fechaInicio)->utc()->toDateTimeString();
        $fin    = Carbon::parse($fechaFin)->utc()->toDateTimeString();

        $query = AgendaMantenimiento::where('tecnico_id', $tecnicoId)
            ->where(function ($q) use ($inicio, $fin) {
                // Overlap: existe solapamiento si inicio_bd < fin_nuevo Y fin_bd > inicio_nuevo
                $q->where('fecha_inicio', '<', $fin)
                  ->where('fecha_fin',    '>', $inicio);
            });

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return !$query->exists();
    }

    /**
     * Devuelve los IDs de técnicos que ya tienen agenda en el rango dado.
     * Se usa desde el frontend para deshabilitar selección.
     *
     * @param int|null $excludeId  ID de agenda a ignorar (edición)
     */
    public function getTecnicosOcupados(string $fechaInicio, string $fechaFin, ?int $excludeId = null): array
    {
        // Normalizar a UTC
        $inicio = Carbon::parse($fechaInicio)->utc()->toDateTimeString();
        $fin    = Carbon::parse($fechaFin)->utc()->toDateTimeString();

        $query = AgendaMantenimiento::select('tecnico_id')
            ->where(function ($q) use ($inicio, $fin) {
                $q->where('fecha_inicio', '<', $fin)
                  ->where('fecha_fin',    '>', $inicio);
            });

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->pluck('tecnico_id')->unique()->values()->toArray();
    }

    /**
     * Validación completa de un rango horario para crear o editar.
     * Retorna null si todo está bien, o un mensaje de error legible.
     */
    public function validarRangoHorario(string $fechaInicio, string $fechaFin): ?string
    {
        $inicio = Carbon::parse($fechaInicio);
        $fin    = Carbon::parse($fechaFin);

        // La fecha de inicio no puede estar en el pasado (margen 5 min)
        if ($inicio->isPast() && $inicio->diffInMinutes(Carbon::now()) > 5) {
            return 'La fecha de inicio no puede ser en el pasado.';
        }

        // Fin debe ser estrictamente mayor que inicio
        if ($fin->lessThanOrEqualTo($inicio)) {
            return 'La fecha de fin debe ser posterior a la fecha de inicio.';
        }

        // Duración mínima: 15 minutos
        if ($inicio->diffInMinutes($fin) < 15) {
            return 'La duración mínima de un agendamiento es de 15 minutos.';
        }

        // Duración máxima: 24 horas
        if ($inicio->diffInHours($fin) > 24) {
            return 'La duración máxima de un agendamiento es de 24 horas.';
        }

        return null;
    }
}

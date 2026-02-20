<?php

namespace App\Services;

use App\Models\AgendaMantenimiento;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class AgendaMantenimientoService
{
    protected $relations = ['mantenimiento', 'sede', 'creador', 'agendador'];

    public function getAll()
    {
        return AgendaMantenimiento::with($this->relations)->get();
    }

    public function create(array $data)
    {
        $user = Auth::guard('api')->user();
        $data['creado_por'] = $user ? $user->id : null;
        $data['agendado_por'] = $user ? $user->id : null;
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
        if ($agenda) {
            $agenda->update($data);
        }
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
}

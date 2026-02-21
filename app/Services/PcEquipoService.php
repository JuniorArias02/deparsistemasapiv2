<?php

namespace App\Services;

use App\Models\PcEquipo;

class PcEquipoService
{
    public function getAll($search = null)
    {
        $query = PcEquipo::with(['sede', 'area', 'responsable', 'creador']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('serial', 'like', "%{$search}%")
                    ->orWhere('marca', 'like', "%{$search}%")
                    ->orWhere('modelo', 'like', "%{$search}%")
                    ->orWhere('numero_inventario', 'like', "%{$search}%");
            });
        }

        return $query->get();
    }

    public function create(array $data)
    {

        if (!isset($data['propiedad'])) {
            $data['propiedad'] = 'empresa';
        }

        return PcEquipo::create($data);
    }

    public function find($id)
    {
        return PcEquipo::with(['sede', 'area', 'responsable', 'creador'])->find($id);
    }

    public function update($id, array $data)
    {
        $equipo = PcEquipo::find($id);
        if ($equipo) {
            $equipo->update($data);
        }
        return $equipo;
    }

    public function delete($id)
    {
        $equipo = PcEquipo::find($id);
        if ($equipo) {
            $equipo->delete();
            return true;
        }
        return false;
    }

    public function hojaDeVida($id)
    {
        $equipo = PcEquipo::with([
            'sede',
            'area', 
            'responsable',
            'creador',
            'caracteristicasTecnicas',
            'licenciasSoftware',
            'entregas' => function ($q) {
                $q->with(['funcionario'])->orderBy('fecha_entrega', 'desc');
            },
            'mantenimientos' => function ($q) {
                $q->with(['empresaResponsable', 'creador'])->orderBy('fecha', 'desc');
            },
        ])->find($id);

        if (!$equipo) {
            return null;
        }

        // Load devuelto for each entrega
        $equipo->entregas->each(function ($entrega) {
            $entrega->load('perifericos');
            $devuelto = \App\Models\PcDevuelto::where('entrega_id', $entrega->id)->first();
            $entrega->setAttribute('devolucion', $devuelto);
        });

        // Maintenance countdown
        $config = \App\Models\PcConfigCronograma::first();
        $diasCumplimiento = $config?->dias_cumplimiento ?? 180;

        $ultimoMantenimiento = $equipo->mantenimientos->first();
        $diasRestantes = null;
        $fechaProximoManto = null;

        if ($ultimoMantenimiento && $ultimoMantenimiento->fecha) {
            $fechaUltimo = \Carbon\Carbon::parse($ultimoMantenimiento->fecha);
            $fechaProximoManto = $fechaUltimo->copy()->addDays($diasCumplimiento);
            $diasRestantes = now()->diffInDays($fechaProximoManto, false);
        }

        return [
            'equipo' => $equipo,
            'mantenimiento_config' => [
                'dias_cumplimiento' => $diasCumplimiento,
                'dias_restantes' => $diasRestantes !== null ? (int) $diasRestantes : null,
                'fecha_proximo_mantenimiento' => $fechaProximoManto?->toDateString(),
                'fecha_ultimo_mantenimiento' => $ultimoMantenimiento?->fecha,
            ],
        ];
    }
}

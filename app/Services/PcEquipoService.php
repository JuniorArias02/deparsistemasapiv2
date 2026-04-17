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

    public function buscar($search)
    {
        return PcEquipo::where('nombre_equipo', 'like', "%{$search}%")
            ->orWhere('numero_inventario', 'like', "%{$search}%")
            ->orWhere('serial', 'like', "%{$search}%")
            ->limit(5)
            ->get(['id', 'nombre_equipo', 'numero_inventario', 'serial', 'marca', 'modelo']);
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

        $mantoInfo = $this->calculateMaintenanceInfo($equipo);

        return [
            'equipo' => $equipo,
            'mantenimiento_config' => $mantoInfo,
        ];
    }

    /**
     * Calcula la información de mantenimiento basado en el cronograma y el historial.
     */
    public function calculateMaintenanceInfo($equipo)
    {
        $config = \Illuminate\Support\Facades\DB::table('pc_config_cronograma')->first();
        
        // Calcular días de cumplimiento
        $diasCumplimiento = 180; // Default 6 meses
        if ($config) {
            if ($config->dias_cumplimiento) {
                $diasCumplimiento = $config->dias_cumplimiento;
            } elseif ($config->meses_cumplimiento) {
                $diasCumplimiento = $config->meses_cumplimiento * 30;
            }
        }

        // Determinar fecha base (último mantenimiento o fecha de ingreso)
        $ultimoMantenimiento = $equipo->mantenimientos->first(); // Ya vienen ordenados desc en hojaDeVida
        
        $fechaBase = null;
        $tipoBase = 'ninguna';

        if ($ultimoMantenimiento && $ultimoMantenimiento->fecha) {
            $fechaBase = \Carbon\Carbon::parse($ultimoMantenimiento->fecha);
            $tipoBase = 'ultimo_mantenimiento';
        } elseif ($equipo->fecha_ingreso) {
            $fechaBase = \Carbon\Carbon::parse($equipo->fecha_ingreso);
            $tipoBase = 'fecha_ingreso';
        }

        $diasRestantes = null;
        $fechaProximoManto = null;

        if ($fechaBase) {
            $fechaProximoManto = $fechaBase->copy()->addDays($diasCumplimiento);
            $diasRestantes = (int) now()->diffInDays($fechaProximoManto, false);
        }

        return [
            'dias_cumplimiento' => $diasCumplimiento,
            'dias_restantes' => $diasRestantes,
            'fecha_proximo_mantenimiento' => $fechaProximoManto?->toDateString(),
            'fecha_ultimo_mantenimiento' => $ultimoMantenimiento?->fecha,
            'base_calculo' => $tipoBase
        ];
    }
}

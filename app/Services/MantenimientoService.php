<?php

namespace App\Services;

use App\Models\Mantenimiento;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class MantenimientoService
{
    protected $relations = ['sede', 'receptor', 'revisador', 'creador'];

    public function getAll()
    {
        return Mantenimiento::with($this->relations)->get();
    }

    public function create(array $data)
    {
        $user = Auth::guard('api')->user();
        $data['creado_por'] = $user ? $user->id : null;
        $data['fecha_creacion'] = Carbon::now();
        $data['fecha_ultima_actualizacion'] = Carbon::now();

        return Mantenimiento::create($data);
    }

    public function find($id)
    {
        return Mantenimiento::with($this->relations)->find($id);
    }

    public function update($id, array $data)
    {
        $mantenimiento = Mantenimiento::find($id);
        if ($mantenimiento) {
            $data['fecha_ultima_actualizacion'] = Carbon::now();
            $mantenimiento->update($data);
        }
        return $mantenimiento;
    }

    public function delete($id)
    {
        $mantenimiento = Mantenimiento::find($id);
        if ($mantenimiento) {
            $mantenimiento->delete();
            return true;
        }
        return false;
    }

    public function getByReceptor($userId)
    {
        return Mantenimiento::with($this->relations)
            ->where('nombre_receptor', $userId)
            ->orderBy('fecha_creacion', 'desc')
            ->get();
    }

    public function marcarRevisado($id)
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

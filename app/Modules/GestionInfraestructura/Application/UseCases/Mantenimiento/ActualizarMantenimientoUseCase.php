<?php
namespace App\Modules\GestionInfraestructura\Application\UseCases\Mantenimiento;

use App\Models\Mantenimiento;
use Carbon\Carbon;

class ActualizarMantenimientoUseCase
{
    public function execute($id, array $data, $request = null)
    {
        $mantenimiento = Mantenimiento::find($id);
        if ($mantenimiento) {
            $data['fecha_ultima_actualizacion'] = Carbon::now();

            if ($request) {
                $paths = [];
                foreach (['imagen', 'imagen2'] as $field) {
                    if ($request->hasFile($field)) {
                        $file = $request->file($field);
                        $filename = md5($file->getClientOriginalName() . time() . uniqid()) . '.' . $file->getClientOriginalExtension();
                        $path = $file->storeAs('mantenimientos', $filename, 'public');
                        $paths[] = 'storage/' . $path;
                    }
                }
                if (!empty($paths)) {
                    $data['imagen'] = implode(',', $paths);
                }
            }

            $mantenimiento->update($data);
        }
        return $mantenimiento;
    }
}
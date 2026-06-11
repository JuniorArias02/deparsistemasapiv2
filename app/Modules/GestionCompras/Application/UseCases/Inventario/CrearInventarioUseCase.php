<?php

namespace App\Modules\GestionCompras\Application\UseCases\Inventario;

use App\Models\Inventario;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class CrearInventarioUseCase
{
    public function execute(array $data, $file = null)
    {
        $user = auth('api')->user();
        $data['creado_por'] = empty($data['creado_por']) ? ($user ? $user->id : null) : $data['creado_por'];
        $data['fecha_creacion'] = Carbon::now();
        $data['activo'] = '1';
        $data['codigo2'] = '';

        if ($file) {
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('inventarioAdjunto', $filename, 'public');
            $data['soporte_adjunto'] = 'storage/' . $path;
        }

        return Inventario::create($data);
    }
}
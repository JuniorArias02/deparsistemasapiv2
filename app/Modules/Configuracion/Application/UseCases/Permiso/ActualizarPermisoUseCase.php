<?php
namespace App\Modules\Configuracion\Application\UseCases\Permiso;
use App\Models\Permiso;

class ActualizarPermisoUseCase
{
    public function execute($id, array $data)
    {
        $item = Permiso::find($id);
        if ($item) {
            $item->update($data);
        }
        return $item;
    }
}
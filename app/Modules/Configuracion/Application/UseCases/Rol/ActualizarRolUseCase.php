<?php
namespace App\Modules\Configuracion\Application\UseCases\Rol;
use App\Models\Rol;

class ActualizarRolUseCase
{
    public function execute($id, array $data)
    {
        $item = Rol::find($id);
        if ($item) {
            $item->update($data);
        }
        return $item;
    }
}
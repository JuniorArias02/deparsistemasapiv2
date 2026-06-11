<?php
namespace App\Modules\Configuracion\Application\UseCases\Sede;
use App\Models\Sede;

class ActualizarSedeUseCase
{
    public function execute($id, array $data)
    {
        $item = Sede::find($id);
        if ($item) {
            $item->update($data);
        }
        return $item;
    }
}
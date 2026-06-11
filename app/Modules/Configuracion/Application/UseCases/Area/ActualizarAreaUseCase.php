<?php
namespace App\Modules\Configuracion\Application\UseCases\Area;
use App\Models\Area;

class ActualizarAreaUseCase
{
    public function execute($id, array $data)
    {
        $item = Area::find($id);
        if ($item) {
            $item->update($data);
        }
        return $item;
    }
}
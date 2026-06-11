<?php
namespace App\Modules\Configuracion\Application\UseCases\PCargo;
use App\Models\PCargo;

class ActualizarPCargoUseCase
{
    public function execute($id, array $data)
    {
        $item = PCargo::find($id);
        if ($item) {
            $item->update($data);
        }
        return $item;
    }
}
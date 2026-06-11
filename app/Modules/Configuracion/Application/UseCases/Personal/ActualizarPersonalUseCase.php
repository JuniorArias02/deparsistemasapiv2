<?php
namespace App\Modules\Configuracion\Application\UseCases\Personal;
use App\Models\Personal;

class ActualizarPersonalUseCase
{
    public function execute($id, array $data)
    {
        $item = Personal::find($id);
        if ($item) {
            $item->update($data);
        }
        return $item;
    }
}
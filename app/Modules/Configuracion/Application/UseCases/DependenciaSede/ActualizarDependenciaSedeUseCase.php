<?php
namespace App\Modules\Configuracion\Application\UseCases\DependenciaSede;
use App\Models\DependenciaSede;

class ActualizarDependenciaSedeUseCase
{
    public function execute($id, array $data)
    {
        $item = DependenciaSede::find($id);
        if ($item) {
            $item->update($data);
        }
        return $item;
    }
}
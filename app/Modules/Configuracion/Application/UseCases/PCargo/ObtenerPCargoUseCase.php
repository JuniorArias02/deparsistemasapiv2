<?php
namespace App\Modules\Configuracion\Application\UseCases\PCargo;
use App\Models\PCargo;

class ObtenerPCargoUseCase
{
    public function execute($id)
    {
        return PCargo::find($id);
    }
}
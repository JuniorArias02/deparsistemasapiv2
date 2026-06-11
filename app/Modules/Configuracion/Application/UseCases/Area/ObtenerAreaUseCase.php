<?php
namespace App\Modules\Configuracion\Application\UseCases\Area;
use App\Models\Area;

class ObtenerAreaUseCase
{
    public function execute($id)
    {
        return Area::with('sede')->find($id);
    }
}
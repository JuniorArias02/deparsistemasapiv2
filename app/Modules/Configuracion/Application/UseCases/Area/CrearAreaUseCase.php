<?php
namespace App\Modules\Configuracion\Application\UseCases\Area;
use App\Models\Area;

class CrearAreaUseCase
{
    public function execute(array $data)
    {
        return Area::create($data);
    }
}
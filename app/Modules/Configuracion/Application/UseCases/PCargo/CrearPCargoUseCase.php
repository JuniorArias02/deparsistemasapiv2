<?php
namespace App\Modules\Configuracion\Application\UseCases\PCargo;
use App\Models\PCargo;

class CrearPCargoUseCase
{
    public function execute(array $data)
    {
        return PCargo::create($data);
    }
}
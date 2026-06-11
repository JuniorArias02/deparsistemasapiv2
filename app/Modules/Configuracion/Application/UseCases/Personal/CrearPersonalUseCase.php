<?php
namespace App\Modules\Configuracion\Application\UseCases\Personal;
use App\Models\Personal;

class CrearPersonalUseCase
{
    public function execute(array $data)
    {
        return Personal::create($data);
    }
}
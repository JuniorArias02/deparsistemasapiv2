<?php
namespace App\Modules\Configuracion\Application\UseCases\PCargo;
use App\Models\PCargo;

class ListarPCargoUseCase
{
    public function execute()
    {
        return PCargo::all();
    }
}
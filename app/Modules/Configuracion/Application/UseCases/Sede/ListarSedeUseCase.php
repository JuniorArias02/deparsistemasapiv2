<?php
namespace App\Modules\Configuracion\Application\UseCases\Sede;
use App\Models\Sede;

class ListarSedeUseCase
{
    public function execute()
    {
        return Sede::all();
    }
}
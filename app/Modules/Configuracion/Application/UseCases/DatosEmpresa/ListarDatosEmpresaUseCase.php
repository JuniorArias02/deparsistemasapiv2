<?php
namespace App\Modules\Configuracion\Application\UseCases\DatosEmpresa;
use App\Models\DatosEmpresa;

class ListarDatosEmpresaUseCase
{
    public function execute()
    {
        return DatosEmpresa::all();
    }
}
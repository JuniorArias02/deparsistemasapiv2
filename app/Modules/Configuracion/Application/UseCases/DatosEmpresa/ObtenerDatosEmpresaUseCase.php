<?php
namespace App\Modules\Configuracion\Application\UseCases\DatosEmpresa;
use App\Models\DatosEmpresa;

class ObtenerDatosEmpresaUseCase
{
    public function execute($id)
    {
        return DatosEmpresa::find($id);
    }
}
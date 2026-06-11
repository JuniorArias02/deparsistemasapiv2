<?php
namespace App\Modules\Configuracion\Application\UseCases\DatosEmpresa;
use App\Models\DatosEmpresa;

class CrearDatosEmpresaUseCase
{
    public function execute(array $data)
    {
        return DatosEmpresa::create($data);
    }
}
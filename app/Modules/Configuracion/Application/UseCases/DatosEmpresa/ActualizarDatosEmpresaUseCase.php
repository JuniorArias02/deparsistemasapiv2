<?php
namespace App\Modules\Configuracion\Application\UseCases\DatosEmpresa;
use App\Models\DatosEmpresa;

class ActualizarDatosEmpresaUseCase
{
    public function execute($id, array $data)
    {
        $item = DatosEmpresa::find($id);
        if ($item) {
            $item->update($data);
        }
        return $item;
    }
}
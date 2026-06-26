<?php
namespace App\Modules\Configuracion\Application\UseCases\DependenciaSede;
use App\Models\DependenciaSede;

class CrearDependenciaSedeUseCase
{
    public function execute(array $data)
    {
        $dependenciaData = [
            'sede_id' => $data['sede_id'] ?? null,
            'nombre' => $data['nombre'] ?? null,
        ];

        return DependenciaSede::create($dependenciaData);
    }
}
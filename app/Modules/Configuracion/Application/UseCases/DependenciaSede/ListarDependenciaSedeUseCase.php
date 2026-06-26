<?php
namespace App\Modules\Configuracion\Application\UseCases\DependenciaSede;
use App\Models\DependenciaSede;

class ListarDependenciaSedeUseCase
{
    public function execute(array $filters = [])
    {
        $query = DependenciaSede::with('sede');

        if (isset($filters['sede_id'])) {
            $query->where('sede_id', $filters['sede_id']);
        }

        return $query->get();
    }
}
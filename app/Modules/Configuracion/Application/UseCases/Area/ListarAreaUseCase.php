<?php
namespace App\Modules\Configuracion\Application\UseCases\Area;
use App\Models\Area;

class ListarAreaUseCase
{
    public function execute($filters = [])
    {
        $query = Area::with('sede');
        
        if (isset($filters['sede_id'])) {
            $query->where('sede_id', $filters['sede_id']);
        }
        
        return $query->get();
    }
}
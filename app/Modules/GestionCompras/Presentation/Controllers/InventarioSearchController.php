<?php

namespace App\Modules\GestionCompras\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\GestionCompras\Application\UseCases\BuscarInventarioUseCase;
use App\Modules\GestionCompras\Infrastructure\Repositories\InventarioRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class InventarioSearchController extends Controller
{
    private BuscarInventarioUseCase $buscarUseCase;

    public function __construct()
    {
        $repository = new InventarioRepository();
        $this->buscarUseCase = new BuscarInventarioUseCase($repository);
    }

    #[OA\Get(
        path: '/api/gestion-compras/inventario/buscar',
        tags: ['Gestion Compras - Inventario'],
        summary: 'Buscar items de inventario',
        description: 'Busca en el inventario usando código o nombre (útil para autocompletados).',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'q',
                in: 'query',
                required: true,
                description: 'Término de búsqueda (código o nombre)',
                schema: new OA\Schema(type: 'string')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Lista de items encontrados'
            )
        ]
    )]
    public function search(Request $request): JsonResponse
    {
        $query = $request->query('q', '');
        $resultados = $this->buscarUseCase->execute($query);

        return response()->json([
            'success' => true,
            'data' => $resultados
        ]);
    }
}

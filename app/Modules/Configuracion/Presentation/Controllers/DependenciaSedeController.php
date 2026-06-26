<?php
namespace App\Modules\Configuracion\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Configuracion\Application\UseCases\DependenciaSede\ListarDependenciaSedeUseCase;
use App\Modules\Configuracion\Application\UseCases\DependenciaSede\CrearDependenciaSedeUseCase;
use App\Modules\Configuracion\Application\UseCases\DependenciaSede\ObtenerDependenciaSedeUseCase;
use App\Modules\Configuracion\Application\UseCases\DependenciaSede\ActualizarDependenciaSedeUseCase;
use App\Modules\Configuracion\Application\UseCases\DependenciaSede\EliminarDependenciaSedeUseCase;
use App\Services\PermissionService;
use App\Responses\ApiResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class DependenciaSedeController extends Controller
{
    public function __construct(
        protected PermissionService $permissionService,
        protected ListarDependenciaSedeUseCase $listarUseCase,
        protected CrearDependenciaSedeUseCase $crearUseCase,
        protected ObtenerDependenciaSedeUseCase $obtenerUseCase,
        protected ActualizarDependenciaSedeUseCase $actualizarUseCase,
        protected EliminarDependenciaSedeUseCase $eliminarUseCase
    ) {}

    #[OA\Get(
        path: '/api/dependencias-sedes',
        tags: ['Dependencias Sede'],
        summary: 'Listar dependencias por sede',
        description: 'Obtiene la lista de procesos o dependencias correspondientes. Permite filtrar por sede_id.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'sede_id',
                in: 'query',
                required: false,
                description: 'Filtra las dependencias que pertenezcan a esta sede.',
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Lista de Dependencias Sede',
                content: new OA\JsonContent(ref: '#/components/schemas/ApiResponse')
            ),
            new OA\Response(response: 500, description: 'Error del servidor')
        ]
    )]
    public function index(Request $request)
    {
        // $this->permissionService->authorize('dependencia_sede.listar');
        return ApiResponse::success($this->listarUseCase->execute($request->all()), 'Lista de Dependencias Sede');
    }

    public function store(Request $request)
    {
        $this->permissionService->authorize('dependencia_sede.crear');
        try {
            return ApiResponse::success($this->crearUseCase->execute($request->all()), 'DependenciaSede creado', 201);
        } catch (\Exception $e) {
            return ApiResponse::error('Error al crear: ' . $e->getMessage(), 500);
        }
    }

    public function show($id)
    {
        // $this->permissionService->authorize('dependencia_sede.listar');
        $item = $this->obtenerUseCase->execute($id);
        if (!$item) return ApiResponse::error('No encontrado', 404);
        return ApiResponse::success($item, 'Detalle de DependenciaSede');
    }

    public function update(Request $request, $id)
    {
        $this->permissionService->authorize('dependencia_sede.actualizar');
        try {
            $item = $this->actualizarUseCase->execute($id, $request->all());
            if (!$item) return ApiResponse::error('No encontrado', 404);
            return ApiResponse::success($item, 'DependenciaSede actualizado');
        } catch (\Exception $e) {
            return ApiResponse::error('Error al actualizar: ' . $e->getMessage(), 500);
        }
    }

    public function destroy($id)
    {
        $this->permissionService->authorize('dependencia_sede.eliminar');
        if ($this->eliminarUseCase->execute($id)) {
            return ApiResponse::success(null, 'DependenciaSede eliminado');
        }
        return ApiResponse::error('No encontrado', 404);
    }
}
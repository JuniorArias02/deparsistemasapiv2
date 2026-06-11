<?php

namespace App\Modules\GestionCompras\Presentation\Controllers;

use App\Http\Controllers\Controller;

use App\Modules\GestionCompras\Application\UseCases\Dependencia\ListarDependenciaUseCase;
use App\Modules\GestionCompras\Application\UseCases\Dependencia\CrearDependenciaUseCase;
use App\Modules\GestionCompras\Application\UseCases\Dependencia\ObtenerDependenciaUseCase;
use App\Modules\GestionCompras\Application\UseCases\Dependencia\ActualizarDependenciaUseCase;
use App\Modules\GestionCompras\Application\UseCases\Dependencia\EliminarDependenciaUseCase;
use App\Services\PermissionService;
use Illuminate\Http\Request;
use App\Responses\ApiResponse;
use OpenApi\Attributes as OA;
use Illuminate\Support\Facades\Validator;

class CpDependenciaController extends Controller
{
    protected $permissionService;

    public function __construct(
        PermissionService $permissionService,
        protected ListarDependenciaUseCase $listarUseCase,
        protected CrearDependenciaUseCase $crearUseCase,
        protected ObtenerDependenciaUseCase $obtenerUseCase,
        protected ActualizarDependenciaUseCase $actualizarUseCase,
        protected EliminarDependenciaUseCase $eliminarUseCase
    ) {
        $this->permissionService = $permissionService;
    }

    /**
     * Listar dependencias.
     */
    #[OA\Get(
        path: '/api/gestion-compras/cp-dependencias',
        tags: ['CP Dependencias'],
        summary: 'Listar dependencias',
        description: 'Obtiene la lista de dependencias. Requiere permiso cp_dependencia.read.',
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Lista de dependencias', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponse')),
            new OA\Response(response: 403, description: 'Prohibido')
        ]
    )]
    public function index(Request $request)
    {
        // $this->permissionService->authorize('cp_dependencia.read');
        return ApiResponse::success($this->listarUseCase->execute($request->get('sede_id')), 'Lista de dependencias');
    }

    /**
     * Crear dependencia.
     */
    #[OA\Post(
        path: '/api/gestion-compras/cp-dependencias',
        tags: ['CP Dependencias'],
        summary: 'Crear dependencia',
        description: 'Crea una nueva dependencia. Requiere permiso cp_dependencia.create.',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'codigo', type: 'integer', example: 123),
                    new OA\Property(property: 'nombre', type: 'string', example: 'Dirección General'),
                    new OA\Property(property: 'sede_id', type: 'integer', example: 1)
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Dependencia creada', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponse')),
            new OA\Response(response: 403, description: 'Prohibido')
        ]
    )]
    public function store(Request $request)
    {
        $this->permissionService->authorize('cp_dependencia.crear');

        $validated = $request->validate([
            'codigo' => 'sometimes|integer',
            'nombre' => 'required|string|max:160',
            'sede_id' => 'required|exists:sedes,id'
        ]);

        return ApiResponse::created($this->crearUseCase->execute($validated), 'Dependencia creada exitosamente');
    }

    /**
     * Mostrar dependencia.
     */
    #[OA\Get(
        path: '/api/gestion-compras/cp-dependencias/{id}',
        tags: ['CP Dependencias'],
        summary: 'Obtener dependencia',
        description: 'Obtiene los detalles de una dependencia. Requiere permiso cp_dependencia.read.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Detalles de la dependencia', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponse')),
            new OA\Response(response: 404, description: 'Dependencia no encontrada'),
            new OA\Response(response: 403, description: 'Prohibido')
        ]
    )]
    public function show($id)
    {
        return ApiResponse::success($this->obtenerUseCase->execute($id), 'Detalle de dependencia');
    }

    /**
     * Actualizar dependencia.
     */
    #[OA\Put(
        path: '/api/gestion-compras/cp-dependencias/{id}',
        tags: ['CP Dependencias'],
        summary: 'Actualizar dependencia',
        description: 'Actualiza una dependencia existente. Requiere permiso cp_dependencia.update.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'codigo', type: 'integer', example: 124),
                    new OA\Property(property: 'nombre', type: 'string', example: 'Dirección General Actualizada'),
                    new OA\Property(property: 'sede_id', type: 'integer', example: 2)
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Dependencia actualizada', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponse')),
            new OA\Response(response: 404, description: 'Dependencia no encontrada'),
            new OA\Response(response: 403, description: 'Prohibido')
        ]
    )]
    public function update(Request $request, $id)
    {
        $this->permissionService->authorize('cp_dependencia.actualizar');

        $validated = $request->validate([
            'codigo' => 'sometimes|integer',
            'nombre' => 'sometimes|string|max:160',
            'sede_id' => 'sometimes|exists:sedes,id'
        ]);

        return ApiResponse::success($this->actualizarUseCase->execute($id, $validated), 'Dependencia actualizada exitosamente');
    }

    /**
     * Eliminar dependencia.
     */
    #[OA\Delete(
        path: '/api/gestion-compras/cp-dependencias/{id}',
        tags: ['CP Dependencias'],
        summary: 'Eliminar dependencia',
        description: 'Elimina una dependencia. Requiere permiso cp_dependencia.eliminar.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Dependencia eliminada', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponse')),
            new OA\Response(response: 404, description: 'Dependencia no encontrada'),
            new OA\Response(response: 403, description: 'Prohibido')
        ]
    )]
    public function destroy($id)
    {
        $this->permissionService->authorize('cp_dependencia.eliminar');
        $this->eliminarUseCase->execute($id);
        return ApiResponse::success(null, 'Dependencia eliminada exitosamente');
    }
}

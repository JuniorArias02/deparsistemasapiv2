<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Rol;
use App\Services\PermissionService;
use App\Responses\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;

class RolController extends Controller
{
    public function __construct(
        protected PermissionService $permissionService
    ) {}

    /**
     * Listar roles.
     */
    #[OA\Get(
        path: '/api/roles',
        tags: ['Roles'],
        summary: 'Listar roles',
        description: 'Obtiene la lista de roles. Requiere permiso rol.read.',
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Lista de roles', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponse')),
            new OA\Response(response: 403, description: 'Prohibido')
        ]
    )]
    public function index()
    {
        $this->permissionService->authorize('rol.read');
        $roles = Rol::with('permisos')->get();
        return ApiResponse::success($roles, 'Lista de roles');
    }

    /**
     * Crear rol.
     */
    #[OA\Post(
        path: '/api/roles',
        tags: ['Roles'],
        summary: 'Crear rol',
        description: 'Crea un nuevo rol. Requiere permiso rol.create.',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'nombre', type: 'string', example: 'Editor')
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Rol creado', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponse')),
            new OA\Response(response: 403, description: 'Prohibido')
        ]
    )]
    public function store(Request $request)
    {
        $this->permissionService->authorize('rol.create');

        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:60|unique:rol,nombre',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error('Error de validación', 422, $validator->errors());
        }

        $rol = Rol::create($request->all());

        return ApiResponse::success($rol, 'Rol creado exitosamente', 201);
    }

    /**
     * Mostrar rol.
     */
    #[OA\Get(
        path: '/api/roles/{id}',
        tags: ['Roles'],
        summary: 'Obtener rol',
        description: 'Obtiene los detalles de un rol. Requiere permiso rol.read.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Detalles del rol', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponse')),
            new OA\Response(response: 404, description: 'Rol no encontrado'),
            new OA\Response(response: 403, description: 'Prohibido')
        ]
    )]
    public function show($id)
    {
        $this->permissionService->authorize('rol.read');
        $rol = Rol::with('permisos')->find($id);

        if (!$rol) {
            return ApiResponse::error('Rol no encontrado', 404);
        }

        return ApiResponse::success($rol, 'Rol obtenido exitosamente');
    }

    /**
     * Actualizar rol.
     */
    #[OA\Put(
        path: '/api/roles/{id}',
        tags: ['Roles'],
        summary: 'Actualizar rol',
        description: 'Actualiza un rol existente. Requiere permiso rol.update.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'nombre', type: 'string', example: 'Editor Senior')
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Rol actualizado', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponse')),
            new OA\Response(response: 404, description: 'Rol no encontrado'),
            new OA\Response(response: 403, description: 'Prohibido')
        ]
    )]
    public function update(Request $request, $id)
    {
        $this->permissionService->authorize('rol.update');

        $rol = Rol::find($id);
        if (!$rol) {
            return ApiResponse::error('Rol no encontrado', 404);
        }

        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:60|unique:rol,nombre,' . $id,
        ]);

        if ($validator->fails()) {
            return ApiResponse::error('Error de validación', 422, $validator->errors());
        }

        $rol->update($request->all());

        return ApiResponse::success($rol, 'Rol actualizado exitosamente');
    }

    /**
     * Eliminar rol.
     */
    #[OA\Delete(
        path: '/api/roles/{id}',
        tags: ['Roles'],
        summary: 'Eliminar rol',
        description: 'Elimina un rol. Requiere permiso rol.delete.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Rol eliminado', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponse')),
            new OA\Response(response: 404, description: 'Rol no encontrado'),
            new OA\Response(response: 403, description: 'Prohibido')
        ]
    )]
    public function destroy($id)
    {
        $this->permissionService->authorize('rol.delete');

        $rol = Rol::find($id);
        if (!$rol) {
            return ApiResponse::error('Rol no encontrado', 404);
        }
        $rol->delete();

        return ApiResponse::success(null, 'Rol eliminado exitosamente');
    }

    /**
     * Asignar permisos a un rol.
     */
    #[OA\Put(
        path: '/api/roles/{id}/permissions',
        tags: ['Roles'],
        summary: 'Asignar permisos',
        description: 'Sincroniza los permisos de un rol. Requiere permiso rol.assign_permission.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'permisos', type: 'array', items: new OA\Items(type: 'integer'), example: [1, 2, 3])
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Permisos asignados', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponse')),
            new OA\Response(response: 403, description: 'Prohibido')
        ]
    )]
    public function assignPermissions(Request $request, $id)
    {
        $this->permissionService->authorize('rol.assign_permission');

        $rol = Rol::find($id);
        if (!$rol) {
            return ApiResponse::error('Rol no encontrado', 404);
        }

        $validator = Validator::make($request->all(), [
            'permisos' => 'required|array',
            'permisos.*' => 'exists:permisos,id',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error('Error de validación', 422, $validator->errors());
        }

        $rol->permisos()->sync($request->permisos);
        $rol->load('permisos');

        return ApiResponse::success($rol, 'Permisos asignados exitosamente');
    }
}

<?php

namespace App\Http\Controllers;

use App\Services\MantenimientoService;
use App\Services\PermissionService;
use App\Responses\ApiResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class MantenimientoController extends Controller
{
    public function __construct(
        protected MantenimientoService $service,
        protected PermissionService $permissionService
    ) {}

    #[OA\Get(
        path: '/api/mantenimientos',
        tags: ['Mantenimientos'],
        summary: 'Listar mantenimientos',
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Lista de mantenimientos', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponse')),
            new OA\Response(response: 403, description: 'Prohibido')
        ]
    )]
    public function index()
    {
        $this->permissionService->authorize('mantenimiento.listar');

        $mantenimientos = $this->service->getAll();
        return ApiResponse::success($mantenimientos, 'Lista de mantenimientos');
    }

    #[OA\Post(
        path: '/api/mantenimientos',
        tags: ['Mantenimientos'],
        summary: 'Crear mantenimiento',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['titulo'],
                properties: [
                    new OA\Property(property: 'titulo', type: 'string'),
                    new OA\Property(property: 'codigo', type: 'string'),
                    new OA\Property(property: 'modelo', type: 'string'),
                    new OA\Property(property: 'dependencia', type: 'string'),
                    new OA\Property(property: 'sede_id', type: 'integer'),
                    new OA\Property(property: 'nombre_receptor', type: 'integer'),
                    new OA\Property(property: 'descripcion', type: 'string'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Mantenimiento creado', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponse')),
            new OA\Response(response: 403, description: 'Prohibido')
        ]
    )]
    public function store(Request $request)
    {
        $this->permissionService->authorize('mantenimiento.crear');

        $request->validate([
            'titulo' => 'required|string|max:255',
            'codigo' => 'nullable|string|max:100',
            'modelo' => 'nullable|string|max:100',
            'dependencia' => 'nullable|string|max:255',
            'sede_id' => 'nullable|exists:sedes,id',
            'nombre_receptor' => 'nullable|exists:usuarios,id',
            'imagen' => 'nullable|file|image|max:5120',
            'imagen2' => 'nullable|file|image|max:5120',
            'descripcion' => 'nullable|string',
        ]);

        $data = $request->except(['imagen', 'imagen2']);

        // Handle image uploads
        $paths = [];
        foreach (['imagen', 'imagen2'] as $field) {
            if ($request->hasFile($field)) {
                $file = $request->file($field);
                $filename = md5($file->getClientOriginalName() . time() . uniqid()) . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('mantenimientos', $filename, 'public');
                $paths[] = 'storage/' . $path;
            }
        }
        if (!empty($paths)) {
            $data['imagen'] = implode(',', $paths);
        }

        try {
            $mantenimiento = $this->service->create($data);
            return ApiResponse::success($mantenimiento, 'Mantenimiento creado exitosamente', 201);
        } catch (\Exception $e) {
            return ApiResponse::error('Error al crear mantenimiento: ' . $e->getMessage(), 500);
        }
    }

    #[OA\Get(
        path: '/api/mantenimientos/{id}',
        tags: ['Mantenimientos'],
        summary: 'Obtener mantenimiento',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Detalle del mantenimiento', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponse')),
            new OA\Response(response: 404, description: 'No encontrado'),
            new OA\Response(response: 403, description: 'Prohibido')
        ]
    )]
    public function show($id)
    {
        $this->permissionService->authorize('mantenimiento.listar');

        $mantenimiento = $this->service->find($id);
        if (!$mantenimiento) {
            return ApiResponse::error('Mantenimiento no encontrado', 404);
        }

        return ApiResponse::success($mantenimiento, 'Detalle del mantenimiento');
    }

    #[OA\Put(
        path: '/api/mantenimientos/{id}',
        tags: ['Mantenimientos'],
        summary: 'Actualizar mantenimiento',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'titulo', type: 'string'),
                    new OA\Property(property: 'codigo', type: 'string'),
                    new OA\Property(property: 'modelo', type: 'string'),
                    new OA\Property(property: 'dependencia', type: 'string'),
                    new OA\Property(property: 'sede_id', type: 'integer'),
                    new OA\Property(property: 'nombre_receptor', type: 'integer'),
                    new OA\Property(property: 'descripcion', type: 'string'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Actualizado exitosamente', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponse')),
            new OA\Response(response: 404, description: 'No encontrado'),
            new OA\Response(response: 403, description: 'Prohibido')
        ]
    )]
    public function update(Request $request, $id)
    {
        $this->permissionService->authorize('mantenimiento.actualizar');

        $mantenimiento = $this->service->find($id);
        if (!$mantenimiento) {
            return ApiResponse::error('Mantenimiento no encontrado', 404);
        }

        $request->validate([
            'titulo' => 'nullable|string|max:255',
            'codigo' => 'nullable|string|max:100',
            'modelo' => 'nullable|string|max:100',
            'dependencia' => 'nullable|string|max:255',
            'sede_id' => 'nullable|exists:sedes,id',
            'nombre_receptor' => 'nullable|exists:usuarios,id',
            'imagen' => 'nullable|file|image|max:5120',
            'imagen2' => 'nullable|file|image|max:5120',
            'descripcion' => 'nullable|string',
        ]);

        $data = $request->except(['imagen', 'imagen2']);

        // Handle image uploads
        $paths = [];
        foreach (['imagen', 'imagen2'] as $field) {
            if ($request->hasFile($field)) {
                $file = $request->file($field);
                $filename = md5($file->getClientOriginalName() . time() . uniqid()) . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('mantenimientos', $filename, 'public');
                $paths[] = 'storage/' . $path;
            }
        }
        if (!empty($paths)) {
            $data['imagen'] = implode(',', $paths);
        }

        try {
            $updated = $this->service->update($id, $data);
            return ApiResponse::success($updated, 'Mantenimiento actualizado exitosamente');
        } catch (\Exception $e) {
            return ApiResponse::error('Error al actualizar mantenimiento: ' . $e->getMessage(), 500);
        }
    }

    #[OA\Delete(
        path: '/api/mantenimientos/{id}',
        tags: ['Mantenimientos'],
        summary: 'Eliminar mantenimiento',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Eliminado exitosamente', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponse')),
            new OA\Response(response: 404, description: 'No encontrado'),
            new OA\Response(response: 403, description: 'Prohibido')
        ]
    )]
    public function destroy($id)
    {
        $this->permissionService->authorize('mantenimiento.eliminar');

        if ($this->service->delete($id)) {
            return ApiResponse::success(null, 'Mantenimiento eliminado exitosamente');
        }

        return ApiResponse::error('Mantenimiento no encontrado o no se pudo eliminar', 404);
    }

    #[OA\Post(
        path: '/api/mantenimientos/{id}/marcar-revisado',
        tags: ['Mantenimientos'],
        summary: 'Marcar mantenimiento como revisado',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Marcado como revisado', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponse')),
            new OA\Response(response: 404, description: 'No encontrado'),
            new OA\Response(response: 403, description: 'Prohibido')
        ]
    )]
    public function marcarRevisado($id)
    {
        $this->permissionService->authorize('mantenimiento.marcar_revisado');

        $mantenimiento = $this->service->marcarRevisado($id);
        if (!$mantenimiento) {
            return ApiResponse::error('Mantenimiento no encontrado', 404);
        }

        return ApiResponse::success($mantenimiento, 'Mantenimiento marcado como revisado');
    }

    /**
     * Get mantenimientos assigned to the authenticated user (receptor).
     */
    public function misMantenimientos()
    {
        $this->permissionService->authorize("mantenimiento.receptor");
        $user = \Illuminate\Support\Facades\Auth::guard('api')->user();
        $mantenimientos = $this->service->getByReceptor($user->id);
        return ApiResponse::success($mantenimientos, 'Mis mantenimientos asignados');
    }
}

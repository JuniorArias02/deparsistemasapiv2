<?php

namespace App\Http\Controllers;

use App\Services\PcMantenimientoService;
use App\Responses\ApiResponse;
use App\Services\PermissionService;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class PcMantenimientoController extends Controller
{
    public function __construct(
        protected PcMantenimientoService $service,
        protected PermissionService $permissionService
    ) {}

    #[OA\Get(
        path: '/api/pc-mantenimientos',
        tags: ['PcMantenimientos'],
        summary: 'Listar mantenimientos',
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Lista obtenida', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponse')),
            new OA\Response(response: 403, description: 'Prohibido')
        ]
    )]
    public function index()
    {
        $items = $this->service->getAll();
        return ApiResponse::success($items, 'Mantenimientos listados exitosamente');
    }

    #[OA\Post(
        path: '/api/pc-mantenimientos',
        tags: ['PcMantenimientos'],
        summary: 'Crear mantenimiento',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'equipo_id', type: 'integer'),
                    new OA\Property(property: 'tipo_mantenimiento', type: 'string', enum: ['preventivo', 'correctivo']),
                    new OA\Property(property: 'descripcion', type: 'string'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Creado exitosamente', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponse')),
            new OA\Response(response: 403, description: 'Prohibido')
        ]
    )]
    public function store(Request $request)
    {
        $this->permissionService->authorize("");
        $validated = $request->validate([
            'equipo_id' => 'required|integer|exists:pc_equipos,id',
            'tipo_mantenimiento' => 'nullable|in:preventivo,correctivo',
            'descripcion' => 'nullable|string',
            'fecha' => 'nullable|date',
            'empresa_responsable_id' => 'nullable|integer|exists:datos_empresa,id',
            'repuesto' => 'nullable|boolean',
            'cantidad_repuesto' => 'nullable|integer',
            'costo_repuesto' => 'nullable|numeric',
            'nombre_repuesto' => 'nullable|string|max:255',
            'responsable_mantenimiento' => 'nullable|string|max:255',
            'estado' => 'nullable|in:completado,pendiente',
        ]);

        try {
            if (auth()->check()) {
                $validated['creado_por'] = auth()->id();
            }

            $item = $this->service->create($validated);
            return ApiResponse::success($item, 'Mantenimiento creado exitosamente', 201);
        } catch (\Exception $e) {
            return ApiResponse::error('Error al crear mantenimiento: ' . $e->getMessage(), 500);
        }
    }

    #[OA\Get(
        path: '/api/pc-mantenimientos/{id}',
        tags: ['PcMantenimientos'],
        summary: 'Obtener mantenimiento',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Detalle', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponse')),
            new OA\Response(response: 404, description: 'No encontrado'),
            new OA\Response(response: 403, description: 'Prohibido')
        ]
    )]
    public function show($id)
    {
        $item = $this->service->find($id);

        if (!$item) {
            return ApiResponse::error('Mantenimiento no encontrado', 404);
        }

        return ApiResponse::success($item, 'Detalle del mantenimiento');
    }

    #[OA\Get(
        path: '/api/pc-mantenimientos/equipo/{equipo_id}',
        tags: ['PcMantenimientos'],
        summary: 'Listar mantenimientos por equipo',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'equipo_id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Lista obtenida', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponse')),
            new OA\Response(response: 404, description: 'No encontrado')
        ]
    )]
    public function showByEquipo($equipo_id)
    {
        $items = $this->service->getByEquipo($equipo_id);
        return ApiResponse::success($items, 'Historial de mantenimientos del equipo');
    }

    #[OA\Put(
        path: '/api/pc-mantenimientos/{id}',
        tags: ['PcMantenimientos'],
        summary: 'Actualizar mantenimiento',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'descripcion', type: 'string'),
                    new OA\Property(property: 'estado', type: 'string', enum: ['completado', 'pendiente']),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Actualizado', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponse')),
            new OA\Response(response: 404, description: 'No encontrado'),
            new OA\Response(response: 403, description: 'Prohibido')
        ]
    )]
    public function update(Request $request, $id)
    {
        $this->permissionService->authorize("pc_mantenimientos.crud");
        $item = $this->service->find($id);
        if (!$item) {
            return ApiResponse::error('Mantenimiento no encontrado', 404);
        }

        $validated = $request->validate([
            'equipo_id' => 'sometimes|integer|exists:pc_equipos,id',
            'tipo_mantenimiento' => 'nullable|in:preventivo,correctivo',
            'descripcion' => 'nullable|string',
            'fecha' => 'nullable|date',
            'empresa_responsable_id' => 'nullable|integer|exists:datos_empresa,id',
            'repuesto' => 'nullable|boolean',
            'cantidad_repuesto' => 'nullable|integer',
            'costo_repuesto' => 'nullable|numeric',
            'nombre_repuesto' => 'nullable|string|max:255',
            'responsable_mantenimiento' => 'nullable|string|max:255',
            'estado' => 'nullable|in:completado,pendiente',
        ]);

        try {
            $updated = $this->service->update($id, $validated);
            return ApiResponse::success($updated, 'Mantenimiento actualizado exitosamente');
        } catch (\Exception $e) {
            return ApiResponse::error('Error al actualizar mantenimiento: ' . $e->getMessage(), 500);
        }
    }

    #[OA\Delete(
        path: '/api/pc-mantenimientos/{id}',
        tags: ['PcMantenimientos'],
        summary: 'Eliminar mantenimiento',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Eliminado', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponse')),
            new OA\Response(response: 404, description: 'No encontrado'),
            new OA\Response(response: 403, description: 'Prohibido')
        ]
    )]
    public function destroy($id)
    {
        $this->permissionService->authorize("pc_mantenimientos.eliminar");
        if ($this->service->delete($id)) {
            return ApiResponse::success(null, 'Mantenimiento eliminado exitosamente');
        }

        return ApiResponse::error('Mantenimiento no encontrado o no se pudo eliminar', 404);
    }
}

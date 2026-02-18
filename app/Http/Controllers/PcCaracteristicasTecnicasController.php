<?php

namespace App\Http\Controllers;

use App\Services\PcCaracteristicasTecnicasService;
use App\Responses\ApiResponse;
use App\Services\PermissionService;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class PcCaracteristicasTecnicasController extends Controller
{
    public function __construct(
        protected PcCaracteristicasTecnicasService $service,
        protected PermissionService $permissionService
    ) {}

    #[OA\Get(
        path: '/api/pc-caracteristicas-tecnicas',
        tags: ['PcCaracteristicasTecnicas'],
        summary: 'Listar características técnicas',
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Lista obtenida', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponse')),
            new OA\Response(response: 403, description: 'Prohib000000.0ido')
        ]
    )]
    public function index()
    {
        $items = $this->service->getAll();
        return ApiResponse::success($items, 'Características técnicas listadas exitosamente');
    }

    #[OA\Post(
        path: '/api/pc-caracteristicas-tecnicas',
        tags: ['PcCaracteristicasTecnicas'],
        summary: 'Crear características técnicas',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'equipo_id', type: 'integer'),
                    new OA\Property(property: 'procesador', type: 'string'),
                    new OA\Property(property: 'memoria_ram', type: 'string'),
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
        $this->permissionService->authorize("pc_caracteristicas_tecnicas.crear");
        $validated = $request->validate([
            'equipo_id' => 'required|integer|exists:pc_equipos,id',
            'procesador' => 'nullable|string|max:255',
            'memoria_ram' => 'nullable|string|max:255',
            'disco_duro' => 'nullable|string|max:255',
            'tarjeta_video' => 'nullable|string|max:255',
            'tarjeta_red' => 'nullable|string|max:255',
            'tarjeta_sonido' => 'nullable|string|max:255',
            'usb' => 'nullable|string|max:35',
            'unidad_cd' => 'nullable|string|max:35',
            'parlantes' => 'nullable|string|max:35',
            'drive' => 'nullable|string|max:35',
            'monitor' => 'nullable|string|max:255',
            'monitor_id' => 'nullable|integer|exists:inventario,id',
            'teclado' => 'nullable|string|max:255',
            'teclado_id' => 'nullable|integer|exists:inventario,id',
            'mouse' => 'nullable|string|max:255',
            'mouse_id' => 'nullable|integer|exists:inventario,id',
            'internet' => 'nullable|string|max:255',
            'velocidad_red' => 'nullable|string|max:255',
            'capacidad_disco' => 'nullable|string|max:255',
        ]);

        try {
            $item = $this->service->create($validated);
            return ApiResponse::success($item, 'Características técnicas creadas exitosamente', 201);
        } catch (\Exception $e) {
            return ApiResponse::error('Error al crear características: ' . $e->getMessage(), 500);
        }
    }

    #[OA\Get(
        path: '/api/pc-caracteristicas-tecnicas/{id}',
        tags: ['PcCaracteristicasTecnicas'],
        summary: 'Obtener por ID',
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
            return ApiResponse::error('Registro no encontrado', 404);
        }

        return ApiResponse::success($item, 'Detalle de características técnicas');
    }

    #[OA\Get(
        path: '/api/pc-caracteristicas-tecnicas/equipo/{equipo_id}',
        tags: ['PcCaracteristicasTecnicas'],
        summary: 'Obtener por ID de Equipo',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'equipo_id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Detalle', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponse')),
            new OA\Response(response: 404, description: 'No encontrado')
        ]
    )]
    public function showByEquipo($equipo_id)
    {
        $item = $this->service->getByEquipo($equipo_id);

        if (!$item) {
            return ApiResponse::error('Características no encontradas para este equipo', 404);
        }

        return ApiResponse::success($item, 'Características técnicas del equipo');
    }

    #[OA\Put(
        path: '/api/pc-caracteristicas-tecnicas/{id}',
        tags: ['PcCaracteristicasTecnicas'],
        summary: 'Actualizar características',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'procesador', type: 'string'),
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
        $validated = $request->validate([
            'equipo_id' => 'sometimes|integer|exists:pc_equipos,id',
            'procesador' => 'nullable|string|max:255',
            'memoria_ram' => 'nullable|string|max:255',
            'disco_duro' => 'nullable|string|max:255',
            'tarjeta_video' => 'nullable|string|max:255',
            'tarjeta_red' => 'nullable|string|max:255',
            'tarjeta_sonido' => 'nullable|string|max:255',
            'usb' => 'nullable|string|max:35',
            'unidad_cd' => 'nullable|string|max:35',
            'parlantes' => 'nullable|string|max:35',
            'drive' => 'nullable|string|max:35',
            'monitor' => 'nullable|string|max:255',
            'monitor_id' => 'nullable|integer|exists:inventario,id',
            'teclado' => 'nullable|string|max:255',
            'teclado_id' => 'nullable|integer|exists:inventario,id',
            'mouse' => 'nullable|string|max:255',
            'mouse_id' => 'nullable|integer|exists:inventario,id',
            'internet' => 'nullable|string|max:255',
            'velocidad_red' => 'nullable|string|max:255',
            'capacidad_disco' => 'nullable|string|max:255',
        ]);

        try {
            $item = $this->service->update($id, $validated);
            
            if (!$item) {
                return ApiResponse::error('Registro no encontrado', 404);
            }

            return ApiResponse::success($item, 'Características actualizadas exitosamente');
        } catch (\Exception $e) {
            return ApiResponse::error('Error al actualizar: ' . $e->getMessage(), 500);
        }
    }

    #[OA\Delete(
        path: '/api/pc-caracteristicas-tecnicas/{id}',
        tags: ['PcCaracteristicasTecnicas'],
        summary: 'Eliminar características',
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
        $this->permissionService->authorize("pc_caracteristicas_tecnicas.eliminar");
        if ($this->service->delete($id)) {
            return ApiResponse::success(null, 'Registro eliminado exitosamente');
        }

        return ApiResponse::error('Registro no encontrado o no se pudo eliminar', 404);
    }
}

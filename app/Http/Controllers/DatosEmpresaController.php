<?php

namespace App\Http\Controllers;

use App\Services\DatosEmpresaService;
use App\Responses\ApiResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class DatosEmpresaController extends Controller
{
    public function __construct(
        protected DatosEmpresaService $service
    ) {}

    #[OA\Get(
        path: '/api/datos-empresa',
        tags: ['DatosEmpresa'],
        summary: 'Listar datos de empresas',
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Lista obtenida', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponse')),
            new OA\Response(response: 403, description: 'Prohibido')
        ]
    )]
    public function index()
    {
        $items = $this->service->getAll();
        return ApiResponse::success($items, 'Listado exitoso');
    }

    #[OA\Post(
        path: '/api/datos-empresa',
        tags: ['DatosEmpresa'],
        summary: 'Registrar empresa',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'nombre', type: 'string'),
                    new OA\Property(property: 'nit', type: 'string'),
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
        $validated = $request->validate([
            'nombre' => 'nullable|string|max:255',
            'nit' => 'nullable|string|max:255',
            'direccion' => 'nullable|string|max:255',
            'telefono' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'representante_legal' => 'nullable|string|max:255',
            'ciudad' => 'nullable|string|max:255',
        ]);

        try {
            $item = $this->service->create($validated);
            return ApiResponse::success($item, 'Empresa registrada exitosamente', 201);
        } catch (\Exception $e) {
            return ApiResponse::error('Error al registrar empresa: ' . $e->getMessage(), 500);
        }
    }

    #[OA\Get(
        path: '/api/datos-empresa/{id}',
        tags: ['DatosEmpresa'],
        summary: 'Obtener empresa',
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
            return ApiResponse::error('Empresa no encontrada', 404);
        }

        return ApiResponse::success($item, 'Detalle obtenido');
    }

    #[OA\Put(
        path: '/api/datos-empresa/{id}',
        tags: ['DatosEmpresa'],
        summary: 'Actualizar empresa',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'nombre', type: 'string'),
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
        $item = $this->service->find($id);
        if (!$item) {
            return ApiResponse::error('Empresa no encontrada', 404);
        }

        $validated = $request->validate([
            'nombre' => 'nullable|string|max:255',
            'nit' => 'nullable|string|max:255',
            'direccion' => 'nullable|string|max:255',
            'telefono' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'representante_legal' => 'nullable|string|max:255',
            'ciudad' => 'nullable|string|max:255',
        ]);

        try {
            $updated = $this->service->update($id, $validated);
            return ApiResponse::success($updated, 'Empresa actualizada exitosamente');
        } catch (\Exception $e) {
            return ApiResponse::error('Error al actualizar: ' . $e->getMessage(), 500);
        }
    }

    #[OA\Delete(
        path: '/api/datos-empresa/{id}',
        tags: ['DatosEmpresa'],
        summary: 'Eliminar empresa',
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
        if ($this->service->delete($id)) {
            return ApiResponse::success(null, 'Empresa eliminada exitosamente');
        }

        return ApiResponse::error('Empresa no encontrada o no se pudo eliminar', 404);
    }
}

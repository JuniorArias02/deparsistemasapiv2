<?php

namespace App\Http\Controllers;

use App\Services\PcEquipoService;
use App\Responses\ApiResponse;
use Illuminate\Http\Request;
use App\Services\PermissionService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\PcEquipo;
use OpenApi\Attributes as OA;

class PcEquipoController extends Controller
{
    public function __construct(
        protected PcEquipoService $service,
        protected PermissionService $permissionService
    ) {}

    #[OA\Get(
        path: '/api/pc-equipos',
        tags: ['PcEquipos'],
        summary: 'Listar equipos de PC',
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Lista de equipos', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponse')),
            new OA\Response(response: 403, description: 'Prohibido')
        ]
    )]
    public function index(Request $request)
    {
        $equipos = $this->service->getAll($request->get('q'));
        return ApiResponse::success($equipos, 'Lista de equipos obtenida exitosamente');
    }

    #[OA\Post(
        path: '/api/pc-equipos',
        tags: ['PcEquipos'],
        summary: 'Crear equipo de PC',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'serial', type: 'string'),
                    new OA\Property(property: 'nombre_equipo', type: 'string'),
                    new OA\Property(property: 'sede_id', type: 'integer'),
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
        $this->permissionService->authorize('pc_equipo.crear');
        $validated = $request->validate([
            'serial' => 'required|string|unique:pc_equipos,serial|max:255',
            'numero_inventario' => 'nullable|string|unique:pc_equipos,numero_inventario|max:255',
            'nombre_equipo' => 'nullable|string|max:255',
            'marca' => 'nullable|string|max:255',
            'modelo' => 'nullable|string|max:255',
            'tipo' => 'nullable|string|max:255',
            'propiedad' => 'nullable|in:empleado,empresa',
            'ip_fija' => 'nullable|string|max:255',
            'sede_id' => 'nullable|integer|exists:sedes,id',
            'area_id' => 'nullable|integer|exists:areas,id',
            'responsable_id' => 'nullable|integer|exists:personal,id',
            'estado' => 'nullable|string|max:255',
            'fecha_ingreso' => 'nullable|date',
            'imagen' => 'nullable|image|max:5120', // 5MB max
            'fecha_entrega' => 'nullable|date',
            'descripcion_general' => 'nullable|string',
            'garantia_meses' => 'nullable|integer',
            'forma_adquisicion' => 'nullable|in:compra,alquiler,donacion,comodato',
            'observaciones' => 'nullable|string',
            'repuestos_principales' => 'nullable|string',
            'recomendaciones' => 'nullable|string',
            'equipos_adicionales' => 'nullable|string',
        ]);

        try {
            if (auth()->check()) {
                $validated['creado_por'] = auth()->id();
            } else {
                return ApiResponse::error('Usuario no autenticado', 401);
            }

            // Handle Image Upload
            if ($request->hasFile('imagen')) {
                $path = $request->file('imagen')->store('pcEquipos', 'public');
                $validated['imagen_url'] = 'storage/' . $path;
            }

            $item = $this->service->create($validated);
            return ApiResponse::success($item, 'Equipo creado exitosamente', 201);
        } catch (\Exception $e) {
            \Log::error('Error creating PcEquipo: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return ApiResponse::error('Error al crear equipo: ' . $e->getMessage(), 500);
        }
    }

    #[OA\Get(
        path: '/api/pc-equipos/{id}',
        tags: ['PcEquipos'],
        summary: 'Obtener detalles de un equipo',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Detalles del equipo', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponse')),
            new OA\Response(response: 404, description: 'No encontrado'),
            new OA\Response(response: 403, description: 'Prohibido')
        ]
    )]
    public function show($id)
    {
        try {
            $item = $this->service->find($id);

            if (!$item) {
                return ApiResponse::error('Equipo no encontrado', 404);
            }

            return ApiResponse::success($item, 'Detalle del equipo');
        } catch (\Exception $e) {
            return ApiResponse::error('Error fetching equipe: ' . $e->getMessage(), 500);
        }
    }

    public function update(Request $request, $id)
    {
        $this->permissionService->authorize('pc_equipo.actualizar');
        $item = $this->service->find($id);
        if (!$item) {
            return ApiResponse::error('Equipo no encontrado', 404);
        }

        $validated = $request->validate([
            'serial' => 'sometimes|string|max:255|unique:pc_equipos,serial,' . $id,
            'numero_inventario' => 'nullable|string|max:255|unique:pc_equipos,numero_inventario,' . $id,
            'nombre_equipo' => 'nullable|string|max:255',
            'marca' => 'nullable|string|max:255',
            'modelo' => 'nullable|string|max:255',
            'tipo' => 'nullable|string|max:255',
            'propiedad' => 'nullable|in:empleado,empresa',
            'ip_fija' => 'nullable|string|max:255',
            'sede_id' => 'nullable|integer|exists:sedes,id',
            'area_id' => 'nullable|integer|exists:areas,id',
            'responsable_id' => 'nullable|integer|exists:personal,id',
            'estado' => 'nullable|string|max:255',
            'fecha_ingreso' => 'nullable|date',
            'imagen' => 'nullable|image|max:5120',
            'fecha_entrega' => 'nullable|date',
            'descripcion_general' => 'nullable|string',
            'garantia_meses' => 'nullable|integer',
            'forma_adquisicion' => 'nullable|in:compra,alquiler,donacion,comodato',
            'observaciones' => 'nullable|string',
            'repuestos_principales' => 'nullable|string',
            'recomendaciones' => 'nullable|string',
            'equipos_adicionales' => 'nullable|string',
        ]);

        try {
            // Handle Image Upload
            if ($request->hasFile('imagen')) {
                // Delete old image if exists
                if ($item->imagen_url) {
                    $oldPath = str_replace('storage/', '', $item->imagen_url);
                    if (\Illuminate\Support\Facades\Storage::disk('public')->exists($oldPath)) {
                        \Illuminate\Support\Facades\Storage::disk('public')->delete($oldPath);
                    }
                }

                $path = $request->file('imagen')->store('pcEquipos', 'public');
                $validated['imagen_url'] = 'storage/' . $path;
            }

            $updated = $this->service->update($id, $validated);
            return ApiResponse::success($updated, 'Equipo actualizado exitosamente');
        } catch (\Exception $e) {
            return ApiResponse::error('Error al actualizar equipo: ' . $e->getMessage(), 500);
        }
    }

    #[OA\Delete(
        path: '/api/pc-equipos/{id}',
        tags: ['PcEquipos'],
        summary: 'Eliminar equipo',
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
        $this->permissionService->authorize('pc_equipo.eliminar');
        if ($this->service->delete($id)) {
            return ApiResponse::success(null, 'Equipo eliminado exitosamente');
        }

        return ApiResponse::error('Equipo no encontrado o no se pudo eliminar', 404);
    }

    public function hojaDeVida($id)
    {
        try {
            $data = $this->service->hojaDeVida($id);

            if (!$data) {
                return ApiResponse::error('Equipo no encontrado', 404);
            }

            return ApiResponse::success($data, 'Hoja de vida del equipo');
        } catch (\Exception $e) {
            return ApiResponse::error('Error al obtener hoja de vida: ' . $e->getMessage(), 500);
        }
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Inventario;
use App\Services\InventarioService;
use App\Responses\ApiResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class InventarioController extends Controller
{
    protected $permissionService;

    public function __construct(
        protected InventarioService $inventarioService,
        \App\Services\PermissionService $permissionService
    ) {
        $this->permissionService = $permissionService;
    }

    #[OA\Post(
        path: '/api/inventario',
        tags: ['Inventario'],
        summary: 'Crear item de inventario',
        description: 'Crea un nuevo registro en el inventario. Requiere autenticación JWT. Permite pasar todos los campos definidos en el sistema.',
        operationId: 'inventarioStore',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['codigo', 'nombre'],
                properties: [
                    new OA\Property(property: 'codigo', type: 'string', example: 'INV-001'),
                    new OA\Property(property: 'nombre', type: 'string', example: 'Laptop Dell Latitude'),
                    new OA\Property(property: 'dependencia', type: 'string', example: 'Sistemas'),
                    new OA\Property(property: 'responsable', type: 'string', example: 'Juan Perez'),
                    new OA\Property(property: 'responsable_id', type: 'integer', example: 1),
                    new OA\Property(property: 'coordinador_id', type: 'integer', example: 2),
                    new OA\Property(property: 'marca', type: 'string', example: 'Dell'),
                    new OA\Property(property: 'modelo', type: 'string', example: 'Latitude 5420'),
                    new OA\Property(property: 'serial', type: 'string', example: 'ABC123456'),
                    new OA\Property(property: 'proceso_id', type: 'integer', example: 1),
                    new OA\Property(property: 'sede_id', type: 'integer', example: 1),
                    new OA\Property(property: 'codigo_barras', type: 'string', example: '123456789'),
                    new OA\Property(property: 'num_factu', type: 'string', example: 'FAC-001'),
                    new OA\Property(property: 'grupo', type: 'string', example: 'Equipos de Computo'),
                    new OA\Property(property: 'vida_util', type: 'integer', example: 5),
                    new OA\Property(property: 'vida_util_niff', type: 'integer', example: 5),
                    new OA\Property(property: 'centro_costo', type: 'string', example: 'CC-001'),
                    new OA\Property(property: 'ubicacion', type: 'string', example: 'Piso 2'),
                    new OA\Property(property: 'proveedor', type: 'string', example: 'Dell Colombia'),
                    new OA\Property(property: 'fecha_compra', type: 'string', format: 'date', example: '2023-01-15'),
                    new OA\Property(property: 'soporte', type: 'string', example: 'Garantía 3 años'),
                    new OA\Property(property: 'soporte_adjunto', type: 'string', example: 'url/to/doc.pdf'),
                    new OA\Property(property: 'descripcion', type: 'string', example: 'Laptop asignada a sistemas'),
                    new OA\Property(property: 'estado', type: 'string', example: 'Bueno'),
                    new OA\Property(property: 'escritura', type: 'string', example: 'N/A'),
                    new OA\Property(property: 'matricula', type: 'string', example: 'MAT-01'),
                    new OA\Property(property: 'valor_compra', type: 'number', format: 'float', example: 1500.00),
                    new OA\Property(property: 'salvamenta', type: 'string', example: 'N/A'),
                    new OA\Property(property: 'depreciacion', type: 'number', format: 'float', example: 0.0),
                    new OA\Property(property: 'depreciacion_niif', type: 'number', format: 'float', example: 0.0),
                    new OA\Property(property: 'meses', type: 'string', example: '60'),
                    new OA\Property(property: 'meses_niif', type: 'string', example: '60'),
                    new OA\Property(property: 'tipo_adquisicion', type: 'string', example: 'Compra'),
                    new OA\Property(property: 'calibrado', type: 'string', format: 'date', example: '2023-01-20'),
                    new OA\Property(property: 'observaciones', type: 'string', example: 'Ninguna'),
                    new OA\Property(property: 'cuenta_inventario', type: 'number', format: 'float', example: 1500.00),
                    new OA\Property(property: 'cuenta_gasto', type: 'number', format: 'float', example: 0.0),
                    new OA\Property(property: 'cuenta_salida', type: 'number', format: 'float', example: 0.0),
                    new OA\Property(property: 'grupo_activos', type: 'string', example: 'Activos Fijos'),
                    new OA\Property(property: 'valor_actual', type: 'number', format: 'float', example: 1500.00),
                    new OA\Property(property: 'depreciacion_acumulada', type: 'number', format: 'float', example: 0.0),
                    new OA\Property(property: 'tipo_bien', type: 'string', example: 'Mueble/Enser'),
                    new OA\Property(property: 'tiene_accesorio', type: 'string', example: 'No'),
                    new OA\Property(property: 'descripcion_accesorio', type: 'string', example: ''),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Inventario creado exitosamente',
                content: new OA\JsonContent(ref: '#/components/schemas/ApiResponse')
            ),
            new OA\Response(
                response: 422,
                description: 'Error de validación',
                content: new OA\JsonContent(ref: '#/components/schemas/ApiResponse')
            )
        ]
    )]
    public function store(Request $request)
    {
        $this->permissionService->authorize('inventario.create');

        $validated = $request->validate([
            'codigo' => 'required|string|max:50|unique:inventario,codigo',
            'nombre' => 'required|string|max:100',
            'dependencia' => 'nullable|string|max:100',
            'responsable' => 'nullable|string|max:100',
            'responsable_id' => 'nullable|exists:usuarios,id',
            'coordinador_id' => 'nullable|exists:usuarios,id',
            'marca' => 'nullable|string|max:100',
            'modelo' => 'nullable|string|max:100',
            'serial' => 'nullable|string|max:100',
            'proceso_id' => 'nullable|integer',
            'sede_id' => 'nullable|exists:sedes,id',
            'creado_por' => 'nullable|integer', 
            'codigo_barras' => 'nullable|string|max:160',
            'num_factu' => 'nullable|string|max:60',
            'grupo' => 'nullable|string|max:60',
            'vida_util' => 'nullable|integer',
            'vida_util_niff' => 'nullable|integer',
            'centro_costo' => 'nullable|string|max:120',
            'ubicacion' => 'nullable|string|max:60',
            'proveedor' => 'nullable|string|max:60',
            'fecha_compra' => 'nullable|date',
            'soporte' => 'nullable|string|max:160',
            'soporte_adjunto' => 'nullable|string|max:260',
            'descripcion' => 'nullable|string|max:160',
            'estado' => 'nullable|string|max:160',
            'escritura' => 'nullable|string|max:255',
            'matricula' => 'nullable|string|max:10',
            'valor_compra' => 'nullable|numeric',
            'salvamenta' => 'nullable|string|max:255',
            'depreciacion' => 'nullable|numeric',
            'depreciacion_niif' => 'nullable|numeric',
            'meses' => 'nullable|string|max:7',
            'meses_niif' => 'nullable|string|max:8',
            'tipo_adquisicion' => 'nullable|string|max:60',
            'calibrado' => 'nullable|date',
            'observaciones' => 'nullable|string',
            'cuenta_inventario' => 'nullable|numeric',
            'cuenta_gasto' => 'nullable|numeric',
            'cuenta_salida' => 'nullable|numeric',
            'grupo_activos' => 'nullable|string|max:60',
            'valor_actual' => 'nullable|numeric',
            'depreciacion_acumulada' => 'nullable|numeric',
            'tipo_bien' => 'nullable|string|max:60',
            'tiene_accesorio' => 'nullable|string|max:10',
            'descripcion_accesorio' => 'nullable|string',
        ]);

        try {
            $inventario = $this->inventarioService->create($request->all());
            return ApiResponse::success($inventario, 'Inventario creado exitosamente', 201);
        } catch (\Exception $e) {
            return ApiResponse::error('Error al crear inventario: ' . $e->getMessage(), 500);
        }
    }

    #[OA\Put(
        path: '/api/inventario/{id}',
        tags: ['Inventario'],
        summary: 'Actualizar item de inventario',
        description: 'Actualiza un registro existente en el inventario. Requiere autenticación JWT y permiso inventario.update.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'nombre', type: 'string', example: 'Laptop Updated'),
                    // ... other properties
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Inventario actualizado', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponse')),
            new OA\Response(response: 404, description: 'Inventario no encontrado'),
            new OA\Response(response: 403, description: 'Prohibido')
        ]
    )]
    public function update(Request $request, $id)
    {
        $this->permissionService->authorize('inventario.update');

        $inventario = Inventario::find($id);
        if (!$inventario) {
            return ApiResponse::error('Item de inventario no encontrado', 404);
        }
        $validated = $request->validate([
            'codigo' => 'nullable|string|max:50|unique:inventario,codigo,' . $id,
            'nombre' => 'nullable|string|max:100',
            'dependencia' => 'nullable|string|max:100',
            'responsable' => 'nullable|string|max:100',
            'responsable_id' => 'nullable|exists:usuarios,id',
            'coordinador_id' => 'nullable|exists:usuarios,id',
            'marca' => 'nullable|string|max:100',
            'modelo' => 'nullable|string|max:100',
            'serial' => 'nullable|string|max:100',
            'proceso_id' => 'nullable|integer',
            'sede_id' => 'nullable|exists:sedes,id',
            'creado_por' => 'nullable|integer', 
            'codigo_barras' => 'nullable|string|max:160',
            'num_factu' => 'nullable|string|max:60',
            'grupo' => 'nullable|string|max:60',
            'vida_util' => 'nullable|integer',
            'vida_util_niff' => 'nullable|integer',
            'centro_costo' => 'nullable|string|max:120',
            'ubicacion' => 'nullable|string|max:60',
            'proveedor' => 'nullable|string|max:60',
            'fecha_compra' => 'nullable|date',
            'soporte' => 'nullable|string|max:160',
            'soporte_adjunto' => 'nullable|string|max:260',
            'descripcion' => 'nullable|string|max:160',
            'estado' => 'nullable|string|max:160',
            'escritura' => 'nullable|string|max:255',
            'matricula' => 'nullable|string|max:10',
            'valor_compra' => 'nullable|numeric',
            'salvamenta' => 'nullable|string|max:255',
            'depreciacion' => 'nullable|numeric',
            'depreciacion_niif' => 'nullable|numeric',
            'meses' => 'nullable|string|max:7',
            'meses_niif' => 'nullable|string|max:8',
            'tipo_adquisicion' => 'nullable|string|max:60',
            'calibrado' => 'nullable|date',
            'observaciones' => 'nullable|string',
            'cuenta_inventario' => 'nullable|numeric',
            'cuenta_gasto' => 'nullable|numeric',
            'cuenta_salida' => 'nullable|numeric',
            'grupo_activos' => 'nullable|string|max:60',
            'valor_actual' => 'nullable|numeric',
            'depreciacion_acumulada' => 'nullable|numeric',
            'tipo_bien' => 'nullable|string|max:60',
            'tiene_accesorio' => 'nullable|string|max:10',
            'descripcion_accesorio' => 'nullable|string',
        ]);

        try {
            $inventario->update($request->all());
            return ApiResponse::success($inventario, 'Inventario actualizado exitosamente');
        } catch (\Exception $e) {
            return ApiResponse::error('Error al actualizar inventario: ' . $e->getMessage(), 500);
        }
    }

    #[OA\Delete(
        path: '/api/inventario/{id}',
        tags: ['Inventario'],
        summary: 'Eliminar item de inventario',
        description: 'Elimina un registro del inventario. Requiere autenticación JWT y permiso inventario.delete.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Inventario eliminado', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponse')),
            new OA\Response(response: 404, description: 'Inventario no encontrado'),
            new OA\Response(response: 403, description: 'Prohibido')
        ]
    )]
    public function destroy($id)
    {
        $this->permissionService->authorize('inventario.delete');

        $inventario = Inventario::find($id);
        if (!$inventario) {
            return ApiResponse::error('Item de inventario no encontrado', 404);
        }

        try {
            $inventario->delete();
            return ApiResponse::success(null, 'Inventario eliminado exitosamente');
        } catch (\Exception $e) {
            return ApiResponse::error('Error al eliminar inventario: ' . $e->getMessage(), 500);
        }
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Inventario;
use App\Services\InventarioService;
use App\Responses\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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

    /**
     * List all inventory items
     */
    #[OA\Get(
        path: '/api/inventario',
        tags: ['Inventario'],
        summary: 'Listar todos los items de inventario',
        description: 'Obtiene la lista completa de items en el inventario. Requiere autenticación JWT.',
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Lista de items de inventario',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'mensaje', type: 'string', example: 'Lista de inventario'),
                        new OA\Property(property: 'objeto', type: 'array', items: new OA\Items(type: 'object')),
                        new OA\Property(property: 'status', type: 'integer', example: 200)
                    ]
                )
            )
        ]
    )]
    public function index(Request $request)
    {
        // $this->permissionService->authorize('inventario.read');

        try {
            $query = Inventario::query();

            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('codigo', 'like', "%{$search}%")
                        ->orWhere('nombre', 'like', "%{$search}%")
                        ->orWhere('serial', 'like', "%{$search}%");
                });
            }

            $perPage = $request->input('per_page', 100);

            // Limit results if searching to avoid huge payload, or just paginate
            if ($request->has('search') && !empty($request->search)) {
                $inventarios = $query->paginate($perPage);
            } else {
                $inventarios = $query->paginate($perPage);
            }

            return ApiResponse::success($inventarios, 'Lista de inventario', 200);
        } catch (\Exception $e) {
            return ApiResponse::error('Error al obtener inventarios: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get inventory items by responsable and coordinador
     */
    public function getByResponsableAndCoordinador(Request $request)
    {
        try {
            $validated = $request->validate([
                'responsable_id' => 'required|integer',
                'coordinador_id' => 'required|integer'
            ]);

            $items = Inventario::where('responsable_id', $validated['responsable_id'])
                ->where('coordinador_id', $validated['coordinador_id'])
                ->get();

            return response()->json([
                'mensaje' => $items->count() > 0 ? 'Items encontrados' : 'No hay items asignados',
                'objeto' => $items,
                'status' => 200
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'mensaje' => 'Error al buscar items: ' . $e->getMessage(),
                'objeto' => [],
                'status' => 500
            ], 500);
        }
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
        $this->permissionService->authorize('inventario.crear');

        $validated = $request->validate([
            // Campos obligatorios
            'codigo' => 'required|string|max:50|unique:inventario,codigo',
            'nombre' => 'required|string|max:100',
            'dependencia' => 'required|string|max:100',
            'responsable_id' => 'required|exists:personal,id',
            'coordinador_id' => 'required|exists:personal,id',
            'sede_id' => 'required|exists:sedes,id',
            'proceso_id' => 'required|integer',

            // Campos opcionales
            'responsable' => 'nullable|string|max:100',
            'marca' => 'nullable|string|max:100',
            'modelo' => 'nullable|string|max:100',
            'serial' => 'nullable|string|max:100',
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

        $data = $request->all();

        // Si creado_por no viene o es null, usar el ID del usuario autenticado
        if (empty($data['creado_por'])) {
            $data['creado_por'] = auth()->id();
        }

        // Handle file upload
        if ($request->hasFile('soporte_adjunto')) {
            $file = $request->file('soporte_adjunto');
            // Validate it is a PDF if strictly required, though max:260 suggests path length constraint in DB.
            // Let's ensure it's a file.
            $request->validate([
                'soporte_adjunto' => 'file|mimes:pdf|max:10240', // Max 10MB
            ]);

            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('inventarioAdjunto', $filename, 'public');
            $data['soporte_adjunto'] = 'storage/' . $path;
        }

        try {
            $inventario = $this->inventarioService->create($data);
            return ApiResponse::success($inventario, 'Inventario creado exitosamente', 201);
        } catch (\Exception $e) {
            return ApiResponse::error('Error al crear inventario: ' . $e->getMessage(), 500);
        }
    }

    #[OA\Get(
        path: '/api/inventario/{id}',
        tags: ['Inventario'],
        summary: 'Obtener item de inventario',
        description: 'Obtiene los detalles de un item específico del inventario por su ID.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Detalles del inventario', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponse')),
            new OA\Response(response: 404, description: 'Inventario no encontrado')
        ]
    )]
    public function show($id)
    {
        $inventario = Inventario::with(['responsablePersonal', 'coordinadorPersonal', 'sede'])->find($id);

        if (!$inventario) {
            return ApiResponse::error('Item de inventario no encontrado', 404);
        }

        return ApiResponse::success($inventario, 'Detalles del inventario');
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
        $this->permissionService->authorize('inventario.actualizar');

        $inventario = Inventario::find($id);
        if (!$inventario) {
            return ApiResponse::error('Item de inventario no encontrado', 404);
        }
        $validated = $request->validate([
            'codigo' => 'nullable|string|max:50|unique:inventario,codigo,' . $id,
            'nombre' => 'nullable|string|max:100',
            'dependencia' => 'nullable|string|max:100',
            'responsable' => 'nullable|string|max:100',
            'responsable_id' => 'nullable|exists:personal,id',
            'coordinador_id' => 'nullable|exists:personal,id',
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
            $inventario->update($validated);
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
        $this->permissionService->authorize('inventario.eliminar');

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

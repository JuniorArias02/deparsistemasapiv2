<?php

namespace App\Http\Controllers;

use App\Models\CpPedido;
use App\Models\CpItemPedido;
use App\Services\PermissionService;
use App\Services\CpPedidoService;
use App\Responses\ApiResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class CpPedidoController extends Controller
{
    public function __construct(
        protected PermissionService $permissionService,
        protected CpPedidoService $service
    ) {}

    /**
     * Display a listing of the resource.
     */
    #[OA\Get(
        path: '/api/cp-pedidos',
        tags: ['Pedidos de Compra'],
        summary: 'Listar pedidos de compra',
        description: 'Obtiene la lista de pedidos de compra con sus relaciones.',
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Lista de pedidos', content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/CpPedido'))),
            new OA\Response(response: 403, description: 'Prohibido')
        ]
    )]
    public function index()
    {
        try {
            $user = auth('api')->user();
            $pedidos = $this->service->getAll($user);
            return ApiResponse::success($pedidos, 'Listado de pedidos obtenido correctamente');
        } catch (\Exception $e) {
            $status = $e->getCode() === 403 ? 403 : 500;
            return ApiResponse::error('Error obteniendo pedidos: ' . $e->getMessage(), $status);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    #[OA\Post(
        path: '/api/cp-pedidos',
        tags: ['Pedidos de Compra'],
        summary: 'Crear pedido de compra',
        description: 'Crea un nuevo pedido de compra con sus items.',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    required: ['proceso_solicitante', 'tipo_solicitud', 'consecutivo', 'sede_id', 'elaborado_por', 'items'],
                    properties: [
                        new OA\Property(property: 'proceso_solicitante', type: 'integer', description: 'ID de la dependencia solicitante'),
                        new OA\Property(property: 'tipo_solicitud', type: 'integer', description: 'ID del tipo de solicitud'),
                        new OA\Property(property: 'observacion', type: 'string', description: 'Observaciones generales'),
                        new OA\Property(property: 'sede_id', type: 'integer', description: 'ID de la sede'),
                        new OA\Property(property: 'elaborado_por', type: 'integer', description: 'ID del usuario que elabora'),
                        new OA\Property(property: 'elaborado_por_firma', type: 'string', format: 'binary', description: 'Archivo de firma (PNG < 1MB)'),
                        new OA\Property(property: 'use_stored_signature', type: 'boolean', description: 'Usar firma guardada del usuario'),
                        new OA\Property(
                            property: 'items',
                            type: 'array',
                            description: 'Lista de items del pedido',
                            items: new OA\Items(
                                type: 'object',
                                required: ['nombre', 'cantidad', 'unidad_medida', 'productos_id'],
                                properties: [
                                    new OA\Property(property: 'nombre', type: 'string'),
                                    new OA\Property(property: 'cantidad', type: 'integer'),
                                    new OA\Property(property: 'unidad_medida', type: 'string'),
                                    new OA\Property(property: 'referencia_items', type: 'string'),
                                    new OA\Property(property: 'productos_id', type: 'integer')
                                ]
                            )
                        )
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Pedido creado exitosamente'),
            new OA\Response(response: 422, description: 'Error de validación'),
            new OA\Response(response: 500, description: 'Error del servidor')
        ]
    )]
    public function store(Request $request)
    {
        $this->permissionService->authorize('cp_pedido.crear');
        $validated = $request->validate([
            'proceso_solicitante' => 'required|exists:dependencias_sedes,id',
            'tipo_solicitud' => 'required|exists:cp_tipo_solicitud,id',
            'observacion' => 'nullable|string',
            'sede_id' => 'required|exists:sedes,id',
            'elaborado_por' => 'required|exists:usuarios,id',
            'use_stored_signature' => 'nullable|boolean',
            'elaborado_por_firma' => 'nullable|file|image|max:1024',
            'items' => 'required|array|min:1',
            'items.*.nombre' => 'required|string|max:255',
            'items.*.cantidad' => 'required|integer|min:1',
            'items.*.unidad_medida' => 'required|string|max:60',
            'items.*.productos_id' => 'required|exists:cp_productos,id',
            'items.*.referencia_items' => 'nullable|string|max:255',
        ]);

        if (!$request->hasFile('elaborado_por_firma') && !$request->boolean('use_stored_signature')) {
            return response()->json(['error' => 'Debe proporcionar una firma o usar la guardada.'], 400);
        }

        try {
            $pedido = $this->service->create(
                $validated,
                $request->file('elaborado_por_firma'),
                $request->boolean('use_stored_signature'),
                auth()->user()
            );

            return response()->json([
                'message' => 'Pedido creado exitosamente',
                'pedido' => $pedido,
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al crear el pedido: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    #[OA\Get(
        path: '/api/cp-pedidos/{id}',
        tags: ['Pedidos de Compra'],
        summary: 'Obtener pedido de compra',
        description: 'Obtiene los detalles de un pedido específico.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Detalles del pedido', content: new OA\JsonContent(ref: '#/components/schemas/CpPedido')),
            new OA\Response(response: 404, description: 'Pedido no encontrado')
        ]
    )]
    public function show($id)
    {
        $this->permissionService->authorize('cp_pedido.listar');
        $pedido = $this->service->getById($id);

        if (!$pedido) {
            return response()->json(['error' => 'Pedido no encontrado'], 404);
        }

        return response()->json($pedido);
    }

    /**
     * Remove the specified resource from storage.
     */
    #[OA\Delete(
        path: '/api/cp-pedidos/{id}',
        tags: ['Pedidos de Compra'],
        summary: 'Eliminar pedido de compra',
        description: 'Elimina un pedido y sus items asociados.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Pedido eliminado'),
            new OA\Response(response: 404, description: 'Pedido no encontrado'),
            new OA\Response(response: 500, description: 'Error del servidor')
        ]
    )]
    public function destroy($id)
    {
        $this->permissionService->authorize('cp_pedido.eliminar');

        try {
            if ($this->service->delete($id)) {
                return response()->json(['message' => 'Pedido eliminado exitosamente']);
            }
            return response()->json(['error' => 'Pedido no encontrado'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al eliminar el pedido: ' . $e->getMessage()], 500);
        }
    }

    #[OA\Post(
        path: '/api/cp-pedidos/{id}/aprobar-compras',
        tags: ['Pedidos de Compra'],
        summary: 'Aprobar pedido (Compras)',
        description: 'Aprueba un pedido por parte del área de compras.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    required: [],
                    properties: [
                        new OA\Property(property: 'motivo_aprobacion', type: 'string', description: 'Motivo de la aprobación (opcional)'),
                        new OA\Property(property: 'proceso_compra_firma', type: 'string', format: 'binary', description: 'Archivo de firma (PNG < 1MB)'),
                        new OA\Property(property: 'use_stored_signature', type: 'boolean', description: 'Usar firma guardada del usuario')
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Pedido aprobado'),
            new OA\Response(response: 404, description: 'Pedido no encontrado'),
            new OA\Response(response: 400, description: 'Error de validación o falta de firma')
        ]
    )]
    public function aprobarCompras(Request $request, $id)
    {
        $this->permissionService->authorize('cp_pedido.aprobar_compras');
        $validated = $request->validate([
            'motivo_aprobacion' => 'nullable|string',
            'use_stored_signature' => 'nullable|boolean',
            'proceso_compra_firma' => 'nullable|file|image|max:1024',
            'items_comprados' => 'nullable|array',
            'items_comprados.*' => 'exists:cp_items_pedidos,id'
        ]);

        if (!$request->hasFile('proceso_compra_firma') && !$request->boolean('use_stored_signature')) {
            return response()->json(['error' => 'Debe proporcionar una firma o usar la guardada.'], 400);
        }

        try {
            $pedido = $this->service->aprobarCompras(
                $id,
                $validated,
                $request->file('proceso_compra_firma'),
                $request->boolean('use_stored_signature'),
                auth()->user()
            );

            return response()->json(['message' => 'Pedido aprobado por compras', 'pedido' => $pedido]);
        } catch (\Exception $e) {
            $status = $e->getCode() === 404 ? 404 : 500;
            return response()->json(['error' => $e->getMessage()], $status);
        }
    }

    #[OA\Post(
        path: '/api/cp-pedidos/{id}/rechazar-compras',
        tags: ['Pedidos de Compra'],
        summary: 'Rechazar pedido (Compras)',
        description: 'Rechaza un pedido por parte del área de compras.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'motivo', type: 'string', description: 'Motivo del rechazo')
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Pedido rechazado'),
            new OA\Response(response: 404, description: 'Pedido no encontrado')
        ]
    )]
    public function rechazarCompras(Request $request, $id)
    {
        $this->permissionService->authorize('cp_pedido.rechazar_compras');
        $request->validate([
            'motivo' => 'nullable|string',
        ]);

        try {
            $pedido = $this->service->rechazarCompras($id, $request->motivo);
            return response()->json(['message' => 'Pedido rechazado por compras', 'pedido' => $pedido]);
        } catch (\Exception $e) {
            $status = $e->getCode() === 404 ? 404 : 500;
            return response()->json(['error' => $e->getMessage()], $status);
        }
    }

    #[OA\Post(
        path: '/api/cp-pedidos/{id}/aprobar-gerencia',
        tags: ['Pedidos de Compra'],
        summary: 'Aprobar pedido (Gerencia)',
        description: 'Aprueba un pedido por parte de la gerencia.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        requestBody: new OA\RequestBody(
            required: false,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'observacion_gerencia', type: 'string', description: 'Observación de gerencia (opcional)')
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Pedido aprobado'),
            new OA\Response(response: 404, description: 'Pedido no encontrado')
        ]
    )]
    public function aprobarGerencia(Request $request, $id)
    {
        $this->permissionService->authorize('cp_pedido.aprobar_gerencia');
        $validated = $request->validate([
            'observacion_gerencia' => 'nullable|string',
            'use_stored_signature' => 'nullable|boolean',
            'responsable_aprobacion_firma' => 'nullable|file|image|max:1024',
        ]);

        if (!$request->hasFile('responsable_aprobacion_firma') && !$request->boolean('use_stored_signature')) {
            return ApiResponse::error('Debe proporcionar una firma o usar la guardada.', 400);
        }

        try {
            $pedido = $this->service->aprobarGerencia(
                $id,
                $validated,
                $request->file('responsable_aprobacion_firma'),
                $request->boolean('use_stored_signature'),
                auth('api')->user()
            );

            return ApiResponse::success($pedido, 'Pedido aprobado por gerencia');
        } catch (\Exception $e) {
            $status = $e->getCode() === 404 ? 404 : 500;
            return ApiResponse::error('Error al aprobar el pedido: ' . $e->getMessage(), $status);
        }
    }

    #[OA\Post(
        path: '/api/cp-pedidos/{id}/rechazar-gerencia',
        tags: ['Pedidos de Compra'],
        summary: 'Rechazar pedido (Gerencia)',
        description: 'Rechaza un pedido por parte de la gerencia.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'observacion_gerencia', type: 'string', description: 'Observación de gerencia (obligatoria)')
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Pedido rechazado'),
            new OA\Response(response: 404, description: 'Pedido no encontrado')
        ]
    )]
    public function rechazarGerencia(Request $request, $id)
    {
        $this->permissionService->authorize('cp_pedido.rechazar_gerencia');
        $request->validate([
            'observacion_gerencia' => 'required|string',
        ]);

        try {
            $pedido = $this->service->rechazarGerencia($id, $request->observacion_gerencia, auth('api')->user());
            return response()->json(['message' => 'Pedido rechazado por gerencia', 'pedido' => $pedido]);
        } catch (\Exception $e) {
            $status = $e->getCode() === 404 ? 404 : 500;
            return response()->json(['error' => $e->getMessage()], $status);
        }
    }

    #[OA\Post(
        path: '/api/cp-pedidos/{id}/update-items',
        tags: ['Pedidos de Compra'],
        summary: 'Actualizar Items (Compras)',
        description: 'Actualiza el estado de compra de los items de un pedido.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: 'items',
                        type: 'array',
                        items: new OA\Items(
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer'),
                                new OA\Property(property: 'comprado', type: 'integer')
                            ]
                        )
                    )
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Items actualizados'),
            new OA\Response(response: 404, description: 'Pedido no encontrado')
        ]
    )]
    public function updateItems(Request $request, $id)
    {
        $this->permissionService->authorize('cp_pedido.actualizar_items');
        $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|exists:cp_items_pedidos,id',
            'items.*.comprado' => 'required|boolean'
        ]);

        try {
            $pedido = $this->service->updateItems($id, $request->items);
            return response()->json(['message' => 'Items actualizados correctamente', 'pedido' => $pedido]);
        } catch (\Exception $e) {
            $status = $e->getCode() === 404 ? 404 : 500;
            return response()->json(['error' => $e->getMessage()], $status);
        }
    }
    public function updateTracking(Request $request, $id)
    {
        $this->permissionService->authorize('cp_pedido.actualizar');

        $validated = $request->validate([
            'fecha_solicitud_cotizacion' => 'nullable|string|max:255',
            'fecha_respuesta_cotizacion' => 'nullable|string|max:255',
            'firma_aprobacion_orden' => 'nullable|date',
            'fecha_envio_proveedor' => 'nullable|string|max:255',
            'observaciones_pedidos' => 'nullable|string',
        ]);

        try {
            $pedido = CpPedido::find($id);
            if (!$pedido) {
                return ApiResponse::error('Pedido no encontrado', 404);
            }

            $pedido->update($validated);
            $pedido->load(['items', 'solicitante', 'tipoSolicitud', 'sede', 'elaboradoPor', 'procesoCompra', 'responsableAprobacion', 'creador']);

            return ApiResponse::success($pedido, 'Seguimiento actualizado correctamente');
        } catch (\Exception $e) {
            return ApiResponse::error('Error al actualizar seguimiento: ' . $e->getMessage(), 500);
        }
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\CpPedido;
use App\Models\CpItemPedido;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Exception;
use OpenApi\Attributes as OA;

class CpPedidoController extends Controller
{
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
    public function index() // Optional, but good to have
    {
        // Add filtering logic here if needed
        return CpPedido::with(['items', 'solicitante', 'tipoSolicitud', 'sede', 'elaboradoPor', 'procesoCompra', 'responsableAprobacion', 'creador'])->get();
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
                    required: ['proceso_solicitante', 'tipo_solicitud', 'consecutivo', 'sede_id', 'elaborado_por', 'elaborado_por_firma', 'items'],
                    properties: [
                        new OA\Property(property: 'proceso_solicitante', type: 'integer', description: 'ID de la dependencia solicitante'),
                        new OA\Property(property: 'tipo_solicitud', type: 'integer', description: 'ID del tipo de solicitud'),
                        new OA\Property(property: 'consecutivo', type: 'integer', description: 'Número consecutivo único'),
                        new OA\Property(property: 'observacion', type: 'string', description: 'Observaciones generales'),
                        new OA\Property(property: 'sede_id', type: 'integer', description: 'ID de la sede'),
                        new OA\Property(property: 'elaborado_por', type: 'integer', description: 'ID del usuario que elabora'),
                        new OA\Property(property: 'elaborado_por_firma', type: 'string', format: 'binary', description: 'Archivo de firma (PNG < 1MB)'),
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
        $validated = $request->validate([
            'proceso_solicitante' => 'required|exists:dependencias_sedes,id',
            'tipo_solicitud' => 'required|exists:cp_tipo_solicitud,id',
            // 'consecutivo' => 'required|integer|unique:cp_pedidos,consecutivo', // Auto-generated
            'observacion' => 'nullable|string',
            'sede_id' => 'required|exists:sedes,id',
            // 'elaborado_por' => 'required|exists:usuarios,id', // Can be derived from auth user or passed explicitly? Requirement says 'creador_por' from token. 'elaborado_por' seems to be a field to fill. Let's assume passed or same as creator.
            'elaborado_por' => 'required|exists:usuarios,id',
            'elaborado_por_firma' => 'required|file|image|max:1024', // PNG < 1MB
            'items' => 'required|array|min:1',
            'items.*.nombre' => 'required|string|max:255',
            'items.*.cantidad' => 'required|integer|min:1',
            'items.*.unidad_medida' => 'required|string|max:60',
            // 'items.*.referencia_items' => 'nullable|string',
            'items.*.productos_id' => 'required|exists:cp_productos,id',
        ]);

        DB::beginTransaction();

        try {
            $path = null;
            if ($request->hasFile('elaborado_por_firma')) {
                $file = $request->file('elaborado_por_firma');
                $filename = time() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('pedidos_firma', $filename, 'public');
            }

            // Calculate consecutivo
            $lastConsecutivo = CpPedido::max('consecutivo');
            $nextConsecutivo = $lastConsecutivo ? $lastConsecutivo + 1 : 1;

            /** @var CpPedido $pedido */
            $pedido = CpPedido::create([
                'estado_compras' => 'pendiente',
                'fecha_solicitud' => now(),
                'proceso_solicitante' => $validated['proceso_solicitante'],
                'tipo_solicitud' => $validated['tipo_solicitud'],
                'consecutivo' => $nextConsecutivo,
                'observacion' => $validated['observacion'],
                'sede_id' => $validated['sede_id'],
                'elaborado_por' => $validated['elaborado_por'],
                'elaborado_por_firma' => $path ? 'storage/' . $path : null, // Store relative path for frontend access
                'creador_por' => auth()->id(), // From JWT Token
                'pedido_visto' => 0,
                'estado_gerencia' => 'pendiente',
            ]);

            foreach ($validated['items'] as $item) {
                CpItemPedido::create([
                    'nombre' => $item['nombre'],
                    'cantidad' => $item['cantidad'],
                    'unidad_medida' => $item['unidad_medida'],
                    'referencia_items' => $item['referencia_items'] ?? null,
                    'cp_pedido' => $pedido->id,
                    'productos_id' => $item['productos_id'],
                    'comprado' => 0,
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Pedido creado exitosamente',
                'pedido' => $pedido->load('items'),
            ], 201);

        } catch (Exception $e) {
            DB::rollBack();
            if (isset($path)) {
                Storage::disk('public')->delete($path);
            }
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
        $pedido = CpPedido::with(['items', 'solicitante', 'tipoSolicitud', 'sede', 'elaboradoPor', 'procesoCompra', 'responsableAprobacion', 'creador'])->find($id);

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
        $pedido = CpPedido::find($id);

        if (!$pedido) {
            return response()->json(['error' => 'Pedido no encontrado'], 404);
        }

        // Check permissions if needed (e.g., only creator or admin)
        
        DB::beginTransaction();
        try {
            // Items are deleted via cascade if configured in DB, but let's be safe or if not configured
            // Logic says "Items asociados en cp_items_pedidos"
            // If DB cascade is ON, $pedido->delete() is enough. 
            // If not, delete items first.
            $pedido->items()->delete(); 
            $pedido->delete();
            
            DB::commit();
            return response()->json(['message' => 'Pedido eliminado exitosamente']);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error al eliminar el pedido: ' . $e->getMessage()], 500);
        }
    }

    // Custom Methods for Approvals/Rejections

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
                    required: ['proceso_compra_firma'],
                    properties: [
                        new OA\Property(property: 'motivo_aprobacion', type: 'string', description: 'Motivo de la aprobación (opcional)'),
                        new OA\Property(property: 'proceso_compra_firma', type: 'string', format: 'binary', description: 'Archivo de firma (PNG < 1MB)')
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Pedido aprobado'),
            new OA\Response(response: 404, description: 'Pedido no encontrado')
        ]
    )]
    public function aprobarCompras(Request $request, $id)
    {
        $request->validate([
             'motivo_aprobacion' => 'nullable|string',
             'proceso_compra_firma' => 'required|file|image|max:1024',
        ]);

        $pedido = CpPedido::find($id);
        if (!$pedido) return response()->json(['error' => 'Pedido no encontrado'], 404);

        $path = null;
        if ($request->hasFile('proceso_compra_firma')) {
            $file = $request->file('proceso_compra_firma');
            $filename = 'compra_' . time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('pedidos_firma', $filename, 'public');
        }

        /** @var CpPedido $pedido */
        $pedido->update([
            'estado_compras' => 'aprobado',
            'proceso_compra' => auth()->id(),
            'proceso_compra_firma' => $path ? 'storage/' . $path : null,
            'motivo_aprobacion' => $request->motivo_aprobacion,
            'fecha_compra' => now(),
        ]);

        return response()->json(['message' => 'Pedido aprobado por compras', 'pedido' => $pedido]);
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
        $request->validate([
            'motivo' => 'nullable|string', // "Puede registrar observación/motivo" - mapping to a field? 'observaciones_pedidos' or just 'observacion'? Let's use 'observaciones_pedidos' for this status flow usually
        ]);

        $pedido = CpPedido::find($id);
        if (!$pedido) return response()->json(['error' => 'Pedido no encontrado'], 404);

        $pedido->update([
            'estado_compras' => 'rechazado',
            'observaciones_pedidos' => $request->motivo // Assuming this field is used for rejection notes
        ]);

        return response()->json(['message' => 'Pedido rechazado por compras', 'pedido' => $pedido]);
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
        $request->validate([
            'observacion_gerencia' => 'nullable|string',
        ]);

        $pedido = CpPedido::find($id);
        if (!$pedido) return response()->json(['error' => 'Pedido no encontrado'], 404);

        $pedido->update([
            'estado_gerencia' => 'aprobado',
            'fecha_gerencia' => now(),
            'observacion_gerencia' => $request->observacion_gerencia,
        ]);

        return response()->json(['message' => 'Pedido aprobado por gerencia', 'pedido' => $pedido]);
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
        $request->validate([
            'observacion_gerencia' => 'required|string',
        ]);

        $pedido = CpPedido::find($id);
        if (!$pedido) return response()->json(['error' => 'Pedido no encontrado'], 404);

        $pedido->update([
            'estado_gerencia' => 'rechazado',
            'observacion_gerencia' => $request->observacion_gerencia,
        ]);

        return response()->json(['message' => 'Pedido rechazado por gerencia', 'pedido' => $pedido]);
    }
}

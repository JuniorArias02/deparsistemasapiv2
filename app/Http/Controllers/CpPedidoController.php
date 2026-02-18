<?php

namespace App\Http\Controllers;

use App\Models\CpPedido;
use App\Models\CpItemPedido;
use App\Services\PermissionService;
use App\Responses\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Exception;
use OpenApi\Attributes as OA;

class CpPedidoController extends Controller
{
    protected $permissionService;

    public function __construct(PermissionService $permissionService)
    {
        $this->permissionService = $permissionService;
    }
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
            $query = CpPedido::with(['items', 'solicitante', 'tipoSolicitud', 'sede', 'elaboradoPor', 'procesoCompra', 'responsableAprobacion', 'creador'])
                ->orderBy('id', 'desc');

            if ($this->permissionService->check($user, 'cp_pedido.listar.compras')) {
                $pedidos = $query->get();
            } elseif ($this->permissionService->check($user, 'cp_pedido.listar.responsable')) {
                $pedidos = $query->where('estado_compras', 'aprobado')->get();
            } else {
                $this->permissionService->authorize('cp_pedido.listar');
                $pedidos = $query->where('creador_por', $user->id)->get();
            }

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
                        new OA\Property(property: 'consecutivo', type: 'integer', description: 'Número consecutivo único'),
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
        ]);

        if (!$request->hasFile('elaborado_por_firma') && !$request->boolean('use_stored_signature')) {
            return response()->json(['error' => 'Debe proporcionar una firma o usar la guardada.'], 400);
        }

        DB::beginTransaction();

        try {
            $path = null;

            if ($request->boolean('use_stored_signature')) {
                $user = auth()->user();
                // Get raw attribute to avoid accessor URL transform
                $originalPath = $user->getAttributes()['firma_digital'] ?? null;

                if (!$originalPath || !Storage::disk('public')->exists($originalPath)) {
                    throw new Exception('No se encontró una firma digital guardada en su perfil.');
                }

                // Generate new filename for this specific approval
                $extension = pathinfo($originalPath, PATHINFO_EXTENSION);
                $filename = 'elaboracion_' . time() . '_stored.' . $extension;
                $newPath = 'pedidos_firma/' . $filename;

                // Copy file
                Storage::disk('public')->copy($originalPath, $newPath);
                $path = $newPath;
            } elseif ($request->hasFile('elaborado_por_firma')) {
                $file = $request->file('elaborado_por_firma');
                $filename = 'elaboracion_' . time() . '_' . $file->getClientOriginalName();
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

            // Send Email Notification to Compras Users
            try {
                // Find users with 'cp_pedido.listar.compras' permission
                $comprasUsers = \App\Models\Usuario::whereHas('rol.permisos', function ($query) {
                    $query->where('nombre', 'cp_pedido.listar.compras');
                })->whereNotNull('correo')->get();

                foreach ($comprasUsers as $user) {
                    \Illuminate\Support\Facades\Mail::to($user->correo)
                        ->send(new \App\Mail\NewOrderNotification($pedido));
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Error enviando correo de nuevo pedido a compras: ' . $e->getMessage());
            }

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
        $this->permissionService->authorize('cp_pedido.listar');
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
        $this->permissionService->authorize('cp_pedido.eliminar');
        $pedido = CpPedido::find($id);

        if (!$pedido) {
            return response()->json(['error' => 'Pedido no encontrado'], 404);
        }



        DB::beginTransaction();
        try {
            $pedido->items()->delete();
            $pedido->delete();

            DB::commit();
            return response()->json(['message' => 'Pedido eliminado exitosamente']);
        } catch (Exception $e) {
            DB::rollBack();
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
        $request->validate([
            'motivo_aprobacion' => 'nullable|string',
            'use_stored_signature' => 'nullable|boolean',
            'proceso_compra_firma' => 'nullable|file|image|max:1024',
            'items_comprados' => 'nullable|array',
            'items_comprados.*' => 'exists:cp_items_pedidos,id'
        ]);

        if (!$request->hasFile('proceso_compra_firma') && !$request->boolean('use_stored_signature')) {
            return response()->json(['error' => 'Debe proporcionar una firma o usar la guardada.'], 400);
        }

        $pedido = CpPedido::find($id);
        if (!$pedido) return response()->json(['error' => 'Pedido no encontrado'], 404);

        $path = null;

        if ($request->boolean('use_stored_signature')) {
            $user = auth()->user();
            // Get raw attribute to avoid accessor URL transform
            $originalPath = $user->getAttributes()['firma_digital'] ?? null;

            if (!$originalPath || !Storage::disk('public')->exists($originalPath)) {
                return response()->json(['error' => 'No se encontró una firma digital guardada en su perfil.'], 400);
            }

            // Generate new filename for this specific approval
            $extension = pathinfo($originalPath, PATHINFO_EXTENSION);
            $filename = 'compra_' . time() . '_stored.' . $extension;
            $newPath = 'pedidos_firma/' . $filename;

            // Copy file
            Storage::disk('public')->copy($originalPath, $newPath);
            $path = $newPath;
        } elseif ($request->hasFile('proceso_compra_firma')) {
            $file = $request->file('proceso_compra_firma');
            $filename = 'compra_' . time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('pedidos_firma', $filename, 'public');
        }

        /** @var CpPedido $pedido */
        $pedido->update([
            'estado_compras' => 'aprobado',
            'proceso_compra' => auth()->id(),
            'proceso_compra_firma' => $path ? 'storage/' . $path : null, // Store relative path like store()
            'motivo_aprobacion' => $request->motivo_aprobacion,
            'fecha_compra' => now(),
        ]);

        // Update items status
        if ($request->has('items_comprados')) {
            CpItemPedido::whereIn('id', $request->items_comprados)->update(['comprado' => 1]);
        }

        // Send Email Notification to Creator
        try {
            if ($pedido->creador && $pedido->creador->correo) {
                \Illuminate\Support\Facades\Mail::to($pedido->creador->correo)
                    ->send(new \App\Mail\OrderApprovedNotification($pedido));
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error enviando correo de aprobación de pedido: ' . $e->getMessage());
        }

        return response()->json(['message' => 'Pedido aprobado por compras', 'pedido' => $pedido->load('items')]);
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

        $pedido = CpPedido::find($id);
        if (!$pedido) return response()->json(['error' => 'Pedido no encontrado'], 404);

        $pedido->update([
            'estado_compras' => 'rechazado',
            'observaciones_pedidos' => $request->motivo
        ]);

        // Send Email Notification to Creator
        try {
            if ($pedido->creador && $pedido->creador->correo) {
                \Illuminate\Support\Facades\Mail::to($pedido->creador->correo)
                    ->send(new \App\Mail\OrderRejectedNotification($pedido, $request->motivo));
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error enviando correo de rechazo de pedido: ' . $e->getMessage());
        }

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
        try {
            $this->permissionService->authorize('cp_pedido.aprobar_gerencia');
            $request->validate([
                'observacion_gerencia' => 'nullable|string',
                'use_stored_signature' => 'nullable|boolean',
                'responsable_aprobacion_firma' => 'nullable|file|image|max:1024',
            ]);

            if (!$request->hasFile('responsable_aprobacion_firma') && !$request->boolean('use_stored_signature')) {
                return ApiResponse::error('Debe proporcionar una firma o usar la guardada.', 400);
            }

            $pedido = CpPedido::find($id);
            if (!$pedido) return ApiResponse::error('Pedido no encontrado', 404);

            $path = null;

            if ($request->boolean('use_stored_signature')) {
                $user = auth('api')->user();
                $originalPath = $user->getAttributes()['firma_digital'] ?? null;

                if (!$originalPath || !Storage::disk('public')->exists($originalPath)) {
                    return ApiResponse::error('No se encontró una firma digital guardada en su perfil.', 400);
                }

                $extension = pathinfo($originalPath, PATHINFO_EXTENSION);
                $filename = 'gerencia_' . time() . '_stored.' . $extension;
                $newPath = 'pedidos_firma/' . $filename;

                Storage::disk('public')->copy($originalPath, $newPath);
                $path = $newPath;
            } elseif ($request->hasFile('responsable_aprobacion_firma')) {
                $file = $request->file('responsable_aprobacion_firma');
                $filename = 'gerencia_' . time() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('pedidos_firma', $filename, 'public');
            }

            $pedido->update([
                'estado_gerencia' => 'aprobado',
                'responsable_aprobacion' => auth('api')->id(),
                'responsable_aprobacion_firma' => $path ? 'storage/' . $path : null, // Store relative path like store()
                'fecha_gerencia' => now(),
                'observacion_gerencia' => $request->observacion_gerencia,
            ]);

            // Send Email Notification to Creator
            try {
                if ($pedido->creador && $pedido->creador->correo) {
                    \Illuminate\Support\Facades\Mail::to($pedido->creador->correo)
                        ->send(new \App\Mail\GerenciaApprovedNotification($pedido));
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Error enviando correo de aprobación gerencia: ' . $e->getMessage());
            }

            return ApiResponse::success($pedido, 'Pedido aprobado por gerencia');
        } catch (\Exception $e) {
            $status = $e->getCode() === 403 ? 403 : 500;
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

        $pedido = CpPedido::find($id);
        if (!$pedido) return response()->json(['error' => 'Pedido no encontrado'], 404);

        $pedido->update([
            'estado_gerencia' => 'rechazado',
            'responsable_aprobacion' => auth('api')->id(),
            'observacion_gerencia' => $request->observacion_gerencia,
        ]);

        // Send Email Notification to Creator
        try {
            if ($pedido->creador && $pedido->creador->correo) {
                \Illuminate\Support\Facades\Mail::to($pedido->creador->correo)
                    ->send(new \App\Mail\GerenciaRejectedNotification($pedido, $request->observacion_gerencia));
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error enviando correo de rechazo de gerencia: ' . $e->getMessage());
        }

        return response()->json(['message' => 'Pedido rechazado por gerencia', 'pedido' => $pedido]);
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
        $pedido = CpPedido::find($id);
        if (!$pedido) return response()->json(['error' => 'Pedido no encontrado'], 404);

        foreach ($request->items as $itemData) {
            CpItemPedido::where('id', $itemData['id'])
                ->where('cp_pedido', $id)
                ->update(['comprado' => $itemData['comprado']]);
        }

        return response()->json(['message' => 'Items actualizados correctamente', 'pedido' => $pedido->load('items')]);
    }
}

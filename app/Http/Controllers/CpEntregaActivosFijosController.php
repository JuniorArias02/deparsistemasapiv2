<?php

namespace App\Http\Controllers;

use App\Models\CpEntregaActivosFijos;
use App\Models\CpEntregaActivosFijosItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Exception;
use OpenApi\Attributes as OA;

class CpEntregaActivosFijosController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    #[OA\Get(
        path: '/api/cp-entrega-activos-fijos',
        tags: ['Entrega de Activos Fijos'],
        summary: 'Listar entregas de activos fijos',
        description: 'Obtiene la lista de entregas de activos fijos con sus relaciones.',
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Lista de entregas'),
            new OA\Response(response: 403, description: 'Prohibido')
        ]
    )]
    public function index()
    {
        return CpEntregaActivosFijos::with([
            'personal',
            'sede',
            'procesoSolicitante',
            'coordinador',
            'items.inventario'
        ])->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    #[OA\Post(
        path: '/api/cp-entrega-activos-fijos',
        tags: ['Entrega de Activos Fijos'],
        summary: 'Crear entrega de activos fijos',
        description: 'Crea una nueva entrega de activos fijos con sus items.',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    required: ['personal_id', 'sede_id', 'proceso_solicitante', 'coordinador_id', 'fecha_entrega', 'items'],
                    properties: [
                        new OA\Property(property: 'personal_id', type: 'integer', description: 'ID del personal que recibe'),
                        new OA\Property(property: 'sede_id', type: 'integer', description: 'ID de la sede'),
                        new OA\Property(property: 'proceso_solicitante', type: 'integer', description: 'ID del proceso solicitante'),
                        new OA\Property(property: 'coordinador_id', type: 'integer', description: 'ID del coordinador'),
                        new OA\Property(property: 'fecha_entrega', type: 'string', format: 'date', description: 'Fecha de entrega'),
                        new OA\Property(property: 'firma_quien_entrega', type: 'string', format: 'binary', description: 'Firma de quien entrega'),
                        new OA\Property(property: 'firma_quien_recibe', type: 'string', format: 'binary', description: 'Firma de quien recibe'),
                        new OA\Property(
                            property: 'items',
                            type: 'array',
                            description: 'Lista de items a entregar',
                            items: new OA\Items(
                                type: 'object',
                                required: ['item_id'],
                                properties: [
                                    new OA\Property(property: 'item_id', type: 'integer', description: 'ID del item de inventario'),
                                    new OA\Property(property: 'es_accesorio', type: 'boolean', description: 'Si es un accesorio'),
                                    new OA\Property(property: 'accesorio_descripcion', type: 'string', description: 'Descripción del accesorio')
                                ]
                            )
                        )
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Entrega creada exitosamente'),
            new OA\Response(response: 422, description: 'Error de validación'),
            new OA\Response(response: 500, description: 'Error del servidor')
        ]
    )]
    public function store(Request $request)
    {
        $validated = $request->validate([
            'personal_id' => 'required|integer|exists:personal,id',
            'sede_id' => 'required|integer|exists:sedes,id',
            'proceso_solicitante' => 'required|integer|exists:dependencias_sedes,id',
            'coordinador_id' => 'required|integer|exists:personal,id',
            'fecha_entrega' => 'required|date',
            'firma_quien_entrega' => 'nullable|file|mimes:png,jpg|max:1024',
            'firma_quien_recibe' => 'nullable|file|mimes:png,jpg|max:1024',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|integer|exists:inventario,id',
            'items.*.es_accesorio' => 'nullable|boolean',
            'items.*.accesorio_descripcion' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            // Handle file uploads
            $firmaEntregaPath = null;
            $firmaRecibePath = null;

            if ($request->hasFile('firma_quien_entrega')) {
                $file = $request->file('firma_quien_entrega');
                $filename = time() . '_firma_entrega.' . $file->getClientOriginalExtension();
                $firmaEntregaPath = $file->storeAs('entrega_activos_firma', $filename, 'public');
            }

            if ($request->hasFile('firma_quien_recibe')) {
                $file = $request->file('firma_quien_recibe');
                $filename = time() . '_firma_recibe.' . $file->getClientOriginalExtension();
                $firmaRecibePath = $file->storeAs('entrega_activos_firma', $filename, 'public');
            }

            /** @var CpEntregaActivosFijos $entrega */
            $entrega = CpEntregaActivosFijos::create([
                'personal_id' => $validated['personal_id'],
                'sede_id' => $validated['sede_id'],
                'proceso_solicitante' => $validated['proceso_solicitante'],
                'coordinador_id' => $validated['coordinador_id'],
                'fecha_entrega' => $validated['fecha_entrega'],
                'firma_quien_entrega' => $firmaEntregaPath ? 'storage/' . $firmaEntregaPath : null,
                'firma_quien_recibe' => $firmaRecibePath ? 'storage/' . $firmaRecibePath : null,
            ]);

            foreach ($validated['items'] as $item) {
                CpEntregaActivosFijosItem::create([
                    'item_id' => $item['item_id'],
                    'es_accesorio' => $item['es_accesorio'] ?? false,
                    'accesorio_descripcion' => $item['accesorio_descripcion'] ?? null,
                    'entrega_activos_id' => $entrega->id,
                ]);
            }

            DB::commit();

            return response()->json([
                'mensaje' => 'Entrega de activos fijos creada exitosamente',
                'objeto' => $entrega->load('items'),
                'status' => 201
            ], 201);

        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'mensaje' => 'Error al crear la entrega: ' . $e->getMessage(),
                'objeto' => null,
                'status' => 500
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    #[OA\Get(
        path: '/api/cp-entrega-activos-fijos/{id}',
        tags: ['Entrega de Activos Fijos'],
        summary: 'Obtener entrega específica',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Entrega encontrada'),
            new OA\Response(response: 404, description: 'Entrega no encontrada')
        ]
    )]
    public function show(string $id)
    {
        $entrega = CpEntregaActivosFijos::with([
            'personal',
            'sede',
            'procesoSolicitante',
            'coordinador',
            'items.inventario'
        ])->find($id);

        if (!$entrega) {
            return response()->json([
                'mensaje' => 'Entrega no encontrada',
                'objeto' => null,
                'status' => 404
            ], 404);
        }

        return response()->json([
            'mensaje' => 'Entrega encontrada',
            'objeto' => $entrega,
            'status' => 200
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    #[OA\Put(
        path: '/api/cp-entrega-activos-fijos/{id}',
        tags: ['Entrega de Activos Fijos'],
        summary: 'Actualizar entrega',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Entrega actualizada'),
            new OA\Response(response: 404, description: 'Entrega no encontrada')
        ]
    )]
    public function update(Request $request, string $id)
    {
        $entrega = CpEntregaActivosFijos::find($id);

        if (!$entrega) {
            return response()->json([
                'mensaje' => 'Entrega no encontrada',
                'objeto' => null,
                'status' => 404
            ], 404);
        }

        $validated = $request->validate([
            'personal_id' => 'sometimes|integer|exists:personal,id',
            'sede_id' => 'sometimes|integer|exists:sedes,id',
            'proceso_solicitante' => 'sometimes|integer|exists:dependencias_sedes,id',
            'coordinador_id' => 'sometimes|integer|exists:personal,id',
            'fecha_entrega' => 'sometimes|date',
            'firma_quien_entrega' => 'nullable|file|mimes:png,jpg|max:1024',
            'firma_quien_recibe' => 'nullable|file|mimes:png,jpg|max:1024',
        ]);

        try {
            DB::beginTransaction();

            // Handle file uploads
            if ($request->hasFile('firma_quien_entrega')) {
                // Delete old file if exists
                if ($entrega->firma_quien_entrega) {
                    Storage::disk('public')->delete(str_replace('storage/', '', $entrega->firma_quien_entrega));
                }
                $file = $request->file('firma_quien_entrega');
                $filename = time() . '_firma_entrega.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('entrega_activos_firma', $filename, 'public');
                $entrega->firma_quien_entrega = 'storage/' . $path;
            }

            if ($request->hasFile('firma_quien_recibe')) {
                // Delete old file if exists
                if ($entrega->firma_quien_recibe) {
                    Storage::disk('public')->delete(str_replace('storage/', '', $entrega->firma_quien_recibe));
                }
                $file = $request->file('firma_quien_recibe');
                $filename = time() . '_firma_recibe.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('entrega_activos_firma', $filename, 'public');
                $entrega->firma_quien_recibe = 'storage/' . $path;
            }

            $entrega->fill($validated);
            // Protect signatures from being nullified if not sent (though fill shouldn't touch them if not in validated array, but to be safe)
            // Actually, if validation passes and keys are not present, fill won't touch them.
            // But if keys are present and null (e.g. from FormData), we might need to be careful.
            // Our service only appends if file is present. if null, it might send null.
            // 'nullable' validation allows null.
            // However, we handled file logic above manually.
            // We should unset firm keys from validated to avoid overwriting with null if they were processed manually or if we want to preserve old ones when no new file is sent.
            unset($validated['firma_quien_entrega']);
            unset($validated['firma_quien_recibe']);
            
            $entrega->update($validated);

            DB::commit();

            return response()->json([
                'mensaje' => 'Entrega actualizada exitosamente',
                'objeto' => $entrega->load('items'),
                'status' => 200
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'mensaje' => 'Error al actualizar la entrega: ' . $e->getMessage(),
                'objeto' => null,
                'status' => 500
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    #[OA\Delete(
        path: '/api/cp-entrega-activos-fijos/{id}',
        tags: ['Entrega de Activos Fijos'],
        summary: 'Eliminar entrega',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Entrega eliminada'),
            new OA\Response(response: 404, description: 'Entrega no encontrada')
        ]
    )]
    public function destroy(string $id)
    {
        $entrega = CpEntregaActivosFijos::find($id);

        if (!$entrega) {
            return response()->json([
                'mensaje' => 'Entrega no encontrada',
                'objeto' => null,
                'status' => 404
            ], 404);
        }

        try {
            DB::beginTransaction();

            // Delete associated items
            CpEntregaActivosFijosItem::where('entrega_activos_id', $id)->delete();

            // Delete signature files if they exist
            if ($entrega->firma_quien_entrega) {
                Storage::disk('public')->delete(str_replace('storage/', '', $entrega->firma_quien_entrega));
            }
            if ($entrega->firma_quien_recibe) {
                Storage::disk('public')->delete(str_replace('storage/', '', $entrega->firma_quien_recibe));
            }

            $entrega->delete();

            DB::commit();

            return response()->json([
                'mensaje' => 'Entrega eliminada exitosamente',
                'objeto' => null,
                'status' => 200
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'mensaje' => 'Error al eliminar la entrega: ' . $e->getMessage(),
                'objeto' => null,
                'status' => 500
            ], 500);
        }
    }
}

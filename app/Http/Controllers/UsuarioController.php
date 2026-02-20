<?php

namespace App\Http\Controllers;

use App\Services\UsuarioService;
use App\Responses\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use OpenApi\Attributes as OA;

class UsuarioController extends Controller
{
    public function __construct(protected UsuarioService $usuarioService) {}

    #[OA\Get(
        path: '/api/usuarios',
        tags: ['Usuarios'],
        summary: 'Listar usuarios',
        description: 'Obtiene una lista de todos los usuarios registrados.',
        operationId: 'usuariosIndex',
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Lista de usuarios',
                content: new OA\JsonContent(
                    ref: '#/components/schemas/ApiResponse',
                    example: [
                        'mensaje' => 'Lista de usuarios obtenida exitosamente',
                        'objeto' => [
                            ['id' => 1, 'usuario' => 'admin', 'rol' => []]
                        ],
                        'status' => 200
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'No autenticado',
                content: new OA\JsonContent(ref: '#/components/schemas/ApiResponse')
            )
        ]
    )]
    public function index()
    {
        $usuarios = $this->usuarioService->getAll();
        return ApiResponse::success($usuarios, 'Lista de usuarios obtenida exitosamente');
    }

    public function show($id)
    {
        try {
            $usuario = $this->usuarioService->getById($id);
            return ApiResponse::success($usuario, 'Usuario obtenido exitosamente');
        } catch (\Exception $e) {
            return ApiResponse::error('Usuario no encontrado', 404);
        }
    }

    #[OA\Post(
        path: '/api/usuarios',
        tags: ['Usuarios'],
        summary: 'Crear usuario',
        description: 'Registra un nuevo usuario en el sistema.',
        operationId: 'usuariosStore',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['usuario', 'contrasena'],
                properties: [
                    new OA\Property(property: 'nombre_completo', type: 'string', example: 'Juan Perez'),
                    new OA\Property(property: 'usuario', type: 'string', example: 'juan.perez'),
                    new OA\Property(property: 'contrasena', type: 'string', format: 'password', example: 'secret123'),
                    new OA\Property(property: 'correo', type: 'string', format: 'email', example: 'juan@example.com'),
                    new OA\Property(property: 'rol_id', type: 'integer', example: 1),
                    new OA\Property(property: 'sede_id', type: 'integer', example: 1),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Usuario creado',
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
        $validated = $request->validate([
            'nombre_completo' => 'nullable|string|max:100',
            'usuario' => 'nullable|string|max:50|unique:usuarios,usuario',
            'contrasena' => 'nullable|string|min:6',
            'rol_id' => 'nullable|exists:rol,id',
            'correo' => 'nullable|email|max:60',
            'telefono' => 'nullable|string|max:15',
            'estado' => 'boolean',
            'sede_id' => 'nullable|exists:sedes,id',
            'firma_digital' => 'nullable|string|max:260',
        ]);

        $usuario = $this->usuarioService->create($validated);
        return ApiResponse::success($usuario, 'Usuario creado exitosamente', 201);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'nombre_completo' => 'nullable|string|max:100',
            'usuario' => ['nullable', 'string', 'max:50', Rule::unique('usuarios')->ignore($id)],
            'contrasena' => 'nullable|string|min:6',
            'rol_id' => 'nullable|exists:rol,id',
            'correo' => 'nullable|email|max:60',
            'telefono' => 'nullable|string|max:15',
            'estado' => 'boolean',
            'sede_id' => 'nullable|exists:sedes,id',
            'firma_digital' => 'nullable|string|max:260',
        ]);

        try {
            $usuario = $this->usuarioService->update($id, $validated);
            return ApiResponse::success($usuario, 'Usuario actualizado exitosamente');
        } catch (\Exception $e) {
            return ApiResponse::error('Error al actualizar usuario: ' . $e->getMessage(), 404);
        }
    }

    public function destroy($id)
    {
        try {
            $this->usuarioService->delete($id);
            return ApiResponse::success(null, 'Usuario eliminado exitosamente');
        } catch (\Exception $e) {
            return ApiResponse::error('Error al eliminar usuario', 404);
        }
    }

    /**
     * Get users whose role has a specific permission.
     */
    public function getByPermission($permiso)
    {
        $usuarios = \App\Models\Usuario::whereHas('rol.permisos', function ($query) use ($permiso) {
            $query->where('nombre', $permiso);
        })->with('rol')->get();

        return ApiResponse::success($usuarios, 'Usuarios con permiso: ' . $permiso);
    }
}

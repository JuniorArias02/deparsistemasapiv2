<?php

namespace App\Http\Controllers;

use App\DTOs\Auth\LoginDTO;
use App\Services\AuthService;
use App\Responses\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log; // For error logging
use App\Mail\LoginAlertMail;
use App\Http\Controllers\Controller;
use OpenApi\Attributes as OA;

class AuthController extends Controller
{
    public function __construct(
        protected AuthService $authService
    ) {}

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    #[OA\Post(
        path: '/api/auth/login',
        tags: ['Autenticación'],
        summary: 'Iniciar sesión',
        description: 'Autentica al usuario y devuelve un token JWT.',
        operationId: 'authLogin',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['usuario', 'contrasena'],
                properties: [
                    new OA\Property(property: 'usuario', type: 'string', example: 'junior@house'),
                    new OA\Property(property: 'contrasena', type: 'string', format: 'password', example: 'qweasdzxc')
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Login exitoso',
                content: new OA\JsonContent(
                    ref: '#/components/schemas/ApiResponse',
                    example: [
                        'mensaje' => 'Login exitoso',
                        'objeto' => [
                            'access_token' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...',
                            'token_type' => 'bearer',
                            'expires_in' => 3600
                        ],
                        'status' => 200
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Credenciales inválidas',
                content: new OA\JsonContent(ref: '#/components/schemas/ApiResponse')
            )
        ]
    )]
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'usuario' => 'required|string',
            'contrasena' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error('Error de validación', 422, $validator->errors());
        }

        $dto = LoginDTO::fromRequest($request);
        $result = $this->authService->login($dto);

        if (! $result) {
            return ApiResponse::error('Credenciales inválidas', 401);
        }

        if ($result) {
            try {
                $user = auth('api')->user();
                if ($user && $user->email) {
                    Mail::to($user->email)->send(new LoginAlertMail($user, $request));
                }
            } catch (\Exception $e) {
                Log::error('Error sending login alert email: ' . $e->getMessage());
            }
        }

        return ApiResponse::success($result, 'Login exitoso');
    }

    #[OA\Post(
        path: '/api/auth/me',
        tags: ['Autenticación'],
        summary: 'Obtener usuario autenticado',
        description: 'Devuelve los datos del usuario actual.',
        operationId: 'authMe',
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Datos del usuario',
                content: new OA\JsonContent(ref: '#/components/schemas/ApiResponse')
            ),
            new OA\Response(
                response: 401,
                description: 'No autenticado',
                content: new OA\JsonContent(ref: '#/components/schemas/ApiResponse')
            )
        ]
    )]
    public function me()
    {
        return ApiResponse::success($this->authService->me(), 'Datos del usuario');
    }

    public function logout()
    {
        $this->authService->logout();
        return ApiResponse::success([], 'Sesión cerrada exitosamente');
    }

    public function sendResetCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'usuario' => 'required|string',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error('Error de validación', 422, $validator->errors());
        }

        $result = $this->authService->sendResetCode($request->usuario);

        if (!$result['success']) {
            return ApiResponse::error($result['message'], 404);
        }

        return ApiResponse::success([], $result['message']);
    }

    public function verifyResetCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'usuario' => 'required|string',
            'codigo' => 'required|string',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error('Error de validación', 422, $validator->errors());
        }

        $result = $this->authService->verifyResetCode($request->usuario, $request->codigo);

        if (!$result['success']) {
            return ApiResponse::error($result['message'], 400);
        }

        return ApiResponse::success([], $result['message']);
    }

    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'usuario' => 'required|string',
            'codigo' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error('Error de validación', 422, $validator->errors());
        }

        $result = $this->authService->resetPassword($request->usuario, $request->codigo, $request->password);

        if (!$result['success']) {
            return ApiResponse::error($result['message'], 400);
        }

        return ApiResponse::success([], $result['message']);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Responses\ApiResponse;
use App\Models\Usuario;
use OpenApi\Attributes as OA;

class ProfileController extends Controller
{
    /**
     * Update user profile information.
     */
    #[OA\Post(
        path: '/api/profile/update',
        tags: ['Perfil'],
        summary: 'Actualizar perfil',
        description: 'Actualiza la información básica del usuario autenticado.',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'nombre_completo', type: 'string'),
                    new OA\Property(property: 'email', type: 'string', format: 'email'),
                    new OA\Property(property: 'telefono', type: 'string'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Perfil actualizado exitosamente',
                content: new OA\JsonContent(ref: '#/components/schemas/ApiResponse')
            )
        ]
    )]
    public function update(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return ApiResponse::error('Usuario no autenticado', 401);
        }

        /** @var Usuario $user */
        $validator = Validator::make($request->all(), [
            'nombre_completo' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:usuarios,correo,' . $user->id,
            'telefono' => 'sometimes|nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error('Error de validación', 422, $validator->errors());
        }

        $data = $request->only(['nombre_completo', 'telefono']);
        if ($request->has('email')) {
            $data['correo'] = $request->input('email');
        }

        $user->update($data);

        return ApiResponse::success($user, 'Perfil actualizado exitosamente');
    }

    /**
     * Change user password.
     */
    #[OA\Post(
        path: '/api/profile/change-password',
        tags: ['Perfil'],
        summary: 'Cambiar contraseña',
        description: 'Actualiza la contraseña del usuario autenticado.',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['current_password', 'new_password', 'new_password_confirmation'],
                properties: [
                    new OA\Property(property: 'current_password', type: 'string', format: 'password'),
                    new OA\Property(property: 'new_password', type: 'string', format: 'password'),
                    new OA\Property(property: 'new_password_confirmation', type: 'string', format: 'password'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Contraseña actualizada exitosamente',
                content: new OA\JsonContent(ref: '#/components/schemas/ApiResponse')
            )
        ]
    )]
    public function changePassword(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return ApiResponse::error('Usuario no autenticado', 401);
        }

        /** @var Usuario $user */
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error('Error de validación', 422, $validator->errors());
        }

        if (!Hash::check($request->current_password, $user->contrasena)) {
            return ApiResponse::error('La contraseña actual es incorrecta', 400);
        }

        $user->contrasena = Hash::make($request->new_password);
        $user->save();

        return ApiResponse::success([], 'Contraseña actualizada exitosamente');
    }

    /**
     * Upload user signature.
     */
    #[OA\Post(
        path: '/api/profile/upload-signature',
        tags: ['Perfil'],
        summary: 'Subir firma',
        description: 'Sube y actualiza la firma del usuario autenticado.',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'firma', type: 'string', format: 'binary')
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Firma actualizada exitosamente',
                content: new OA\JsonContent(ref: '#/components/schemas/ApiResponse')
            )
        ]
    )]
    public function uploadSignature(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return ApiResponse::error('Usuario no autenticado', 401);
        }

        /** @var Usuario $user */
        $validator = Validator::make($request->all(), [
            'firma' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error('Error de validación', 422, $validator->errors());
        }

        if ($request->hasFile('firma')) {
            $usuarioService = app(\App\Services\UsuarioService::class);
            $path = $usuarioService->handleSignatureUpload($user, $request->file('firma'));
            $user->firma_digital = $path;
            $user->save();

            return ApiResponse::success(['firma_url' => url('api/storage/' . $path)], 'Firma actualizada exitosamente');
        }

        return ApiResponse::error('No se ha subido ningún archivo', 400);
    }
    /**
     * Upload user profile photo.
     */
    #[OA\Post(
        path: '/api/profile/upload-photo',
        tags: ['Perfil'],
        summary: 'Subir foto de perfil',
        description: 'Sube y actualiza la foto de perfil del usuario autenticado.',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'foto', type: 'string', format: 'binary')
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Foto actualizada exitosamente',
                content: new OA\JsonContent(ref: '#/components/schemas/ApiResponse')
            )
        ]
    )]
    public function uploadPhoto(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return ApiResponse::error('Usuario no autenticado', 401);
        }

        /** @var Usuario $user */
        $validator = Validator::make($request->all(), [
            'foto' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error('Error de validación', 422, $validator->errors());
        }

        if ($request->hasFile('foto')) {
            // Delete old photo if exists
            if ($user->foto_usuario && Storage::exists('public/' . $user->getRawOriginal('foto_usuario'))) {
                Storage::delete('public/' . $user->getRawOriginal('foto_usuario'));
            }

            $path = $request->file('foto')->store('fotoPerfil', 'public');
            $user->foto_usuario = $path;
            $user->save();

            return ApiResponse::success(['foto_url' => url('api/storage/' . $path)], 'Foto actualizada exitosamente');
        }

        return ApiResponse::error('No se ha subido ningún archivo', 400);
    }
    /**
     * Delete user profile photo.
     */
    #[OA\Post(
        path: '/api/profile/delete-photo',
        tags: ['Perfil'],
        summary: 'Eliminar foto de perfil',
        description: 'Elimina la foto de perfil del usuario autenticado.',
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Foto eliminada exitosamente',
                content: new OA\JsonContent(ref: '#/components/schemas/ApiResponse')
            )
        ]
    )]
    public function deletePhoto(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return ApiResponse::error('Usuario no autenticado', 401);
        }

        /** @var Usuario $user */
        if ($user->foto_usuario) {
            if (Storage::exists('public/' . $user->getRawOriginal('foto_usuario'))) {
                Storage::delete('public/' . $user->getRawOriginal('foto_usuario'));
            }
            $user->foto_usuario = null;
            $user->save();
            return ApiResponse::success([], 'Foto eliminada exitosamente');
        }

        return ApiResponse::error('El usuario no tiene foto de perfil', 404);
    }
}

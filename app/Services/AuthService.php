<?php

namespace App\Services;

use App\DTOs\Auth\LoginDTO;
use App\Repositories\AuthRepository;

class AuthService
{
    public function __construct(
        protected AuthRepository $authRepository
    ) {}

    public function login(LoginDTO $dto)
    {
        $credentials = [
            'usuario' => $dto->usuario,
            'password' => $dto->password
        ];

        $token = $this->authRepository->attempt($credentials);

        if (! $token) {
            return null;
        }

        return $this->respondWithToken($token);
    }

    public function me()
    {
        $user = $this->authRepository->user();
        return $user ? $user->load('rol.permisos') : null;
    }

    public function logout()
    {
        $this->authRepository->logout();
    }

    protected function respondWithToken($token)
    {
        return [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60
        ];
    }

    public function sendResetCode(string $usuario)
    {
        $user = \App\Models\Usuario::where('usuario', $usuario)->orWhere('correo', $usuario)->first();

        if (!$user) {
            return ['success' => false, 'message' => 'Usuario no encontrado'];
        }

        if (!$user->correo) {
            return ['success' => false, 'message' => 'El usuario no tiene un correo asignado'];
        }

        // Generate 6 digit code
        $code = rand(100000, 999999);

        // Store in sec_codigo_verificacion
        \App\Models\SecCodigoVerificacion::create([
            'codigo' => $code,
            'id_usuario' => $user->id,
            'creado' => now(),
            'fecha_expiracion' => now()->addMinutes(15),
            'consumido' => 0
        ]);

        try {
            \Illuminate\Support\Facades\Mail::to($user->correo)
                ->send(new \App\Mail\ResetPasswordNotification($user, $code));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Error sending reset email: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error al enviar el correo. Intente más tarde.'];
        }

        return ['success' => true, 'message' => 'Código enviado a su correo'];
    }

    public function verifyResetCode(string $usuario, string $code)
    {
        $user = \App\Models\Usuario::where('usuario', $usuario)->orWhere('correo', $usuario)->first();

        if (!$user) {
            return ['success' => false, 'message' => 'Usuario no encontrado'];
        }

        $verification = \App\Models\SecCodigoVerificacion::where('id_usuario', $user->id)
            ->where('codigo', $code)
            ->where('consumido', 0)
            ->where('fecha_expiracion', '>', now())
            ->first();

        if (!$verification) {
            return ['success' => false, 'message' => 'Código inválido o expirado'];
        }

        return ['success' => true, 'message' => 'Código válido'];
    }

    public function resetPassword(string $usuario, string $code, string $password)
    {
        $user = \App\Models\Usuario::where('usuario', $usuario)->orWhere('correo', $usuario)->first();

        if (!$user) {
            return ['success' => false, 'message' => 'Usuario no encontrado'];
        }

        // Verify code again & Mark as consumed
        $verification = \App\Models\SecCodigoVerificacion::where('id_usuario', $user->id)
            ->where('codigo', $code)
            ->where('consumido', 0)
            ->where('fecha_expiracion', '>', now())
            ->first();

        if (!$verification) {
            return ['success' => false, 'message' => 'Código inválido terminando proceso.'];
        }

        // Update password
        $user->contrasena = bcrypt($password);
        $user->save();

        // Mark code as consumed using update() to avoid timestamp issues if model timestamps are false
        \App\Models\SecCodigoVerificacion::where('id', $verification->id)->update([
            'consumido' => 1,
            'fecha_activacion' => now()
        ]);

        return ['success' => true, 'message' => 'Contraseña actualizada correctamente'];
    }
}

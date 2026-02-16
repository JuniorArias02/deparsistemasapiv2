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
        return $user ? $user->load('rol') : null;
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

        // Generate 6 digit code
        $code = rand(100000, 999999);
        $user->codigo_verificacion = $code;
        $user->codigo_verificacion_expira_at = now()->addMinutes(15);
        $user->save();

        // Simulate sending email
        // In production: Mail::to($user->correo)->send(new ResetCodeMail($code));
        \Illuminate\Support\Facades\Log::info("Reset code for {$user->usuario}: {$code}");

        return ['success' => true, 'message' => 'Código enviado (Revisa logs)'];
    }

    public function verifyResetCode(string $usuario, string $code)
    {
        $user = \App\Models\Usuario::where('usuario', $usuario)->orWhere('correo', $usuario)->first();

        if (!$user) {
            return ['success' => false, 'message' => 'Usuario no encontrado'];
        }

        if ($user->codigo_verificacion !== $code) {
            return ['success' => false, 'message' => 'Código inválido'];
        }

        if (now()->greaterThan($user->codigo_verificacion_expira_at)) {
            return ['success' => false, 'message' => 'El código ha expirado'];
        }

        return ['success' => true, 'message' => 'Código válido'];
    }

    public function resetPassword(string $usuario, string $code, string $password)
    {
        $user = \App\Models\Usuario::where('usuario', $usuario)->orWhere('correo', $usuario)->first();

        if (!$user) {
            return ['success' => false, 'message' => 'Usuario no encontrado'];
        }

        // Verify code again just in case
        $verify = $this->verifyResetCode($usuario, $code);
        if (!$verify['success']) {
            return $verify;
        }

        $user->contrasena = bcrypt($password);
        $user->codigo_verificacion = null;
        $user->codigo_verificacion_expira_at = null;
        $user->save();

        return ['success' => true, 'message' => 'Contraseña actualizada correctamente'];
    }
}

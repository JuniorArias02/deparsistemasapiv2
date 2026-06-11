<?php

namespace App\Modules\Autenticacion\Application\UseCases\Auth;

use App\DTOs\Auth\LoginDTO;
use App\Repositories\AuthRepository;

class LoginUseCase
{
    public function __construct(
        protected AuthRepository $authRepository
    ) {}

    public function execute(LoginDTO $dto)
    {
        $credentials = [
            'usuario' => $dto->usuario,
            'password' => $dto->password
        ];

        $token = $this->authRepository->attempt($credentials);

        if (! $token) {
            return null;
        }

        $user = $this->authRepository->user();

        if ($user && $user->estado == 0) {
            $this->authRepository->logout();
            return ['error' => 'Usuario deshabilitado en el sistema', 'status' => 403];
        }

        return $this->respondWithToken($token);
    }

    protected function respondWithToken($token)
    {
        return [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60
        ];
    }
}

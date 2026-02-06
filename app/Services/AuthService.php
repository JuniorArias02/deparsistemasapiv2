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
        return $this->authRepository->user();
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
}

<?php

namespace App\Modules\Autenticacion\Application\UseCases\Auth;

use App\Repositories\AuthRepository;

class GetAuthUserUseCase
{
    public function __construct(
        protected AuthRepository $authRepository
    ) {}

    public function execute()
    {
        $user = $this->authRepository->user();
        return $user ? $user->load('rol.permisos') : null;
    }
}

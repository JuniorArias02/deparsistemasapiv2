<?php

namespace App\Modules\Autenticacion\Application\UseCases\Auth;

use App\Repositories\AuthRepository;

class LogoutUseCase
{
    public function __construct(
        protected AuthRepository $authRepository
    ) {}

    public function execute()
    {
        $this->authRepository->logout();
    }
}

<?php

namespace App\DTOs\Auth;

class LoginDTO
{
    public function __construct(
        public readonly string $usuario,
        public readonly string $password
    ) {}

    public static function fromRequest($request): self
    {
        return new self(
            usuario: $request->input('usuario'),
            password: $request->input('contrasena')
        );
    }
}

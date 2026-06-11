<?php

namespace App\Modules\Autenticacion\Application\UseCases\Auth;

use App\Models\Usuario;
use App\Models\SecCodigoVerificacion;

class VerifyResetCodeUseCase
{
    public function execute(string $usuario, string $code)
    {
        $user = Usuario::where('usuario', $usuario)->orWhere('correo', $usuario)->first();

        if (!$user) {
            return ['success' => false, 'message' => 'Usuario no encontrado'];
        }

        $verification = SecCodigoVerificacion::where('id_usuario', $user->id)
            ->where('codigo', $code)
            ->where('consumido', 0)
            ->where('fecha_expiracion', '>', now())
            ->first();

        if (!$verification) {
            return ['success' => false, 'message' => 'Código inválido o expirado'];
        }

        return ['success' => true, 'message' => 'Código válido'];
    }
}

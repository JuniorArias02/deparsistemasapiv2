<?php

namespace App\Modules\Autenticacion\Application\UseCases\Auth;

use App\Models\Usuario;
use App\Models\SecCodigoVerificacion;

class ResetPasswordUseCase
{
    public function execute(string $usuario, string $code, string $password)
    {
        $user = Usuario::where('usuario', $usuario)->orWhere('correo', $usuario)->first();

        if (!$user) {
            return ['success' => false, 'message' => 'Usuario no encontrado'];
        }

        // Verify code again & Mark as consumed
        $verification = SecCodigoVerificacion::where('id_usuario', $user->id)
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
        SecCodigoVerificacion::where('id', $verification->id)->update([
            'consumido' => 1,
            'fecha_activacion' => now()
        ]);

        return ['success' => true, 'message' => 'Contraseña actualizada correctamente'];
    }
}

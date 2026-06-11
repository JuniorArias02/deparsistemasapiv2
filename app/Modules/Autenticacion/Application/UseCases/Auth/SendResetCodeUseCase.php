<?php

namespace App\Modules\Autenticacion\Application\UseCases\Auth;

use App\Models\Usuario;
use App\Models\SecCodigoVerificacion;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\ResetPasswordNotification;

class SendResetCodeUseCase
{
    public function execute(string $usuario)
    {
        $user = Usuario::where('usuario', $usuario)->orWhere('correo', $usuario)->first();

        if (!$user) {
            return ['success' => false, 'message' => 'Usuario no encontrado'];
        }

        if (!$user->correo) {
            return ['success' => false, 'message' => 'El usuario no tiene un correo asignado'];
        }

        // Generate 6 digit code
        $code = rand(100000, 999999);

        // Store in sec_codigo_verificacion
        SecCodigoVerificacion::create([
            'codigo' => $code,
            'id_usuario' => $user->id,
            'creado' => now(),
            'fecha_expiracion' => now()->addMinutes(15),
            'consumido' => 0
        ]);

        try {
            Mail::to($user->correo)
                ->send(new ResetPasswordNotification($user, $code));
        } catch (\Exception $e) {
            Log::error("Error sending reset email: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error al enviar el correo. Intente más tarde.'];
        }

        return ['success' => true, 'message' => 'Código enviado a su correo'];
    }
}

<?php

namespace App\Modules\Autenticacion\Application\UseCases\Profile;

use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;

class ChangePasswordUseCase
{
    public function execute(Usuario $user, string $currentPassword, string $newPassword)
    {
        if (!Hash::check($currentPassword, $user->contrasena)) {
            return ['success' => false, 'message' => 'La contraseña actual es incorrecta'];
        }

        $user->contrasena = Hash::make($newPassword);
        $user->save();

        return ['success' => true];
    }
}

<?php

namespace App\Modules\Autenticacion\Application\UseCases\Profile;

use App\Models\Usuario;

class UpdateProfileUseCase
{
    public function execute(Usuario $user, array $data)
    {
        $updateData = [];
        if (isset($data['nombre_completo'])) {
            $updateData['nombre_completo'] = $data['nombre_completo'];
        }
        if (isset($data['telefono'])) {
            $updateData['telefono'] = $data['telefono'];
        }
        if (isset($data['email'])) {
            $updateData['correo'] = $data['email'];
        }

        $user->update($updateData);

        return $user;
    }
}

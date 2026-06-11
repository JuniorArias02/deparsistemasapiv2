<?php

namespace App\Modules\Autenticacion\Application\UseCases\Usuario;

use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;

class CrearUsuarioUseCase
{
    public function execute(array $data)
    {
        if (isset($data['contrasena'])) {
            $data['contrasena'] = Hash::make($data['contrasena']);
        }

        $usuario = new Usuario($data);

        if (isset($data['firma_file']) && $data['firma_file'] instanceof \Illuminate\Http\UploadedFile) {
            $usuario->firma_digital = $data['firma_file']->store('signatures', 'public');
            unset($data['firma_file']);
        }

        $usuario->save();
        return $usuario;
    }
}

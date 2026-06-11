<?php

namespace App\Modules\Autenticacion\Application\UseCases\Profile;

use App\Models\Usuario;
use Illuminate\Support\Facades\Storage;

class DeletePhotoUseCase
{
    public function execute(Usuario $user)
    {
        if ($user->foto_usuario) {
            if (Storage::exists('public/' . $user->getRawOriginal('foto_usuario'))) {
                Storage::delete('public/' . $user->getRawOriginal('foto_usuario'));
            }
            $user->foto_usuario = null;
            $user->save();
            return true;
        }

        return false;
    }
}

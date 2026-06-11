<?php

namespace App\Modules\Autenticacion\Application\UseCases\Profile;

use App\Models\Usuario;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class UploadPhotoUseCase
{
    public function execute(Usuario $user, UploadedFile $file)
    {
        if ($user->foto_usuario && Storage::exists('public/' . $user->getRawOriginal('foto_usuario'))) {
            Storage::delete('public/' . $user->getRawOriginal('foto_usuario'));
        }

        $path = $file->store('fotoPerfil', 'public');
        $user->foto_usuario = $path;
        $user->save();

        return $path;
    }
}

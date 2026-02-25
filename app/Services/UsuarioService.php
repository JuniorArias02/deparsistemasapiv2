<?php

namespace App\Services;

use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UsuarioService
{
    public function getAll()
    {
        return Usuario::with(['rol', 'sede'])->get();
    }

    public function getById($id)
    {
        return Usuario::with(['rol', 'sede'])->findOrFail($id);
    }

    public function create(array $data)
    {
        if (isset($data['contrasena'])) {
            $data['contrasena'] = Hash::make($data['contrasena']);
        }

        // Create temporary user to handle storage if needed, or just store and assign
        $usuario = new Usuario($data);

        if (isset($data['firma_file']) && $data['firma_file'] instanceof \Illuminate\Http\UploadedFile) {
            $usuario->firma_digital = $data['firma_file']->store('signatures', 'public');
            unset($data['firma_file']);
        }

        $usuario->save();
        return $usuario;
    }

    public function update($id, array $data)
    {
        $usuario = Usuario::findOrFail($id);

        if (isset($data['contrasena']) && !empty($data['contrasena'])) {
            $data['contrasena'] = Hash::make($data['contrasena']);
        } else {
            unset($data['contrasena']);
        }

        // Handle signature upload if present as an uploaded file
        if (isset($data['firma_file']) && $data['firma_file'] instanceof \Illuminate\Http\UploadedFile) {
            $data['firma_digital'] = $this->handleSignatureUpload($usuario, $data['firma_file']);
            unset($data['firma_file']);
        }

        $usuario->update($data);
        return $usuario->refresh();
    }

    /**
     * Handle the upload of a digital signature and delete the old one.
     */
    public function handleSignatureUpload(Usuario $usuario, $file)
    {
        // Delete old signature if exists
        $this->deleteOldFile($usuario->getRawOriginal('firma_digital'));

        return $file->store('signatures', 'public');
    }

    /**
     * Delete a file from public storage if it exists.
     */
    public function deleteOldFile($path)
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    public function delete($id)
    {
        $usuario = Usuario::findOrFail($id);
        $usuario->delete();
        return true;
    }
}

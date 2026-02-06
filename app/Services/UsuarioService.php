<?php

namespace App\Services;

use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;

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
        return Usuario::create($data);
    }

    public function update($id, array $data)
    {
        $usuario = Usuario::findOrFail($id);

        if (isset($data['contrasena'])) {
            $data['contrasena'] = Hash::make($data['contrasena']);
        }

        $usuario->update($data);
        return $usuario->refresh();
    }

    public function delete($id)
    {
        $usuario = Usuario::findOrFail($id);
        $usuario->delete();
        return true;
    }
}

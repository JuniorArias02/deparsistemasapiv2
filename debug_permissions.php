<?php

use App\Models\Usuario;
use App\Models\Rol;

$user = Usuario::where('usuario', 'junior@house')->first();
echo "Usuario: " . $user->usuario . "\n";
echo "Rol ID: " . $user->rol_id . "\n";

$rol = Rol::with('permisos')->find($user->rol_id);
echo "Rol Nombre: " . $rol->nombre . "\n";
echo "Permisos count: " . $rol->permisos->count() . "\n";

echo "Has sede.create? " . ($rol->permisos->contains('nombre', 'sede.create') ? 'YES' : 'NO') . "\n";

// List all permissions
foreach($rol->permisos as $p) {
    echo "- " . $p->nombre . "\n";
}

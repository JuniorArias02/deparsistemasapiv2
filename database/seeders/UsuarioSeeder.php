<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Exception;

class UsuarioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rol = DB::table('rol')->where('nombre', 'administrador web')->first();
        if (!$rol) {
            // Fallback or error? Assuming RolSeeder ran first.
            // Let's create it if missing or just warn.
            // For now, assume it exists.
            throw new Exception("Rol 'administrador web' not found. Please run RolSeeder first.");
        }

        $sede = DB::table('sedes')->where('nombre', 'IPS CLINICAL HOUSE')->first();
        if (!$sede) {
            throw new Exception("Sede 'IPS CLINICAL HOUSE' not found. Please run SedeSeeder first.");
        }

        DB::table('usuarios')->insert([
            'nombre_completo' => 'Junior',
            'usuario' => 'junior@house',
            'contrasena' => Hash::make('qweasdzxc'),
            'rol_id' => $rol->id,
            'correo' => 'junior@house',
            'telefono' => null,
            'estado' => 1, // Active
            'sede_id' => $sede->id,
            'firma_digital' => null,
        ]);
    }
}

<?php

namespace Database\Factories;

use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UsuarioFactory extends Factory
{
    protected $model = Usuario::class;

    public function definition(): array
    {
        return [
            'nombre_completo' => $this->faker->name(),
            'usuario' => $this->faker->unique()->userName(),
            'contrasena' => Hash::make('password'),
            'rol_id' => \App\Models\Rol::factory(),
            'correo' => $this->faker->unique()->safeEmail(),
            'telefono' => $this->faker->phoneNumber(),
            'estado' => 1,
            'sede_id' => null,
            'firma_digital' => null,
        ];
    }
}

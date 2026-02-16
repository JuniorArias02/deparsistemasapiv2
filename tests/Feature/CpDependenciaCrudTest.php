<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Usuario;
use App\Models\Rol;
use App\Models\Permiso;
use App\Models\CpDependencia;
use App\Models\Sede;
use Tymon\JWTAuth\Facades\JWTAuth;

class CpDependenciaCrudTest extends TestCase
{
    use RefreshDatabase;

    protected function authenticateUserWithPermissions($permissions = [])
    {
        $rol = Rol::create(['nombre' => 'TestRole']);
        foreach ($permissions as $permStr) {
            $permiso = Permiso::firstOrCreate(['nombre' => $permStr]);
            $rol->permisos()->attach($permiso);
        }

        $user = Usuario::create([
            'usuario' => 'testuser',
            'contrasena' => bcrypt('password'),
            'rol_id' => $rol->id
        ]);

        $token = JWTAuth::fromUser($user);

        return ['user' => $user, 'token' => $token];
    }

    public function test_can_create_dependencia()
    {
        $auth = $this->authenticateUserWithPermissions(['cp_dependencia.create']);
        $sede = Sede::create(['nombre' => 'Sede Test']);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $auth['token']])
                         ->postJson('/api/cp-dependencias', [
                             'nombre' => 'Dependencia Test',
                             'codigo' => 12345,
                             'sede_id' => $sede->id
                         ]);

        $response->assertStatus(201)
                 ->assertJson(['mensaje' => 'Dependencia creada exitosamente']);

        $this->assertDatabaseHas('cp_dependencias', ['nombre' => 'Dependencia Test', 'codigo' => 12345]);
    }

    public function test_can_list_dependencias()
    {
        $auth = $this->authenticateUserWithPermissions(['cp_dependencia.read']); // Actually index doesn't check perm in current controller code, but good practice
        $sede = Sede::create(['nombre' => 'Sede List']);
        CpDependencia::create(['nombre' => 'Dep 1', 'sede_id' => $sede->id]);
        CpDependencia::create(['nombre' => 'Dep 2', 'sede_id' => $sede->id]);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $auth['token']])
                         ->getJson('/api/cp-dependencias');

        $response->assertStatus(200)
                 ->assertJsonCount(2, 'objeto');
    }
}

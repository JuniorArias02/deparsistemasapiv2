<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Usuario;
use App\Models\Rol;
use App\Models\Permiso;
use App\Models\CpCentroCosto;
use Tymon\JWTAuth\Facades\JWTAuth;

class CpCentroCostoCrudTest extends TestCase
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

    public function test_can_create_centro_costo()
    {
        $auth = $this->authenticateUserWithPermissions(['cp_centro_costo.create']);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $auth['token']])
                         ->postJson('/api/cp-centro-costos', [
                             'nombre' => 'Centro Costo Test',
                             'codigo' => 101
                         ]);

        $response->assertStatus(201)
                 ->assertJson(['mensaje' => 'Centro de costo creado exitosamente']);

        $this->assertDatabaseHas('cp_centro_costo', ['nombre' => 'Centro Costo Test', 'codigo' => 101]);
    }

    public function test_can_list_centro_costos()
    {
        $auth = $this->authenticateUserWithPermissions(['cp_centro_costo.read']); 
        CpCentroCosto::create(['nombre' => 'CC 1', 'codigo' => 101]);
        CpCentroCosto::create(['nombre' => 'CC 2', 'codigo' => 102]);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $auth['token']])
                         ->getJson('/api/cp-centro-costos');

        $response->assertStatus(200)
                 ->assertJsonCount(2, 'objeto');
    }

    public function test_can_show_centro_costo()
    {
        $auth = $this->authenticateUserWithPermissions(['cp_centro_costo.read']);
        $cc = CpCentroCosto::create(['nombre' => 'CC Show', 'codigo' => 103]);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $auth['token']])
                         ->getJson("/api/cp-centro-costos/{$cc->id}");

        $response->assertStatus(200)
                 ->assertJsonFragment(['nombre' => 'CC Show']);
    }

    public function test_can_update_centro_costo()
    {
        $auth = $this->authenticateUserWithPermissions(['cp_centro_costo.update']);
        $cc = CpCentroCosto::create(['nombre' => 'CC Old', 'codigo' => 104]);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $auth['token']])
                         ->putJson("/api/cp-centro-costos/{$cc->id}", [
                             'nombre' => 'CC Updated'
                         ]);

        $response->assertStatus(200)
                 ->assertJson(['mensaje' => 'Centro de costo actualizado exitosamente']);

        $this->assertDatabaseHas('cp_centro_costo', ['id' => $cc->id, 'nombre' => 'CC Updated']);
    }

    public function test_can_delete_centro_costo()
    {
        $auth = $this->authenticateUserWithPermissions(['cp_centro_costo.delete']);
        $cc = CpCentroCosto::create(['nombre' => 'CC Delete', 'codigo' => 105]);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $auth['token']])
                         ->deleteJson("/api/cp-centro-costos/{$cc->id}");

        $response->assertStatus(200)
                 ->assertJson(['mensaje' => 'Centro de costo eliminado exitosamente']);

        $this->assertDatabaseMissing('cp_centro_costo', ['id' => $cc->id]);
    }
}

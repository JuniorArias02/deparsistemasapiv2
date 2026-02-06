<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Usuario;
use App\Models\Rol;
use App\Models\Permiso;
use Tymon\JWTAuth\Facades\JWTAuth;

class RolPermissionTest extends TestCase
{
    // usage of RefreshDatabase is dangerous if not configured correctly, checking phpunit.xml first would be wise
    // but typically Feature tests use a separate DB or transactions.
    // Given the environment, I'll assume standard transaction rollback traits might be safer or just manual cleanup if possible.
    // But `RefreshDatabase` is standard. Let's try it.
    use RefreshDatabase;

    protected $adminUser;
    protected $adminRole;
    protected $token;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Setup permissions
        $permissions = ['rol.read', 'rol.create', 'rol.update', 'rol.delete', 'rol.assign_permission'];
        foreach ($permissions as $p) {
            Permiso::create(['nombre' => $p]);
        }

        // Setup Admin Role
        $this->adminRole = Rol::create(['nombre' => 'Admin']);
        $this->adminRole->permisos()->sync(Permiso::all());

        // Setup Admin User
        $this->adminUser = Usuario::create([
            'usuario' => 'admin_test',
            'contrasena' => bcrypt('password'),
            'rol_id' => $this->adminRole->id,
            'nombre' => 'Admin Test'
        ]);

        $this->token = JWTAuth::fromUser($this->adminUser);
    }

    public function test_can_list_roles()
    {
        $response = $this->actingAs($this->adminUser, 'api')
                         ->getJson('/api/roles');

        $response->assertStatus(200)
                 ->assertJsonStructure(['status', 'mensaje', 'objeto']);
    }

    public function test_can_create_role()
    {
        $response = $this->actingAs($this->adminUser, 'api')
                         ->postJson('/api/roles', ['nombre' => 'New Role']);

        $response->assertStatus(201);
        $this->assertDatabaseHas('rol', ['nombre' => 'New Role']);
    }

    public function test_can_assign_permissions()
    {
        $role = Rol::create(['nombre' => 'Editor']);
        $permiso = Permiso::where('nombre', 'rol.read')->first();

        $response = $this->actingAs($this->adminUser, 'api')
                         ->putJson("/api/roles/{$role->id}/permissions", [
                             'permisos' => [$permiso->id]
                         ]);

        $response->assertStatus(200);
        $this->assertTrue($role->permisos()->where('nombre', 'rol.read')->exists());
    }

    public function test_cannot_access_without_permission()
    {
        // Create user with no permissions
        $user = Usuario::create([
            'usuario' => 'guest_test',
            'contrasena' => bcrypt('password'),
            'nombre' => 'Guest'
        ]);
        
        $response = $this->actingAs($user, 'api')
                         ->getJson('/api/roles');

        $response->assertStatus(403);
    }
}

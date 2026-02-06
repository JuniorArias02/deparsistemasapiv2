<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Usuario;
use App\Models\Rol;
use App\Models\Permiso;
use App\Models\Inventario;
use Tymon\JWTAuth\Facades\JWTAuth;

class InventarioPermissionTest extends TestCase
{
    use RefreshDatabase;

    protected $adminUser;
    protected $role;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        // Setup permissions
        $permissions = ['inventario.create', 'inventario.update', 'inventario.delete'];
        foreach ($permissions as $p) {
            Permiso::create(['nombre' => $p]);
        }

        $this->role = Rol::create(['nombre' => 'InvAdmin']);
        $this->role->permisos()->sync(Permiso::all());

        $this->adminUser = Usuario::create([
            'usuario' => 'inv_admin',
            'contrasena' => bcrypt('password'),
            'rol_id' => $this->role->id,
            'nombre' => 'Inventory Admin'
        ]);
    }

    public function test_can_create_inventory()
    {
        $response = $this->actingAs($this->adminUser, 'api')
                         ->postJson('/api/inventario', [
                             'codigo' => 'TEST-001',
                             'nombre' => 'Test Item'
                         ]);
        
        $response->assertStatus(201);
        $this->assertDatabaseHas('inventario', ['codigo' => 'TEST-001']);
    }

    public function test_can_update_inventory()
    {
        $item = Inventario::create([
            'codigo' => 'TEST-UPD',
            'nombre' => 'Old Name',
            'creado_por' => $this->adminUser->id
        ]);

        $response = $this->actingAs($this->adminUser, 'api')
                         ->putJson("/api/inventario/{$item->id}", [
                             'nombre' => 'New Name'
                         ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('inventario', ['id' => $item->id, 'nombre' => 'New Name']);
    }

    public function test_can_delete_inventory()
    {
        $item = Inventario::create([
            'codigo' => 'TEST-DEL',
            'nombre' => 'To Delete',
            'creado_por' => $this->adminUser->id
        ]);

        $response = $this->actingAs($this->adminUser, 'api')
                         ->deleteJson("/api/inventario/{$item->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('inventario', ['id' => $item->id]);
    }

    public function test_cannot_access_inventory_without_permission()
    {
        $guest = Usuario::create([
            'usuario' => 'guest_inv',
            'contrasena' => bcrypt('password'),
            'nombre' => 'Guest'
        ]);

        $item = Inventario::create([
            'codigo' => 'TEST-SEC', 
            'nombre' => 'Secure Item',
            'creado_por' => $this->adminUser->id
        ]);

        // Try create
        $this->actingAs($guest, 'api')
             ->postJson('/api/inventario', ['codigo' => 'FAIL', 'nombre' => 'Fail'])
             ->assertStatus(403);

        // Try update
        $this->actingAs($guest, 'api')
             ->putJson("/api/inventario/{$item->id}", ['nombre' => 'Hacked'])
             ->assertStatus(403);
             
        // Try delete
        $this->actingAs($guest, 'api')
             ->deleteJson("/api/inventario/{$item->id}")
             ->assertStatus(403);
    }
}

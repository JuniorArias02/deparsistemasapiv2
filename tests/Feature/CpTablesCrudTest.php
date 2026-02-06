<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Usuario;
use App\Models\Rol;
use App\Models\Permiso;
use App\Models\CpProducto;

class CpTablesCrudTest extends TestCase
{
    use RefreshDatabase;

    protected $adminUser;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        // Ensure permissions exist
        $permissions = [
            'cp_producto.read', 'cp_producto.create', 'cp_producto.update', 'cp_producto.delete',
            'cp_proveedor.read', 'cp_proveedor.create', 'cp_proveedor.update', 'cp_proveedor.delete'
        ];
        
        $role = Rol::create(['nombre' => 'CpAdmin']);
        
        foreach ($permissions as $p) {
            $perm = Permiso::firstOrCreate(['nombre' => $p]);
            $role->permisos()->attach($perm->id);
        }

        $this->adminUser = Usuario::create([
            'usuario' => 'cp_admin',
            'contrasena' => bcrypt('password'),
            'rol_id' => $role->id,
            'nombre' => 'CP Admin'
        ]);
    }

    public function test_can_create_and_list_cp_producto()
    {
        $response = $this->actingAs($this->adminUser, 'api')
                         ->postJson('/api/cp-productos', [
                             'codigo' => 'PROD-001',
                             'nombre' => 'Test Product'
                         ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('cp_productos', ['codigo' => 'PROD-001']);

        $response = $this->actingAs($this->adminUser, 'api')
                         ->getJson('/api/cp-productos');
        
        $response->assertStatus(200)
                 ->assertJsonCount(1, 'objeto');
    }

    public function test_can_update_cp_producto()
    {
        $prod = CpProducto::create(['codigo' => 'OLD', 'nombre' => 'Old Name']);

        $response = $this->actingAs($this->adminUser, 'api')
                         ->putJson("/api/cp-productos/{$prod->id}", [
                             'nombre' => 'New Name'
                         ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('cp_productos', ['id' => $prod->id, 'nombre' => 'New Name']);
    }

    public function test_can_create_cp_proveedor()
    {
        $response = $this->actingAs($this->adminUser, 'api')
                         ->postJson('/api/cp-proveedores', [
                             'nombre' => 'Test Provider',
                             'nit' => '900123456',
                             'correo' => 'test@provider.com'
                         ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('cp_proveedores', ['nit' => '900123456']);
    }

    public function test_cannot_access_without_permission()
    {
        $guest = Usuario::create([
            'usuario' => 'guest_cp',
            'contrasena' => bcrypt('password'),
            'nombre' => 'Guest'
        ]);

        $this->actingAs($guest, 'api')
             ->getJson('/api/cp-productos')
             ->assertStatus(403);
    }
}

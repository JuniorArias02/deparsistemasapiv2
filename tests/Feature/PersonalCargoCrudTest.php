<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Usuario;
use App\Models\Rol;
use App\Models\Permiso;
use App\Models\PCargo;

class PersonalCargoCrudTest extends TestCase
{
    use RefreshDatabase;

    protected $adminUser;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        // Ensure permissions exist
        $permissions = [
            'p_cargo.read', 'p_cargo.create', 'p_cargo.update', 'p_cargo.delete',
            'personal.read', 'personal.create', 'personal.update', 'personal.delete'
        ];
        
        $role = Rol::create(['nombre' => 'PersonalAdmin']);
        
        foreach ($permissions as $p) {
            $perm = Permiso::firstOrCreate(['nombre' => $p]);
            $role->permisos()->attach($perm->id);
        }

        $this->adminUser = Usuario::create([
            'usuario' => 'personal_admin',
            'contrasena' => bcrypt('password'),
            'rol_id' => $role->id,
            'nombre' => 'Personal Admin'
        ]);
    }

    public function test_can_create_and_list_cargos()
    {
        $response = $this->actingAs($this->adminUser, 'api')
                         ->postJson('/api/p-cargos', [
                             'nombre' => 'Test Cargo'
                         ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('p_cargo', ['nombre' => 'Test Cargo']);

        $response = $this->actingAs($this->adminUser, 'api')
                         ->getJson('/api/p-cargos');
        
        $response->assertStatus(200)
                 ->assertJsonCount(1, 'objeto');
    }

    public function test_can_create_personal()
    {
        $cargo = PCargo::create(['nombre' => 'Analista']);

        $response = $this->actingAs($this->adminUser, 'api')
                         ->postJson('/api/personal', [
                             'nombre' => 'John Doe',
                             'cedula' => '123456789',
                             'telefono' => '555-1234',
                             'cargo_id' => $cargo->id
                         ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('personal', ['cedula' => '123456789']);
    }

    public function test_cannot_access_without_permission()
    {
        $guest = Usuario::create([
            'usuario' => 'guest_personal',
            'contrasena' => bcrypt('password'),
            'nombre' => 'Guest'
        ]);

        $this->actingAs($guest, 'api')
             ->getJson('/api/personal')
             ->assertStatus(403);
    }
}

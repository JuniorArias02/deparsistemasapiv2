<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Usuario;
use App\Models\Rol;
use App\Models\Permiso;
use App\Models\Sede;

class SedeDependenciaCrudTest extends TestCase
{
    use RefreshDatabase;

    protected $adminUser;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        // Ensure permissions exist
        $permissions = [
            'sede.read', 'sede.create', 'sede.update', 'sede.delete',
            'dependencia_sede.read', 'dependencia_sede.create', 'dependencia_sede.update', 'dependencia_sede.delete'
        ];
        
        $role = Rol::create(['nombre' => 'SedeAdmin']);
        
        foreach ($permissions as $p) {
            $perm = Permiso::firstOrCreate(['nombre' => $p]);
            $role->permisos()->attach($perm->id);
        }

        $this->adminUser = Usuario::create([
            'usuario' => 'sede_admin',
            'contrasena' => bcrypt('password'),
            'rol_id' => $role->id,
            'nombre' => 'Sede Admin'
        ]);
    }

    public function test_can_create_and_list_sedes()
    {
        $response = $this->actingAs($this->adminUser, 'api')
                         ->postJson('/api/sedes', [
                             'nombre' => 'Test Sede'
                         ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('sedes', ['nombre' => 'Test Sede']);

        $response = $this->actingAs($this->adminUser, 'api')
                         ->getJson('/api/sedes');
        
        $response->assertStatus(200)
                 ->assertJsonCount(1, 'objeto');
    }

    public function test_can_create_dependencia_sede()
    {
        $sede = Sede::create(['nombre' => 'Parent Sede']);

        $response = $this->actingAs($this->adminUser, 'api')
                         ->postJson('/api/dependencias-sedes', [
                             'sede_id' => $sede->id,
                             'nombre' => 'Test Dependencia'
                         ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('dependencias_sedes', ['nombre' => 'Test Dependencia', 'sede_id' => $sede->id]);
    }

    public function test_cannot_access_without_permission()
    {
        $guest = Usuario::create([
            'usuario' => 'guest_sede',
            'contrasena' => bcrypt('password'),
            'nombre' => 'Guest'
        ]);

        $this->actingAs($guest, 'api')
             ->getJson('/api/sedes')
             ->assertStatus(403);
    }
}

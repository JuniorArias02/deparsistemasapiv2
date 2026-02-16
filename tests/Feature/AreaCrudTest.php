<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\Sede;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Services\PermissionService;

class AreaCrudTest extends TestCase
{
    use RefreshDatabase;

    protected $usuario;
    protected $sede;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a user and mock permissions
        $this->usuario = Usuario::factory()->create();
        $this->actingAs($this->usuario, 'api');

        // Create a Sede for the Area
        $this->sede = Sede::create(['nombre' => 'Sede Principal']);

        // Mock PermissionService to allow all
        $this->mock(PermissionService::class, function ($mock) {
            $mock->shouldReceive('authorize')->andReturn(true);
        });
    }

    public function test_can_list_areas()
    {
        Area::create(['nombre' => 'Area 1', 'sede_id' => $this->sede->id]);
        Area::create(['nombre' => 'Area 2', 'sede_id' => $this->sede->id]);

        $response = $this->getJson('/api/areas');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'objeto');
    }

    public function test_can_create_area()
    {
        $data = [
            'nombre' => 'Nueva Area',
            'sede_id' => $this->sede->id
        ];

        $response = $this->postJson('/api/areas', $data);

        $response->assertStatus(201)
            ->assertJsonFragment(['nombre' => 'Nueva Area']);

        $this->assertDatabaseHas('areas', ['nombre' => 'Nueva Area']);
    }

    public function test_can_show_area()
    {
        $area = Area::create(['nombre' => 'Area Ver', 'sede_id' => $this->sede->id]);

        $response = $this->getJson("/api/areas/{$area->id}");

        $response->assertStatus(200)
            ->assertJsonFragment(['nombre' => 'Area Ver']);
    }

    public function test_can_update_area()
    {
        $area = Area::create(['nombre' => 'Original', 'sede_id' => $this->sede->id]);

        $data = ['nombre' => 'Modificado'];

        $response = $this->putJson("/api/areas/{$area->id}", $data);

        $response->assertStatus(200)
            ->assertJsonFragment(['nombre' => 'Modificado']);

        $this->assertDatabaseHas('areas', ['id' => $area->id, 'nombre' => 'Modificado']);
    }

    public function test_can_delete_area()
    {
        $area = Area::create(['nombre' => 'Borrar', 'sede_id' => $this->sede->id]);

        $response = $this->deleteJson("/api/areas/{$area->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('areas', ['id' => $area->id]);
    }
}

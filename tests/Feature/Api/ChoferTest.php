<?php

namespace Tests\Feature\Api;

use App\Models\Chofer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChoferTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_list_chofers(): void
    {
        Chofer::factory()->count(3)->create();

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/choferes');

        $response->assertStatus(200)
            ->assertJsonCount(3);
    }

    public function test_list_chofers_empty(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/choferes');

        $response->assertStatus(200)
            ->assertJsonCount(0);
    }

    public function test_create_chofer(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/choferes', [
                'nombres_completos' => 'Juan Perez',
                'telefono' => '987654321',
            ]);

        $response->assertStatus(201)
            ->assertJsonFragment([
                'nombres_completos' => 'Juan Perez',
                'telefono' => '987654321',
            ]);

        $this->assertDatabaseHas('chofers', [
            'nombres_completos' => 'Juan Perez',
        ]);
    }

    public function test_create_chofer_validation_required(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/choferes', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['nombres_completos']);
    }

    public function test_show_chofer(): void
    {
        $chofer = Chofer::factory()->create();

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/choferes/{$chofer->id}");

        $response->assertStatus(200);
    }

    public function test_show_chofer_not_found(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/choferes/99999');

        $response->assertStatus(200);
    }

    public function test_update_chofer(): void
    {
        $chofer = Chofer::factory()->create();

        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson("/api/choferes/{$chofer->id}", [
                'nombres_completos' => 'Juan Perez Actualizado',
            ]);

        $response->assertStatus(200);
    }

    public function test_update_estado_asignacion(): void
    {
        $chofer = Chofer::factory()->create(['estado_asignacion' => 'disponible']);

        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson("/api/choferes/{$chofer->id}", [
                'estado_asignacion' => 'ocupado',
            ]);

        $response->assertStatus(200);
    }

    public function test_update_chofer_is_active(): void
    {
        $chofer = Chofer::factory()->create(['is_active' => true]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson("/api/choferes/{$chofer->id}", [
                'is_active' => false,
            ]);

        $response->assertStatus(200);
    }

    public function test_delete_chofer(): void
    {
        $chofer = Chofer::factory()->create();

        $response = $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/choferes/{$chofer->id}");

        $response->assertStatus(204);
    }

    public function test_delete_chofer_soft_delete(): void
    {
        $chofer = Chofer::factory()->create();

        $response = $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/choferes/{$chofer->id}");

        $response->assertStatus(204);
        $this->assertSoftDeleted('chofers', ['id' => $chofer->id]);
    }
}

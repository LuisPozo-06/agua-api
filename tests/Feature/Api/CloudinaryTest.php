<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CloudinaryTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_generar_firma(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/cloudinary/firma');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'timestamp',
                'signature',
                'api_key',
                'cloud_name',
                'folder',
            ]);
    }

    public function test_firma_tiene_timestamp(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/cloudinary/firma');

        $response->assertStatus(200);

        $data = $response->json();
        $this->assertNotNull($data['timestamp']);
        $this->assertIsInt($data['timestamp']);
        $this->assertGreaterThan(0, $data['timestamp']);
    }

    public function test_firma_tiene_signature(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/cloudinary/firma');

        $response->assertStatus(200);

        $data = $response->json();
        $this->assertNotNull($data['signature']);
        $this->assertIsString($data['signature']);
        $this->assertNotEmpty($data['signature']);
    }

    public function test_firma_tiene_configuracion(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/cloudinary/firma');

        $response->assertStatus(200);

        $data = $response->json();
        $this->assertNotNull($data['api_key']);
        $this->assertNotNull($data['cloud_name']);
        $this->assertNotNull($data['folder']);
    }

    public function test_generar_firma_sin_auth(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/cloudinary/firma');

        $response->assertStatus(200);
    }

    public function test_firma_cambia_con_el_tiempo(): void
    {
        $response1 = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/cloudinary/firma');

        $timestamp1 = $response1->json('timestamp');

        sleep(2);

        $response2 = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/cloudinary/firma');

        $timestamp2 = $response2->json('timestamp');

        $this->assertNotEquals($timestamp1, $timestamp2);
    }
}

<?php

namespace Tests\Feature;

use App\Models\Site;
use App\Models\Incident;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class WebhookTest extends TestCase
{
    use RefreshDatabase;

    public function test_uptime_kuma_webhook_creates_incident_for_down_site()
    {
        Queue::fake();

        $site = Site::factory()->create([
            'url' => 'https://example.com',
            'status' => 'active',
        ]);

        $payload = [
            'heartbeat' => [
                'status' => 0,
                'msg' => 'Connection timeout',
                'time' => now()->toISOString(),
            ],
            'monitor' => [
                'name' => 'Example Site',
                'url' => 'https://example.com',
            ],
        ];

        $response = $this->postJson('/webhook/uptime-kuma', $payload);

        $response->assertStatus(200);

        $this->assertDatabaseHas('incidents', [
            'site_id' => $site->id,
            'status' => 'detected',
            'error_details' => 'Connection timeout',
        ]);

        $site->refresh();
        $this->assertEquals('down', $site->status);
    }

    public function test_uptime_kuma_webhook_resolves_incident_for_up_site()
    {
        $site = Site::factory()->create([
            'url' => 'https://example.com',
            'status' => 'down',
        ]);

        $incident = Incident::factory()->create([
            'site_id' => $site->id,
            'status' => 'verified',
            'resolved_at' => null,
        ]);

        $payload = [
            'heartbeat' => [
                'status' => 1,
                'msg' => 'OK',
                'time' => now()->toISOString(),
            ],
            'monitor' => [
                'name' => 'Example Site',
                'url' => 'https://example.com',
            ],
        ];

        $response = $this->postJson('/webhook/uptime-kuma', $payload);

        $response->assertStatus(200);

        $incident->refresh();
        $site->refresh();

        $this->assertEquals('resolved', $incident->status);
        $this->assertNotNull($incident->resolved_at);
        $this->assertEquals('active', $site->status);
    }

    public function test_webhook_rejects_invalid_payload()
    {
        $payload = [
            'invalid' => 'data',
        ];

        $response = $this->postJson('/webhook/uptime-kuma', $payload);

        $response->assertStatus(400);
        $response->assertJson(['error' => 'Invalid payload']);
    }

    public function test_webhook_returns_404_for_unknown_site()
    {
        $payload = [
            'heartbeat' => [
                'status' => 0,
                'msg' => 'Connection timeout',
                'time' => now()->toISOString(),
            ],
            'monitor' => [
                'name' => 'Unknown Site',
                'url' => 'https://unknown-site.com',
            ],
        ];

        $response = $this->postJson('/webhook/uptime-kuma', $payload);

        $response->assertStatus(404);
        $response->assertJson(['message' => 'Site not found']);
    }

    public function test_webhook_ignores_duplicate_down_alerts()
    {
        $site = Site::factory()->create([
            'url' => 'https://example.com',
            'status' => 'down',
        ]);

        $payload = [
            'heartbeat' => [
                'status' => 0,
                'msg' => 'Connection timeout',
                'time' => now()->toISOString(),
            ],
            'monitor' => [
                'name' => 'Example Site',
                'url' => 'https://example.com',
            ],
        ];

        $response = $this->postJson('/webhook/uptime-kuma', $payload);

        $response->assertStatus(200);

        $this->assertDatabaseCount('incidents', 0);
    }
}
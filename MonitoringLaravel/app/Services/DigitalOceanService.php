<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DigitalOceanService
{
    private string $baseUrl = 'https://api.digitalocean.com/v2';
    private string $token;

    public function __construct()
    {
        $this->token = config('services.digitalocean.token');
    }

    public function rebootDroplet(string $dropletId): array
    {
        $url = "{$this->baseUrl}/droplets/{$dropletId}/actions";

        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->token}",
                'Content-Type' => 'application/json',
            ])->post($url, [
                'type' => 'reboot'
            ]);

            if (!$response->successful()) {
                throw new \Exception("DigitalOcean API error: " . $response->body());
            }

            $data = $response->json();

            Log::info('Droplet reboot action initiated', [
                'droplet_id' => $dropletId,
                'action_id' => $data['action']['id'] ?? null,
                'status' => $data['action']['status'] ?? null,
            ]);

            return $data;
        } catch (\Exception $e) {
            Log::error('Failed to reboot droplet', [
                'droplet_id' => $dropletId,
                'error' => $e->getMessage(),
            ]);

            throw new \Exception('Failed to reboot droplet: ' . $e->getMessage());
        }
    }

    public function getDropletInfo(string $dropletId): array
    {
        $url = "{$this->baseUrl}/droplets/{$dropletId}";

        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->token}",
            ])->get($url);

            if (!$response->successful()) {
                throw new \Exception("DigitalOcean API error: " . $response->body());
            }

            return $response->json();
        } catch (\Exception $e) {
            Log::error('Failed to get droplet info', [
                'droplet_id' => $dropletId,
                'error' => $e->getMessage(),
            ]);

            throw new \Exception('Failed to get droplet info: ' . $e->getMessage());
        }
    }

    public function getActionStatus(string $actionId): array
    {
        $url = "{$this->baseUrl}/actions/{$actionId}";

        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->token}",
            ])->get($url);

            if (!$response->successful()) {
                throw new \Exception("DigitalOcean API error: " . $response->body());
            }

            return $response->json();
        } catch (\Exception $e) {
            Log::error('Failed to get action status', [
                'action_id' => $actionId,
                'error' => $e->getMessage(),
            ]);

            throw new \Exception('Failed to get action status: ' . $e->getMessage());
        }
    }

    public function powerOnDroplet(string $dropletId): array
    {
        $url = "{$this->baseUrl}/droplets/{$dropletId}/actions";

        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->token}",
                'Content-Type' => 'application/json',
            ])->post($url, [
                'type' => 'power_on'
            ]);

            if (!$response->successful()) {
                throw new \Exception("DigitalOcean API error: " . $response->body());
            }

            $data = $response->json();

            Log::info('Droplet power on action initiated', [
                'droplet_id' => $dropletId,
                'action_id' => $data['action']['id'] ?? null,
            ]);

            return $data;
        } catch (\Exception $e) {
            Log::error('Failed to power on droplet', [
                'droplet_id' => $dropletId,
                'error' => $e->getMessage(),
            ]);

            throw new \Exception('Failed to power on droplet: ' . $e->getMessage());
        }
    }
}
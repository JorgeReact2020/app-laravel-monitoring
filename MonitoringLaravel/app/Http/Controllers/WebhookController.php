<?php

namespace App\Http\Controllers;

use App\Models\Site;
use App\Models\Incident;
use App\Jobs\VerifySiteDownJob;
use App\Http\Requests\UptimeKumaWebhookRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function uptimeKuma(UptimeKumaWebhookRequest $request): JsonResponse
    {

        echo '<pre>';
        var_dump($request->validated());
        echo '</pre>';
        die();
        Log::info('Uptime Kuma webhook received', $request->all());
        $data = $request->validated();

        $heartbeat = $data['heartbeat'];
        $monitor = $data['monitor'];
        $status = $heartbeat['status'];
        $url = $monitor['url'];
        $name = $monitor['name'];

        $site = Site::where('url', $url)->first();


        if (!$site) {
            Log::warning("Site not found for URL: {$url}");
            return response()->json(['message' => 'Site not found'], 404);
        }


        if ($status === "0") {
            $this->handleSiteDown($site, $heartbeat);
        } elseif ($status === "1") {
            $this->handleSiteUp($site);
        }

        return response()->json(['message' => 'Webhook processed successfully']);
    }

    private function handleSiteDown(Site $site, array $heartbeat): void
    {

        if ($site->isDown()) {
            Log::info("Site {$site->name} already marked as down");
            return;
        }

        $incident = Incident::create([
            'site_id' => $site->id,
            'status' => 'detected',
            'error_details' => $heartbeat['msg'] ?? 'Site detected as down',
            'status_code' => null,
            'detected_at' => now(),
        ]);

        //$site->markAsDown();

        Log::info("Created incident {$incident->id} for site {$site->name}");

        VerifySiteDownJob::dispatch($incident);
    }

    private function handleSiteUp(Site $site): void
    {

        if (!$site->isDown()) {
            return;
        }

        $unresolvedIncidents = $site->unresolvedIncidents;

        foreach ($unresolvedIncidents as $incident) {
            $incident->markAsResolved();
            Log::info("Resolved incident {$incident->id} for site {$site->name}");
        }

        $site->markAsActive();
        Log::info("Site {$site->name} marked as active");
    }
}

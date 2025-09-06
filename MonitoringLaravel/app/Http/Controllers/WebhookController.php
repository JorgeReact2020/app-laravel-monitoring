<?php

namespace App\Http\Controllers;

use App\Models\Site;
use App\Models\Incident;
use App\Jobs\VerifySiteDownJob;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class WebhookController extends Controller
{
    public function uptimeKuma(Request $request): JsonResponse
    {

        Log::info('Uptime Kuma webhook received', $request->all());

        $validator = Validator::make($request->all(), [
            'heartbeat' => 'required|array',
            'heartbeat.status' => 'required|integer',
            'heartbeat.msg' => 'nullable|string',
            'heartbeat.time' => 'required|string',
            'monitor' => 'required|array',
            'monitor.name' => 'required|string',
            'monitor.url' => 'required|string',
        ]);



        if ($validator->fails()) {
            Log::error('Invalid Uptime Kuma webhook payload', $validator->errors()->toArray());
            return response()->json(['error' => 'Invalid payload'], 400);
        }

        $heartbeat = $request->input('heartbeat');
        $monitor = $request->input('monitor');
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

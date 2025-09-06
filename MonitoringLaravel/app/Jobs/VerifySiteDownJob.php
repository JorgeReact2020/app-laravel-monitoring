<?php

namespace App\Jobs;

use App\Models\Incident;
use App\Jobs\SendRebootNotificationJob;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class VerifySiteDownJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    public function __construct(
        public Incident $incident
    ) {}

    public function handle(): void
    {
        $site = $this->incident->site;

        Log::info("Verifying site down for incident {$this->incident->id}", [
            'site_id' => $site->id,
            'site_name' => $site->name,
            'site_url' => $site->url,
        ]);

        $verificationResults = [];
        $maxAttempts = 3;
        $downCount = 0;

        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            $result = $this->checkSite($site->url, $site->timeout);
            $verificationResults[] = $result;

            if (!$result['is_up']) {
                $downCount++;
            }

            Log::info("Verification attempt {$attempt} for site {$site->name}", $result);

            if ($attempt < $maxAttempts) {
                sleep(1);
            }
        }

        $this->incident->addVerificationAttempt([
            'timestamp' => now()->toISOString(),
            'attempts' => $verificationResults,
            'down_count' => $downCount,
            'total_attempts' => $maxAttempts,
        ]);

        if ($downCount >= 1) {
            $this->incident->markAsVerified();

            Log::info("Site {$site->name} confirmed as down ({$downCount}/{$maxAttempts} checks failed)");

            if ($site->notification_phone && $site->droplet_id) {
                SendRebootNotificationJob::dispatch($this->incident);
            } else {
                Log::warning("Cannot send notification for site {$site->name}: missing phone or droplet_id", [
                    'phone' => $site->notification_phone,
                    'droplet_id' => $site->droplet_id,
                ]);
            }
        } else {
            $this->incident->markAsResolved();
            $site->markAsActive();

            Log::info("False positive: Site {$site->name} is actually up ({$downCount}/{$maxAttempts} checks failed)");
        }
    }

    private function checkSite(string $url, int $timeout): array
    {
        $startTime = microtime(true);

        try {
            $response = Http::timeout($timeout)
                ->retry(1, 5000)
                ->get($url);

            $endTime = microtime(true);
            $responseTime = round(($endTime - $startTime) * 1000);

            return [
                'is_up' => $response->successful(),
                'status_code' => $response->status(),
                'response_time' => $responseTime,
                'error' => null,
                'timestamp' => now()->toISOString(),
            ];
        } catch (\Exception $e) {
            $endTime = microtime(true);
            $responseTime = round(($endTime - $startTime) * 1000);

            return [
                'is_up' => false,
                'status_code' => 0,
                'response_time' => $responseTime,
                'error' => $e->getMessage(),
                'timestamp' => now()->toISOString(),
            ];
        }
    }
}

<?php

namespace App\Jobs;

use App\Models\RebootLog;
use App\Services\DigitalOceanService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class RebootDropletJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;
    public int $timeout = 120;

    public function __construct(
        public RebootLog $rebootLog
    ) {}

    public function handle(DigitalOceanService $digitalOceanService): void
    {
        $site = $this->rebootLog->site;
        $incident = $this->rebootLog->incident;

        Log::info("Starting droplet reboot for reboot log {$this->rebootLog->id}", [
            'site_id' => $site->id,
            'site_name' => $site->name,
            'droplet_id' => $this->rebootLog->droplet_id,
            'incident_id' => $incident->id,
        ]);

        $this->rebootLog->markAsInProgress();

        try {
            $response = $digitalOceanService->rebootDroplet($this->rebootLog->droplet_id);

            $this->rebootLog->markAsCompleted(json_encode($response));

            Log::info("Droplet reboot completed successfully", [
                'reboot_log_id' => $this->rebootLog->id,
                'droplet_id' => $this->rebootLog->droplet_id,
                'site_name' => $site->name,
                'response' => $response,
            ]);

            $this->waitAndVerifyReboot();

        } catch (\Exception $e) {
            $this->rebootLog->markAsFailed($e->getMessage());

            Log::error("Droplet reboot failed", [
                'reboot_log_id' => $this->rebootLog->id,
                'droplet_id' => $this->rebootLog->droplet_id,
                'site_name' => $site->name,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    private function waitAndVerifyReboot(): void
    {
        $site = $this->rebootLog->site;

        Log::info("Waiting for droplet {$this->rebootLog->droplet_id} to come back online");

        sleep(90);

        $maxAttempts = 5;
        $attempt = 0;

        while ($attempt < $maxAttempts) {
            $attempt++;
            
            try {
                $response = \Illuminate\Support\Facades\Http::timeout(15)->get($site->url);
                
                if ($response->successful()) {
                    $this->rebootLog->incident->markAsResolved();
                    
                    Log::info("Site {$site->name} is back online after reboot", [
                        'attempt' => $attempt,
                        'reboot_log_id' => $this->rebootLog->id,
                    ]);
                    
                    return;
                }
            } catch (\Exception $e) {
                Log::debug("Site check attempt {$attempt} failed: {$e->getMessage()}");
            }

            if ($attempt < $maxAttempts) {
                sleep(30);
            }
        }

        Log::warning("Site {$site->name} did not come back online after reboot", [
            'attempts' => $maxAttempts,
            'reboot_log_id' => $this->rebootLog->id,
        ]);
    }
}

<?php

namespace App\Jobs;

use App\Models\Incident;
use App\Services\SmsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;

class SendRebootNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 30;

    public function __construct(
        public Incident $incident
    ) {}

    public function handle(SmsService $smsService): void
    {
        $site = $this->incident->site;
  

        if (!$site->notification_phone) {
            Log::warning("No notification phone configured for site {$site->name}");
            return;
        }

        if (!$site->droplet_id) {
            Log::warning("No droplet ID configured for site {$site->name}");
            return;
        }

        $rebootUrl = URL::temporarySignedRoute(
            'reboot.show',
            now()->addHour(),
            ['site' => $site->id, 'incident' => $this->incident->id]
        );

        $message = $this->buildSmsMessage($site, $this->incident, $rebootUrl);

        try {
            $smsService->sendSms($site->notification_phone, $message);

            $this->incident->markAsNotificationSent();

            Log::info("SMS notification sent for incident {$this->incident->id}", [
                'site_id' => $site->id,
                'site_name' => $site->name,
                'phone' => $site->notification_phone,
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to send SMS notification for incident {$this->incident->id}", [
                'site_id' => $site->id,
                'site_name' => $site->name,
                'phone' => $site->notification_phone,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    private function buildSmsMessage(
        \App\Models\Site $site,
        Incident $incident,
        string $rebootUrl
    ): string {
        $detectedAt = $incident->detected_at->format('H:i d/m/Y');

        return "🚨 ALERT: {$site->name} is DOWN!\n\n" .
               "Detected: {$detectedAt}\n" .
               "Site: {$site->url}\n\n" .
               "Tap to restart server:\n{$rebootUrl}\n\n" .
               "Link expires in 1 hour.";
    }
}

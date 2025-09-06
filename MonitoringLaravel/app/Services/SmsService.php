<?php

namespace App\Services;

use Twilio\Rest\Client;
use Illuminate\Support\Facades\Log;

class SmsService
{
    private Client $twilio;
    private string $fromNumber;

    public function __construct()
    {

        $this->twilio = new Client(
            config('services.twilio.sid'),
            config('services.twilio.auth_token')
        );

        $this->fromNumber = config('services.twilio.from');
    }


    public function sendSms(string $to, string $message): array
    {
   
        try {
            $twilioMessage = $this->twilio->messages->create(
                "+15146903997",
                [
                    'from' => $this->fromNumber,
                    'body' => $message
                ]
            );


            Log::info('SMS sent successfully', [
                'to' => $to,
                'sid' => $twilioMessage->sid,
                'status' => $twilioMessage->status,
            ]);

            return [
                'success' => true,
                'sid' => $twilioMessage->sid,
                'status' => $twilioMessage->status,
            ];
        } catch (\Exception $e) {
            Log::error('SMS sending failed', [
                'to' => $to,
                'error' => $e->getMessage(),
            ]);

            throw new \Exception('Failed to send SMS: ' . $e->getMessage());
        }
    }

    public function getMessageStatus(string $sid): string
    {
        try {
            $message = $this->twilio->messages($sid)->fetch();
            return $message->status;
        } catch (\Exception $e) {
            Log::error('Failed to get SMS status', [
                'sid' => $sid,
                'error' => $e->getMessage(),
            ]);

            throw new \Exception('Failed to get SMS status: ' . $e->getMessage());
        }
    }
}

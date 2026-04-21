<?php

namespace App\Jobs;

use App\Models\ApiWebhook;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ProcessWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $webhook;

    protected $payload;

    /**
     * Create a new job instance.
     */
    public function __construct(ApiWebhook $webhook, array $payload)
    {
        $this->webhook = $webhook;
        $this->payload = $payload;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $response = Http::withHeaders([
                'X-Webhook-Signature' => $this->generateSignature(),
                'Content-Type' => 'application/json',
            ])->post($this->webhook->url, [
                'event' => $this->webhook->event_type,
                'data' => $this->payload,
                'timestamp' => now()->toIso8601String(),
            ]);

            if ($response->failed()) {
                Log::error("Webhook failed for {$this->webhook->url}: ".$response->body());
                // Optionally retry or log status
            }
        } catch (\Exception $e) {
            Log::error("Webhook exception for {$this->webhook->url}: ".$e->getMessage());
        }
    }

    protected function generateSignature()
    {
        return hash_hmac('sha256', json_encode($this->payload), $this->webhook->secret);
    }
}

<?php

namespace App\Services\GoldRate;

use App\Models\RateApiProvider;
use App\Models\GoldRate;
use App\Models\RateApiLog;
use Illuminate\Support\Facades\Http;

class RateApiService
{
    public function syncRates(RateApiProvider $provider): bool
    {
        try {
            $response = Http::timeout(10)->get($provider->api_url, [
                'api_key' => $provider->api_key,
            ]);

            if (!$response->successful()) {
                $this->logError($provider, $response->status(), 'API request failed');
                return false;
            }

            $data = $response->json();
            $recordsUpdated = 0;
            
            if (isset($data['rates'])) {
                foreach ($data['rates'] as $rate) {
                    GoldRate::updateOrCreate(
                        ['metal_type' => $rate['metal'] ?? 'gold', 'purity' => $rate['purity'] ?? '24'],
                        ['rate' => $rate['rate'] ?? 0, 'currency' => $rate['currency'] ?? 'INR']
                    );
                    $recordsUpdated++;
                }
            }

            $provider->update(['last_sync_at' => now()]);
            $this->logSuccess($provider, $recordsUpdated);
            
            return true;
        } catch (\Exception $e) {
            $this->logError($provider, null, $e->getMessage());
            return false;
        }
    }

    private function logSuccess(RateApiProvider $provider, int $recordsUpdated): void
    {
        RateApiLog::create([
            'provider_id' => $provider->id,
            'status' => 'success',
            'records_updated' => $recordsUpdated,
        ]);
    }

    private function logError(RateApiProvider $provider, ?int $code, string $message): void
    {
        RateApiLog::create([
            'provider_id' => $provider->id,
            'status' => 'failed',
            'response_code' => $code,
            'error_message' => $message,
        ]);
    }

    public function testConnection(RateApiProvider $provider): bool
    {
        try {
            $response = Http::timeout(5)->get($provider->api_url, [
                'api_key' => $provider->api_key,
            ]);
            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }
}

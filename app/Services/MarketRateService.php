<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MarketRateService
{
    private $apiKey;

    private $baseUrl;

    private $currency;

    public function __construct()
    {
        $this->apiKey = config('services.gold_api.key');
        $this->baseUrl = config('services.gold_api.base_url');
        $this->currency = config('services.gold_api.currency', 'PKR');
    }

    /**
     * Fetch live rates from external API
     */
    public function fetchLiveRates(): array
    {
        if (empty($this->apiKey)) {
            Log::warning('GoldAPI.io key is missing.');

            return ['status' => 'error', 'message' => 'API Key missing'];
        }

        try {
            $goldResponse = Http::withHeaders([
                'x-access-token' => $this->apiKey,
                'Content-Type' => 'application/json',
            ])->get($this->baseUrl.'XAU/'.$this->currency);

            $silverResponse = Http::withHeaders([
                'x-access-token' => $this->apiKey,
                'Content-Type' => 'application/json',
            ])->get($this->baseUrl.'XAG/'.$this->currency);

            if ($goldResponse->successful() && $silverResponse->successful()) {
                $goldData = $goldResponse->json();
                $silverData = $silverResponse->json();

                // GoldAPI returns price per ounce. Convert to gram (1 ounce = 31.1035 grams)
                $goldPriceGram = $goldData['price'] / 31.1035;
                $silverPriceGram = $silverData['price'] / 31.1035;

                $rates = [
                    'gold_24k' => round($goldPriceGram, 2),
                    'silver' => round($silverPriceGram, 2),
                    'currency' => $this->currency,
                    'timestamp' => now()->toDateTimeString(),
                    'source' => 'GoldAPI.io',
                ];

                Cache::put('market_rates_live', $rates, 3600); // Cache for 1 hour

                return $rates;
            }

            Log::error('GoldAPI failed: '.$goldResponse->body());
        } catch (\Exception $e) {
            Log::error('GoldAPI Exception: '.$e->getMessage());
        }

        return ['status' => 'error', 'message' => 'Failed to fetch live rates'];
    }

    public function getLatestRate(string $metal = 'gold'): array
    {
        $cacheKey = 'market_rate_'.$metal;

        // Try to get live rates from cache first
        $liveRates = Cache::get('market_rates_live');
        if ($liveRates) {
            return [
                'metal' => $metal,
                'rate_per_gram' => $metal === 'silver' ? $liveRates['silver'] : $liveRates['gold_24k'],
                'timestamp' => $liveRates['timestamp'],
                'source' => 'GoldAPI.io (cached)',
            ];
        }

        // return cached if exists
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $now = now();
        $rate = 0;

        $todayRate = \App\Models\GoldRate::getTodayRate();

        if ($metal === 'silver') {
            $rate = $todayRate ? $todayRate->silver_rate : 75.00;
        } else {
            // Default to 24K gold if metal is 'gold'
            $rate = $todayRate ? $todayRate->rate_24k : 5500.00;
        }

        $payload = [
            'metal' => $metal,
            'rate_per_gram' => (float) $rate,
            'timestamp' => $now->toDateTimeString(),
            'source' => $todayRate ? 'database' : 'default_mock',
        ];

        // cache for 5 minutes
        Cache::put($cacheKey, $payload, 300);

        return $payload;
    }
}

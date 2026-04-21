<?php

namespace App\Console\Commands;

use App\Models\GoldRate;
use App\Services\MarketRateService;
use Illuminate\Console\Command;

class UpdateMarketRates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rates:update';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Fetch live gold and silver rates and update database';

    /**
     * Execute the console command.
     */
    public function handle(MarketRateService $marketRateService)
    {
        $this->info('Fetching live market rates...');

        $rates = $marketRateService->fetchLiveRates();

        if (isset($rates['status']) && $rates['status'] === 'error') {
            $this->error('Error: '.$rates['message']);

            return 1;
        }

        $this->info('Gold (24K): '.$rates['gold_24k'].' '.$rates['currency']);
        $this->info('Silver: '.$rates['silver'].' '.$rates['currency']);

        $today = today();
        $goldRate = GoldRate::whereDate('date', $today)->first();

        if (! $goldRate) {
            $goldRate = new GoldRate;
            $goldRate->date = $today;
            $goldRate->status = 'draft'; // Require approval or set to approved based on policy
            $goldRate->created_by = 1;
        }

        $goldRate->calculateRatesFromBase($rates['gold_24k']);
        $goldRate->silver_rate = $rates['silver'];
        $goldRate->comment = 'Auto-updated from live market API';
        $goldRate->save();

        $this->info('Database updated successfully.');

        return 0;
    }
}

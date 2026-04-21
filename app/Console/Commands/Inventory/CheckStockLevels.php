<?php

namespace App\Console\Commands\Inventory;

use Illuminate\Console\Command;

class CheckStockLevels extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inventory:check-stock';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check all product stock levels and trigger alerts if below reorder levels';

    /**
     * Execute the console command.
     */
    public function handle(\App\Services\Inventory\ReorderService $reorderService)
    {
        $this->info('Starting stock level check...');
        $triggered = $reorderService->checkAndTriggerAlerts();
        $this->info("Stock check complete. {$triggered} new alerts triggered.");
    }
}

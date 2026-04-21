<?php

namespace App\Listeners\Inventory;

use App\Events\POS\SaleCompleted;
use App\Services\Inventory\InventoryService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UpdateInventoryAfterSale implements ShouldQueue
{
    use InteractsWithQueue;

    protected $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    public function handle(SaleCompleted $event): void
    {
        $this->inventoryService->reduceStockFromSale($event->sale);
    }
}

<?php

namespace App\Events\Inventory;

use App\Models\InventoryProduct;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StockUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $product;

    public $changeAmount;

    public $reason;

    public function __construct(InventoryProduct $product, $changeAmount, $reason = '')
    {
        $this->product = $product;
        $this->changeAmount = $changeAmount;
        $this->reason = $reason;
    }
}

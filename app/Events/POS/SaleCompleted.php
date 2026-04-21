<?php

namespace App\Events\POS;

use App\Models\PosSale;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SaleCompleted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $sale;

    public function __construct(PosSale $sale)
    {
        $this->sale = $sale;
    }
}

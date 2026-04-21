<?php

namespace App\Listeners\Accounting;

use App\Events\POS\SaleCompleted;
use App\Services\Accounting\SalesAccountingService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class PostSaleToLedger implements ShouldQueue
{
    use InteractsWithQueue;

    protected $accountingService;

    public function __construct(SalesAccountingService $accountingService)
    {
        $this->accountingService = $accountingService;
    }

    public function handle(SaleCompleted $event): void
    {
        $this->accountingService->postSale($event->sale);
    }
}

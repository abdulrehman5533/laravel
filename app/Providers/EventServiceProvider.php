<?php

namespace App\Providers;

use App\Events\POS\SaleCompleted;
use App\Listeners\Accounting\PostSaleToLedger;
use App\Listeners\Inventory\UpdateInventoryAfterSale;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        SaleCompleted::class => [
            PostSaleToLedger::class,
            UpdateInventoryAfterSale::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        parent::boot();
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}

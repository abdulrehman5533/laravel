<?php

namespace App\Console\Commands\Girvi;

use App\Services\GirviService;
use Illuminate\Console\Command;

class SendReminders extends Command
{
    protected $signature = 'girvi:send-reminders';

    protected $description = 'Automatically send SMS/WhatsApp reminders for Girvi due dates and maturity';

    protected $girviService;

    public function __construct(GirviService $girviService)
    {
        parent::__construct();
        $this->girviService = $girviService;
    }

    public function handle()
    {
        $this->info('Starting Girvi reminder dispatch...');
        $count = $this->girviService->sendReminders();
        $this->info("Successfully dispatched {$count} reminders.");
    }
}

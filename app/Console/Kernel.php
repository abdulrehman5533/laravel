<?php

namespace App\Console;

use App\Console\Commands\SendOverdueInstallmentReminders;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Girvi: Automatic Interest Posting (Daily at midnight)
        $schedule->command('girvi:post-interest')
            ->daily()
            ->onOneServer();

        // Girvi: Automated Reminders (Daily at 10 AM)
        $schedule->command('girvi:send-reminders')
            ->dailyAt('10:00')
            ->onOneServer();

        // Send overdue installment reminders daily at 9 AM
        $schedule->command(SendOverdueInstallmentReminders::class, ['--days' => 1, '--force'])
            ->dailyAt('09:00')
            ->timezone('Asia/Kolkata')
            ->onOneServer()
            ->withoutOverlapping(30);

        // Send weekly summary for overdue installments
        $schedule->command(SendOverdueInstallmentReminders::class, ['--days' => 7, '--force'])
            ->weeklyOn(1, '10:00') // Monday at 10 AM
            ->timezone('Asia/Kolkata')
            ->onOneServer()
            ->withoutOverlapping(30);
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}

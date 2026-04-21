<?php

namespace App\Console\Commands;

use App\Mail\OverdueInstallmentReminder;
use App\Models\InstallmentSchedule;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendOverdueInstallmentReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'installments:send-reminders
                            {--days=7 : Send reminders for installments overdue by X days or more}
                            {--force : Force send reminders without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send email reminders for overdue installments';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = $this->option('days');
        $force = $this->option('force');

        // Get all overdue installments
        $overdueSchedules = InstallmentSchedule::where('due_date', '<', now()->subDays($days))
            ->where('status', '!=', 'paid')
            ->whereHas('installment', fn ($q) => $q->where('status', 'active'))
            ->whereNotNull('customer_email')
            ->get();

        if ($overdueSchedules->isEmpty()) {
            $this->info('No overdue installments found.');

            return 0;
        }

        $count = $overdueSchedules->count();
        $this->warn("Found $count overdue installment(s) to remind about (overdue by $days+ days).");

        if (! $force && ! $this->confirm('Do you want to send reminders for these installments?')) {
            $this->info('Cancelled.');

            return 0;
        }

        $successCount = 0;
        $failedCount = 0;

        foreach ($overdueSchedules as $schedule) {
            try {
                // Get customer email
                $customerEmail = $schedule->customer_email ?? $schedule->installment->sale->customer->email;

                if (! $customerEmail) {
                    $this->warn("No email found for installment: {$schedule->installment->reference_number}");
                    $failedCount++;

                    continue;
                }

                // Send email
                Mail::to($customerEmail)->send(new OverdueInstallmentReminder($schedule));

                // Log reminder sent
                $schedule->update([
                    'last_reminder_sent_at' => now(),
                    'reminder_count' => ($schedule->reminder_count ?? 0) + 1,
                ]);

                $this->line("<info>✓</info> Reminder sent: {$schedule->installment->reference_number} → {$customerEmail}");
                $successCount++;
            } catch (\Exception $e) {
                $this->error("Failed to send reminder for: {$schedule->installment->reference_number} - {$e->getMessage()}");
                $failedCount++;
            }
        }

        $this->newLine();
        $this->info("✓ Reminders sent: $successCount");
        if ($failedCount > 0) {
            $this->error("✗ Failed: $failedCount");
        }

        return 0;
    }
}

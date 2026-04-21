<?php

namespace App\Console\Commands\POS;

use App\Models\PosSale;
use Illuminate\Console\Command;

class CheckExpiredMemos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pos:check-expired-memos';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Identify and flag memos that have passed their expiry date';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $expiredMemos = PosSale::where('is_memo', true)
            ->where('consignment_status', 'active')
            ->where('memo_expiry_date', '<', now()->toDateString())
            ->get();

        if ($expiredMemos->isEmpty()) {
            $this->info('No expired memos found.');

            return;
        }

        foreach ($expiredMemos as $memo) {
            $memo->update(['consignment_status' => 'overdue']);

            // Add audit log if model has the method
            if (method_exists($memo, 'logAudit')) {
                $memo->logAudit('memo_expired', 'Memo flagged as overdue automatically.');
            } elseif (method_exists($memo, 'addAuditLog')) {
                $memo->addAuditLog('memo_expired', 'Memo flagged as overdue automatically.');
            }

            $this->warn("Memo #{$memo->invoice_no} (Customer: ".($memo->customer->name ?? 'N/A').') is now OVERDUE.');
        }

        $this->info("Processed {$expiredMemos->count()} expired memos.");
    }
}

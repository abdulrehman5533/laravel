<?php

namespace App\Http\Controllers\Girvi;

use App\Http\Controllers\Controller;
use App\Models\Girvi;
use App\Services\GirviService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GirviBulkOperationsController extends Controller
{
    protected $girviService;

    public function __construct(GirviService $girviService)
    {
        $this->girviService = $girviService;
    }

    public function index()
    {
        $activeLoans = Girvi::where('status', 'active')->count();
        $overdueLoans = Girvi::where('status', 'active')
            ->where('maturity_date', '<', now())
            ->count();

        return view('girvi.bulk.index', compact('activeLoans', 'overdueLoans'));
    }

    public function bulkInterestPosting(Request $request)
    {
        $validated = $request->validate([
            'posting_date' => 'required|date',
            'loan_ids' => 'nullable|array',
            'loan_ids.*' => 'exists:girvis,id',
        ]);

        $query = Girvi::where('status', 'active');
        
        if (!empty($validated['loan_ids'])) {
            $query->whereIn('id', $validated['loan_ids']);
        }

        $loans = $query->get();
        $results = [
            'success' => 0,
            'failed' => 0,
            'total_interest' => 0,
            'errors' => [],
        ];

        DB::beginTransaction();
        try {
            foreach ($loans as $loan) {
                try {
                    $posting = $this->girviService->calculateAndPostInterest($loan, true);
                    if ($posting) {
                        $results['success']++;
                        $results['total_interest'] += $posting->interest_amount;
                    }
                } catch (\Exception $e) {
                    $results['failed']++;
                    $results['errors'][] = "Loan {$loan->girvi_number}: {$e->getMessage()}";
                }
            }
            DB::commit();
            
            return back()->with('success', "Interest posted for {$results['success']} loans. Total: Rs." . number_format($results['total_interest'], 2));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Bulk posting failed: ' . $e->getMessage());
        }
    }

    public function bulkReminders(Request $request)
    {
        $validated = $request->validate([
            'reminder_type' => 'nullable|in:due_soon,overdue,maturity',
            'channel' => 'nullable|in:sms,whatsapp,email',
            'girvi_ids' => 'nullable|array',
            'girvi_ids.*' => 'exists:girvis,id',
            'loan_ids' => 'nullable|array',
            'loan_ids.*' => 'exists:girvis,id',
        ]);

        // Support both girvi_ids (from index) and loan_ids (from bulk page)
        $ids = array_merge($validated['girvi_ids'] ?? [], $validated['loan_ids'] ?? []);
        $reminderType = $validated['reminder_type'] ?? 'overdue';
        $channel = $validated['channel'] ?? 'whatsapp';

        $query = Girvi::where('status', 'active')->with('customer');

        if (!empty($ids)) {
            $query->whereIn('id', $ids);
        } else {
            switch ($reminderType) {
                case 'due_soon':
                    $query->whereBetween('maturity_date', [now(), now()->addDays(7)]);
                    break;
                case 'overdue':
                    $query->where('maturity_date', '<', now());
                    break;
                case 'maturity':
                    $query->whereDate('maturity_date', now()->toDateString());
                    break;
            }
        }

        $loans = $query->get();
        $sent = 0;

        foreach ($loans as $loan) {
            $message = $this->generateReminderMessage($loan, $reminderType);
            \App\Models\GirviReminder::create([
                'girvi_id' => $loan->id,
                'channel' => $channel,
                'reminder_type' => $reminderType,
                'recipient_contact' => $loan->customer->phone,
                'message_content' => $message,
                'status' => 'pending',
            ]);
            $sent++;
        }

        return back()->with('success', "{$sent} reminders queued via {$channel}");
    }

    public function bulkStatusUpdate(Request $request)
    {
        $validated = $request->validate([
            'loan_ids' => 'required|array|min:1',
            'loan_ids.*' => 'exists:girvis,id',
            'new_status' => 'required|in:active,overdue,transferred',
        ]);

        $updated = Girvi::whereIn('id', $validated['loan_ids'])
            ->update(['status' => $validated['new_status']]);

        return back()->with('success', "{$updated} loans updated to status: {$validated['new_status']}");
    }

    private function generateReminderMessage(Girvi $loan, string $type): string
    {
        $customer = $loan->customer->name;
        $loanNumber = $loan->girvi_number;
        $outstanding = number_format($loan->outstanding_amount, 2);
        $maturityDate = $loan->maturity_date->format('d/m/Y');

        switch ($type) {
            case 'due_soon':
                return "Dear {$customer}, your Girvi loan {$loanNumber} is due on {$maturityDate}. Outstanding: Rs.{$outstanding}. Please make payment to avoid penalties.";
            case 'overdue':
                $daysOverdue = now()->diffInDays($loan->maturity_date);
                return "Dear {$customer}, your Girvi loan {$loanNumber} is overdue by {$daysOverdue} days. Outstanding: Rs.{$outstanding}. Please contact us immediately.";
            case 'maturity':
                return "Dear {$customer}, your Girvi loan {$loanNumber} matures today. Outstanding: Rs.{$outstanding}. Please visit our branch for settlement.";
            default:
                return "Reminder for Girvi loan {$loanNumber}";
        }
    }
}

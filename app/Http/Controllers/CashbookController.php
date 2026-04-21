<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Branch;
use App\Models\Cashbook;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class CashbookController extends Controller
{
    public function index(Request $request)
    {
        // CSV Export
        if ($request->export === 'csv') {
            return $this->exportCsv($request);
        }

        $query = Cashbook::query();

        if ($request->branch_id)   $query->where('branch_id', $request->branch_id);
        if ($request->from_date)   $query->whereDate('date', '>=', $request->from_date);
        if ($request->to_date)     $query->whereDate('date', '<=', $request->to_date);
        if ($request->entry_type)  $query->where('entry_type', $request->entry_type);
        if ($request->user_id)     $query->where('user_id', $request->user_id);
        if ($request->category)    $query->where('category', $request->category);
        if ($request->status)      $query->where('status', $request->status);

        $cashbooks = $query->with(['branch', 'user', 'verifiedBy'])
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(25);

        // Running balance — calculated from ALL entries (not just current page)
        $allEntries = Cashbook::orderBy('date', 'asc')->orderBy('id', 'asc')->get();
        $balanceMap = [];
        $running = 0;
        foreach ($allEntries as $e) {
            $running += $e->entry_type === 'cash_in' ? $e->amount : -$e->amount;
            $balanceMap[$e->id] = $running;
        }
        foreach ($cashbooks as $entry) {
            $entry->running_balance = $balanceMap[$entry->id] ?? 0;
        }

        // Summary stats (respecting filters)
        $statsQuery = Cashbook::query();
        if ($request->branch_id)  $statsQuery->where('branch_id', $request->branch_id);
        if ($request->from_date)  $statsQuery->whereDate('date', '>=', $request->from_date);
        if ($request->to_date)    $statsQuery->whereDate('date', '<=', $request->to_date);

        $totalInflows  = (clone $statsQuery)->where('entry_type', 'cash_in')->sum('amount');
        $totalOutflows = (clone $statsQuery)->where('entry_type', 'cash_out')->sum('amount');
        $pendingCount  = Cashbook::where('status', 'pending')->count();

        $branches   = Branch::where('is_active', true)->get();
        $users      = User::where('is_active', true)->get();
        $categories = Cashbook::distinct()->pluck('category')->filter()->sort()->values();

        $filterLabel = $request->from_date || $request->to_date
            ? 'Filtered period'
            : 'All time';

        return view('accounts.cashbook.index', compact(
            'cashbooks', 'branches', 'users', 'categories',
            'totalInflows', 'totalOutflows', 'pendingCount', 'filterLabel'
        ));
    }

    public function create(): View
    {
        $branches = Branch::where('is_active', true)->get();
        return view('accounts.cashbook.create', compact('branches'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'branch_id'      => 'required|exists:branches,id',
            'date'           => 'required|date',
            'entry_type'     => 'required|in:cash_in,cash_out',
            'category'       => 'required|string|max:100',
            'subcategory'    => 'nullable|string|max:100',
            'amount'         => 'required|numeric|min:0.01',
            'payment_method' => 'required|string|max:50',
            'description'    => 'nullable|string|max:500',
            'reference_type' => 'nullable|string|max:50',
            'reference_id'   => 'nullable|string|max:100',
        ]);

        $validated['user_id'] = Auth::id();
        $validated['status']  = 'pending';

        $cashbook = Cashbook::create($validated);
        $this->logActivity('CREATE', $cashbook->id, null, $validated);

        return redirect()->route('accounts.cashbook.index')
            ->with('success', 'Cash entry recorded successfully.');
    }

    public function show(Cashbook $cashbook): View
    {
        $cashbook->load(['branch', 'user', 'verifiedBy']);
        return view('accounts.cashbook.show', compact('cashbook'));
    }

    public function edit(Cashbook $cashbook): View|RedirectResponse
    {
        if ($cashbook->status === 'verified') {
            return back()->with('error', 'Verified entries cannot be edited.');
        }
        $branches = Branch::where('is_active', true)->get();
        return view('accounts.cashbook.create', compact('cashbook', 'branches'));
    }

    public function update(Request $request, Cashbook $cashbook): RedirectResponse
    {
        if ($cashbook->status === 'verified') {
            return back()->with('error', 'Verified entries cannot be edited.');
        }

        $validated = $request->validate([
            'branch_id'      => 'required|exists:branches,id',
            'date'           => 'required|date',
            'entry_type'     => 'required|in:cash_in,cash_out',
            'category'       => 'required|string|max:100',
            'subcategory'    => 'nullable|string|max:100',
            'amount'         => 'required|numeric|min:0.01',
            'payment_method' => 'required|string|max:50',
            'description'    => 'nullable|string|max:500',
            'reference_type' => 'nullable|string|max:50',
            'reference_id'   => 'nullable|string|max:100',
        ]);

        $old = $cashbook->toArray();
        $cashbook->update($validated);
        $this->logActivity('UPDATE', $cashbook->id, $old, $validated);

        return redirect()->route('accounts.cashbook.index')
            ->with('success', 'Cash entry updated successfully.');
    }

    public function destroy(Cashbook $cashbook): RedirectResponse
    {
        if ($cashbook->status === 'verified') {
            return back()->with('error', 'Verified entries cannot be deleted.');
        }

        $this->logActivity('DELETE', $cashbook->id, $cashbook->toArray(), null);
        $cashbook->delete();

        return redirect()->route('accounts.cashbook.index')
            ->with('success', 'Entry deleted.');
    }

    // Verify single entry
    public function verify(Cashbook $cashbook): RedirectResponse
    {
        if ($cashbook->status === 'verified') {
            return back()->with('error', 'Already verified.');
        }

        $cashbook->update([
            'status'      => 'verified',
            'verified_by' => Auth::id(),
            'verified_at' => now(),
        ]);

        $this->logActivity('VERIFY', $cashbook->id, null, ['status' => 'verified']);

        return back()->with('success', "Entry #CB-{$cashbook->id} verified.");
    }

    // Verify all pending entries
    public function verifyAll(): RedirectResponse
    {
        $count = Cashbook::where('status', 'pending')->count();

        Cashbook::where('status', 'pending')->update([
            'status'      => 'verified',
            'verified_by' => Auth::id(),
            'verified_at' => now(),
        ]);

        return back()->with('success', "{$count} entries verified successfully.");
    }

    private function exportCsv(Request $request)
    {
        $query = Cashbook::with(['branch', 'user']);

        if ($request->branch_id)  $query->where('branch_id', $request->branch_id);
        if ($request->from_date)  $query->whereDate('date', '>=', $request->from_date);
        if ($request->to_date)    $query->whereDate('date', '<=', $request->to_date);
        if ($request->entry_type) $query->where('entry_type', $request->entry_type);
        if ($request->category)   $query->where('category', $request->category);

        $rows = $query->orderBy('date', 'asc')->get();

        $filename = 'cashbook_' . now()->format('Ymd_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($rows) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Ref#', 'Date', 'Branch', 'Type', 'Category', 'Sub-Category', 'Amount', 'Payment Method', 'Status', 'User', 'Description', 'Reference']);

            $running = 0;
            foreach ($rows as $row) {
                $running += $row->entry_type === 'cash_in' ? $row->amount : -$row->amount;
                fputcsv($file, [
                    '#CB-' . str_pad($row->id, 5, '0', STR_PAD_LEFT),
                    $row->date->format('d M Y'),
                    $row->branch->name ?? '',
                    $row->entry_type === 'cash_in' ? 'Cash In' : 'Cash Out',
                    $row->category,
                    $row->subcategory ?? '',
                    $row->amount,
                    $row->payment_method,
                    ucfirst($row->status),
                    $row->user->name ?? '',
                    $row->description ?? '',
                    $row->reference_type ? $row->reference_type . ' #' . $row->reference_id : '',
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function logActivity($action, $id, $old, $new)
    {
        try {
            AuditLog::create([
                'user_id'     => Auth::id(),
                'action'      => $action,
                'module'      => 'Cashbook',
                'entity_type' => 'Cashbook',
                'entity_id'   => $id,
                'old_values'  => $old ? json_encode($old) : null,
                'new_values'  => $new ? json_encode($new) : null,
                'ip_address'  => request()->ip(),
                'user_agent'  => request()->userAgent(),
            ]);
        } catch (\Exception $e) {
            Log::error('Cashbook audit log failed: ' . $e->getMessage());
        }
    }
}

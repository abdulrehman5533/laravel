<?php

namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use App\Models\CashierShift;
use App\Models\PosSale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CashierShiftController extends Controller
{
    public function index()
    {
        $shifts       = CashierShift::with('cashier', 'branch')->latest()->paginate(20);
        $activeShift  = CashierShift::where('cashier_id', Auth::id())->where('status', 'open')->first();
        $branches     = \App\Models\Branch::where('is_active', true)->get();
        return view('pos.shifts.index', compact('shifts', 'activeShift', 'branches'));
    }

    public function open(Request $request)
    {
        $request->validate([
            'opening_balance' => 'required|numeric|min:0',
            'branch_id'       => 'required|exists:branches,id',
        ]);

        // Check if already has open shift
        $existing = CashierShift::where('cashier_id', Auth::id())->where('status', 'open')->first();
        if ($existing) {
            return redirect()->back()->with('error', 'You already have an open shift. Close it first.');
        }

        CashierShift::create([
            'cashier_id'      => Auth::id(),
            'branch_id'       => $request->branch_id,
            'shift_date'      => today(),
            'opened_at'       => now(),
            'opening_balance' => $request->opening_balance,
            'status'          => 'open',
        ]);

        return redirect()->route('pos.shifts.index')->with('success', 'Shift opened successfully.');
    }

    public function close(Request $request, CashierShift $shift)
    {
        $request->validate([
            'physical_count' => 'required|numeric|min:0',
            'notes'          => 'nullable|string|max:500',
        ]);

        if ($shift->cashier_id !== Auth::id()) {
            return redirect()->back()->with('error', 'You can only close your own shift.');
        }

        // Calculate totals from sales during this shift
        $salesDuring = PosSale::where('branch_id', $shift->branch_id)
            ->where('created_by', $shift->cashier_id)
            ->where('status', 'completed')
            ->whereBetween('sale_time', [$shift->opened_at, now()])
            ->get();

        $totalCashIn  = $salesDuring->where('payment_method', 'cash')->sum('total');
        $closingBalance = $shift->opening_balance + $totalCashIn;
        $variance     = $request->physical_count - $closingBalance;

        $shift->update([
            'closed_at'       => now(),
            'total_cash_in'   => $totalCashIn,
            'closing_balance' => $closingBalance,
            'physical_count'  => $request->physical_count,
            'variance'        => $variance,
            'status'          => 'closed',
            'notes'           => $request->notes,
        ]);

        return redirect()->route('pos.shifts.show', $shift)->with('success', 'Shift closed successfully.');
    }

    public function show(CashierShift $shift)
    {
        $shift->load('cashier', 'branch');

        $salesDuring = PosSale::where('branch_id', $shift->branch_id)
            ->where('created_by', $shift->cashier_id)
            ->where('status', 'completed')
            ->whereBetween('sale_time', [$shift->opened_at, $shift->closed_at ?? now()])
            ->with('customer')
            ->get();

        return view('pos.shifts.show', compact('shift', 'salesDuring'));
    }
}

<?php

namespace App\Http\Controllers\Accounts;

use App\Http\Controllers\Controller;
use App\Models\PurchaseAnalytic;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\SupplierCreditLimit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PurchaseAnalyticsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function dashboard(Request $request)
    {
        $branchId = Auth::user()->branch_id;
        $year = $request->input('year', now()->year);
        $month = $request->input('month', now()->month);

        $analytics = PurchaseAnalytic::where('branch_id', $branchId)
            ->where('analysis_year', $year)
            ->where('analysis_month', $month)
            ->get();

        $totalOrders = PurchaseOrder::where('branch_id', $branchId)
            ->whereYear('po_date', $year)
            ->whereMonth('po_date', $month)
            ->count();

        $totalAmount = PurchaseOrder::where('branch_id', $branchId)
            ->whereYear('po_date', $year)
            ->whereMonth('po_date', $month)
            ->sum('total_amount');

        $overdueOrders = PurchaseOrder::where('branch_id', $branchId)
            ->where('is_overdue', true)
            ->count();

        $pendingPayments = PurchaseOrder::where('branch_id', $branchId)
            ->whereIn('payment_status', ['Unpaid', 'Partial', 'Overdue'])
            ->sum('amount_due');

        $statusCounts = PurchaseOrder::where('branch_id', $branchId)
            ->whereYear('po_date', $year)
            ->whereMonth('po_date', $month)
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        $paymentStatusCounts = PurchaseOrder::where('branch_id', $branchId)
            ->whereYear('po_date', $year)
            ->whereMonth('po_date', $month)
            ->selectRaw('payment_status, count(*) as count')
            ->groupBy('payment_status')
            ->pluck('count', 'payment_status');

        return view('accounts.analytics.dashboard', compact(
            'analytics',
            'totalOrders',
            'totalAmount',
            'overdueOrders',
            'pendingPayments',
            'statusCounts',
            'paymentStatusCounts',
            'year',
            'month'
        ));
    }

    public function trends(Request $request)
    {
        $branchId = Auth::user()->branch_id;
        $months = $request->input('months', 12);

        $trends = PurchaseAnalytic::where('branch_id', $branchId)
            ->orderBy('analysis_year')
            ->orderBy('analysis_month')
            ->limit($months)
            ->get();

        $chartData = [
            'labels' => $trends->map(fn ($t) => $t->getPeriodLabel()),
            'purchases' => $trends->map(fn ($t) => $t->total_purchase_amount),
            'payments' => $trends->map(fn ($t) => $t->amount_paid),
        ];

        return view('accounts.analytics.trends', compact('chartData', 'trends'));
    }

    public function supplierPerformance(Request $request)
    {
        $branchId = Auth::user()->branch_id;

        $suppliers = Supplier::where('branch_id', $branchId)
            ->with(['analytics' => function ($q) {
                $q->latest('analysis_year', 'analysis_month')->limit(1);
            }])
            ->orderByDesc('rating')
            ->get();

        return view('accounts.analytics.supplier-performance', compact('suppliers'));
    }

    public function overdueAlerts(Request $request)
    {
        $branchId = Auth::user()->branch_id;

        $overdueOrders = PurchaseOrder::where('branch_id', $branchId)
            ->where('is_overdue', true)
            ->with('supplier')
            ->orderBy('days_overdue', 'desc')
            ->paginate(20);

        return view('accounts.analytics.overdue-alerts', compact('overdueOrders'));
    }

    public function creditStatus(Request $request)
    {
        $branchId = Auth::user()->branch_id;

        $creditLimits = SupplierCreditLimit::where('branch_id', $branchId)
            ->with('supplier')
            ->get();

        $exceeding = $creditLimits->filter(fn ($c) => $c->isExceedingLimit());
        $withOverdue = $creditLimits->filter(fn ($c) => $c->has_overdue);
        $healthy = $creditLimits->filter(fn ($c) => ! $c->isExceedingLimit() && ! $c->has_overdue);

        return view('accounts.analytics.credit-status', compact(
            'creditLimits',
            'exceeding',
            'withOverdue',
            'healthy'
        ));
    }
}

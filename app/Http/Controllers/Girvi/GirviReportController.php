<?php

namespace App\Http\Controllers\Girvi;

use App\Http\Controllers\Controller;
use App\Models\Girvi;
use App\Models\GirviPayment;
use App\Models\GirviInterestPosting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GirviReportController extends Controller
{
    public function dailyRegister(Request $request)
    {
        $date = $request->date ?? now()->toDateString();
        
        $newLoans = Girvi::whereDate('girvi_date', $date)
            ->with(['customer', 'branch', 'items'])
            ->get();

        $payments = GirviPayment::whereDate('payment_date', $date)
            ->with(['girvi.customer'])
            ->get();

        $closures = Girvi::whereDate('updated_at', $date)
            ->where('status', 'settled')
            ->with(['customer', 'branch'])
            ->get();

        $summary = [
            'new_loans_count' => $newLoans->count(),
            'new_loans_amount' => $newLoans->sum('loan_amount'),
            'payments_count' => $payments->count(),
            'payments_amount' => $payments->sum('amount'),
            'interest_collected' => $payments->sum('interest_component'),
            'principal_collected' => $payments->sum('principal_component'),
            'closures_count' => $closures->count(),
        ];

        return view('girvi.reports.daily-register', compact('date', 'newLoans', 'payments', 'closures', 'summary'));
    }

    public function interestCollection(Request $request)
    {
        $startDate = $request->start_date ?? now()->startOfMonth()->toDateString();
        $endDate = $request->end_date ?? now()->toDateString();

        $collections = GirviPayment::whereBetween('payment_date', [$startDate, $endDate])
            ->with(['girvi.customer', 'girvi.branch'])
            ->get();

        $summary = [
            'total_interest' => $collections->sum('interest_component'),
            'total_principal' => $collections->sum('principal_component'),
            'total_penalty' => $collections->sum('penalty_component'),
            'total_amount' => $collections->sum('amount'),
            'transaction_count' => $collections->count(),
        ];

        $branchWise = $collections->groupBy('girvi.branch.name')->map(function ($items) {
            return [
                'count' => $items->count(),
                'interest' => $items->sum('interest_component'),
                'principal' => $items->sum('principal_component'),
                'total' => $items->sum('amount'),
            ];
        });

        return view('girvi.reports.interest-collection', compact('startDate', 'endDate', 'collections', 'summary', 'branchWise'));
    }

    public function overdueLoans(Request $request)
    {
        $overdueLoans = Girvi::where('status', 'active')
            ->where('maturity_date', '<', now())
            ->with(['customer', 'branch', 'items'])
            ->get()
            ->map(function ($girvi) {
                $girvi->days_overdue = now()->diffInDays($girvi->maturity_date);
                $girvi->aging_bucket = $this->getAgingBucket($girvi->days_overdue);
                return $girvi;
            });

        $summary = [
            'total_count' => $overdueLoans->count(),
            'total_outstanding' => $overdueLoans->sum('outstanding_amount'),
            'bucket_0_30' => $overdueLoans->where('aging_bucket', '0-30')->count(),
            'bucket_31_90' => $overdueLoans->where('aging_bucket', '31-90')->count(),
            'bucket_90_plus' => $overdueLoans->where('aging_bucket', '90+')->count(),
        ];

        return view('girvi.reports.overdue', compact('overdueLoans', 'summary'));
    }

    public function settledLoans(Request $request)
    {
        $startDate = $request->start_date ?? now()->startOfMonth()->toDateString();
        $endDate = $request->end_date ?? now()->toDateString();

        $settledLoans = Girvi::where('status', 'settled')
            ->whereBetween('updated_at', [$startDate, $endDate])
            ->with(['customer', 'branch', 'payments'])
            ->get();

        $summary = [
            'total_count' => $settledLoans->count(),
            'total_principal' => $settledLoans->sum('loan_amount'),
            'total_interest_earned' => $settledLoans->sum('interest_paid'),
            'average_duration' => $settledLoans->avg(function ($girvi) {
                return $girvi->girvi_date->diffInDays($girvi->updated_at);
            }),
        ];

        return view('girvi.reports.settled', compact('startDate', 'endDate', 'settledLoans', 'summary'));
    }

    public function customerHistory(Request $request)
    {
        $customerId = $request->customer_id;
        
        if (!$customerId) {
            return view('girvi.reports.customer-history', ['customer' => null]);
        }

        $customer = \App\Models\Customer::findOrFail($customerId);
        
        $loans = Girvi::where('customer_id', $customerId)
            ->with(['branch', 'items', 'payments'])
            ->orderBy('girvi_date', 'desc')
            ->get();

        $summary = [
            'total_loans' => $loans->count(),
            'active_loans' => $loans->where('status', 'active')->count(),
            'total_borrowed' => $loans->sum('loan_amount'),
            'total_outstanding' => $loans->where('status', 'active')->sum('outstanding_amount'),
            'total_interest_paid' => $loans->sum('interest_paid'),
        ];

        return view('girvi.reports.customer-history', compact('customer', 'loans', 'summary'));
    }

    public function branchPerformance(Request $request)
    {
        $startDate = $request->start_date ?? now()->startOfMonth()->toDateString();
        $endDate = $request->end_date ?? now()->toDateString();

        $branchData = Girvi::whereBetween('girvi_date', [$startDate, $endDate])
            ->with('branch')
            ->get()
            ->groupBy('branch_id')
            ->map(function ($loans, $branchId) {
                $branch = $loans->first()->branch;
                return [
                    'branch_name' => $branch->name,
                    'loans_count' => $loans->count(),
                    'total_disbursed' => $loans->sum('loan_amount'),
                    'active_count' => $loans->where('status', 'active')->count(),
                    'settled_count' => $loans->where('status', 'settled')->count(),
                    'outstanding' => $loans->where('status', 'active')->sum('outstanding_amount'),
                ];
            });

        return view('girvi.reports.branch-performance', compact('startDate', 'endDate', 'branchData'));
    }

    public function metalAnalysis(Request $request)
    {
        $metalData = DB::table('girvi_items')
            ->join('girvis', 'girvi_items.girvi_id', '=', 'girvis.id')
            ->where('girvis.status', 'active')
            ->select(
                'girvi_items.item_type',
                DB::raw('COUNT(*) as item_count'),
                DB::raw('SUM(girvi_items.gross_weight) as total_gross_weight'),
                DB::raw('SUM(girvi_items.net_weight) as total_net_weight'),
                DB::raw('SUM(girvi_items.estimated_value) as total_value')
            )
            ->groupBy('girvi_items.item_type')
            ->get();

        return view('girvi.reports.metal-analysis', compact('metalData'));
    }

    private function getAgingBucket($days)
    {
        if ($days <= 30) return '0-30';
        if ($days <= 90) return '31-90';
        return '90+';
    }
}

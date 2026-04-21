<?php

namespace App\Http\Controllers\Girvi;

use App\Http\Controllers\Controller;
use App\Models\Girvi;
use App\Services\GirviService;
use Carbon\Carbon;

class GirviDashboardController extends Controller
{
    protected $girviService;

    public function __construct(GirviService $girviService)
    {
        $this->girviService = $girviService;
    }

    public function index()
    {
        $stats = [
            'active_count' => Girvi::where('status', 'active')->count(),
            'total_principal' => Girvi::where('status', 'active')->sum('loan_amount'),
            'total_outstanding' => Girvi::where('status', 'active')->sum('outstanding_amount'),
            'overdue_count' => Girvi::where('status', 'overdue')->count(),
            'total_interest_earned' => Girvi::sum('interest_paid'),
            'total_interest_accrued' => Girvi::sum('interest_accrued'),
        ];

        $stats['collection_efficiency'] = $stats['total_interest_accrued'] > 0
            ? ($stats['total_interest_earned'] / $stats['total_interest_accrued']) * 100
            : 0;

        $recentGirvis = Girvi::with('customer')->latest()->limit(5)->get();

        // Aging Chart Data (0-30, 31-90, 90+)
        $agingData = [
            '0-30' => Girvi::where('status', 'active')->where('girvi_date', '>=', now()->subDays(30))->count(),
            '31-90' => Girvi::where('status', 'active')->whereBetween('girvi_date', [now()->subDays(90), now()->subDays(31)])->count(),
            '90+' => Girvi::where('status', 'active')->where('girvi_date', '<', now()->subDays(90))->count(),
        ];

        // Collateral Distribution
        $collateralData = \App\Models\GirviItem::select('item_type', \DB::raw('count(*) as count'), \DB::raw('sum(net_weight) as total_weight'))
            ->groupBy('item_type')
            ->get();

        // High Risk Girvis (LTV > 85%)
        $highRiskGirvis = Girvi::with('customer')
            ->where('status', 'active')
            ->where('ltv_ratio', '>', 85)
            ->orderBy('ltv_ratio', 'desc')
            ->limit(5)
            ->get();

        // Disbursement Trend (Last 6 Months)
        $disbursementTrend = Girvi::select(
            \DB::raw("DATE_FORMAT(girvi_date, '%Y-%m') as month"),
            \DB::raw('sum(loan_amount) as total')
        )
            ->where('girvi_date', '>=', now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return view('girvi.dashboard', compact('stats', 'recentGirvis', 'agingData', 'collateralData', 'disbursementTrend', 'highRiskGirvis'));
    }

    public function agingReport()
    {
        $girvis = Girvi::with('customer')
            ->where('status', 'active')
            ->get()
            ->map(function ($girvi) {
                $girvi->days_old = Carbon::parse($girvi->girvi_date)->diffInDays(now());
                $girvi->risk_indicator = $girvi->days_old > 90 ? 'High' : ($girvi->days_old > 30 ? 'Medium' : 'Low');

                return $girvi;
            });

        return view('girvi.reports.aging', compact('girvis'));
    }

    public function forceSync()
    {
        try {
            $updated = $this->girviService->updateStatuses();
            $interestCount = $this->girviService->postInterestForAllActive();

            return back()->with('success', "Sync complete. {$updated} statuses updated and interest posted for {$interestCount} loans.");
        } catch (\Exception $e) {
            return back()->with('error', 'Sync failed: '.$e->getMessage());
        }
    }
}

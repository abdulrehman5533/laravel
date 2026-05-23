<?php

namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\PosSale;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class POSDashboardController extends Controller
{
    public function dailySummary(Request $request)
    {
        $date     = $request->get('date', today()->format('Y-m-d'));
        $branchId = $request->get('branch_id');

        $query = PosSale::whereDate('sale_time', $date)->where('status', 'completed');
        if ($branchId) $query->where('branch_id', $branchId);

        $sales = $query->with(['customer', 'payments'])->get();

        // Payment method breakdown
        $paymentBreakdown = DB::table('pos_payments')
            ->join('pos_sales', 'pos_payments.pos_sale_id', '=', 'pos_sales.id')
            ->whereDate('pos_sales.sale_time', $date)
            ->where('pos_sales.status', 'completed')
            ->when($branchId, fn($q) => $q->where('pos_sales.branch_id', $branchId))
            ->select('pos_payments.payment_method', DB::raw('SUM(pos_payments.amount) as total'))
            ->groupBy('pos_payments.payment_method')
            ->get();

        // Hourly sales
        $hourlySales = $sales->groupBy(fn($s) => Carbon::parse($s->sale_time)->format('H:00'))
            ->map(fn($g) => ['count' => $g->count(), 'total' => $g->sum('total')]);

        // Top products sold today
        $topProducts = DB::table('pos_sale_items')
            ->join('pos_sales', 'pos_sale_items.pos_sale_id', '=', 'pos_sales.id')
            ->whereDate('pos_sales.sale_time', $date)
            ->where('pos_sales.status', 'completed')
            ->when($branchId, fn($q) => $q->where('pos_sales.branch_id', $branchId))
            ->select('pos_sale_items.description', DB::raw('SUM(pos_sale_items.quantity) as qty'), DB::raw('SUM(pos_sale_items.line_total) as total'))
            ->groupBy('pos_sale_items.description')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        $summary = [
            'date'              => $date,
            'total_sales'       => $sales->count(),
            'total_revenue'     => $sales->sum('total'),
            'total_discount'    => $sales->sum('discount'),
            'total_tax'         => $sales->sum('tax_amount'),
            'cash_sales'        => $sales->where('payment_method', 'cash')->sum('total'),
            'card_sales'        => $sales->where('payment_method', 'card')->sum('total'),
            'unpaid_amount'     => $sales->sum('outstanding_balance'),
            'avg_sale_value'    => $sales->count() > 0 ? round($sales->sum('total') / $sales->count(), 2) : 0,
            'payment_breakdown' => $paymentBreakdown,
            'hourly_sales'      => $hourlySales,
            'top_products'      => $topProducts,
        ];

        $branches = \App\Models\Branch::where('is_active', true)->get();

        return view('pos.reports.daily-summary', compact('summary', 'sales', 'branches', 'date'));
    }

    public function outstandingCustomers(Request $request)
    {
        $customers = Customer::where('current_balance', '>', 0)
            ->with(['sales' => fn($q) => $q->where('payment_status', '!=', 'paid')->where('status', 'completed')])
            ->orderByDesc('current_balance')
            ->paginate(20);

        $totalOutstanding = Customer::sum('current_balance');
        $totalCustomers   = Customer::where('current_balance', '>', 0)->count();

        // Overdue (sales older than 30 days unpaid)
        $overdueCount = PosSale::where('payment_status', '!=', 'paid')
            ->where('status', 'completed')
            ->where('sale_time', '<', now()->subDays(30))
            ->count();

        return view('pos.reports.outstanding-customers', compact(
            'customers', 'totalOutstanding', 'totalCustomers', 'overdueCount'
        ));
    }

    public function salesByStaff(Request $request)
    {
        $fromDate = $request->get('from_date', today()->startOfMonth()->format('Y-m-d'));
        $toDate   = $request->get('to_date', today()->format('Y-m-d'));

        $staffSales = DB::table('pos_sales')
            ->join('users', 'pos_sales.created_by', '=', 'users.id')
            ->whereBetween('pos_sales.sale_time', [$fromDate.' 00:00:00', $toDate.' 23:59:59'])
            ->where('pos_sales.status', 'completed')
            ->select(
                'users.id',
                'users.name',
                DB::raw('COUNT(pos_sales.id) as total_sales'),
                DB::raw('SUM(pos_sales.total) as total_revenue'),
                DB::raw('SUM(pos_sales.discount) as total_discount'),
                DB::raw('AVG(pos_sales.total) as avg_sale')
            )
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('total_revenue')
            ->get();

        return view('pos.reports.sales-by-staff', compact('staffSales', 'fromDate', 'toDate'));
    }
}

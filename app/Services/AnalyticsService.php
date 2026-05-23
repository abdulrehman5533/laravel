<?php

namespace App\Services;

use App\Models\PosSale;
use App\Models\InventoryProduct;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\PurchaseOrder;
use App\Models\ServiceJob;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AnalyticsService
{
    /**
     * Get sales analytics
     */
    public function getSalesAnalytics($days = 30)
    {
        try {
            $startDate = Carbon::now()->subDays($days);

            // Daily sales
            $dailySales = PosSale::selectRaw('DATE(created_at) as date, SUM(total) as total, COUNT(*) as count')
                ->where('created_at', '>=', $startDate)
                ->groupBy('date')
                ->orderBy('date')
                ->get();

            // Total sales
            $totalSales = PosSale::where('created_at', '>=', $startDate)->sum('total');
            $avgDailySales = $dailySales->avg('total');
            $maxDailySales = $dailySales->max('total');
            $minDailySales = $dailySales->min('total');

            // Sales by customer
            $topCustomers = PosSale::selectRaw('pos_customer_id, SUM(total) as total, COUNT(*) as count')
                ->where('created_at', '>=', $startDate)
                ->groupBy('pos_customer_id')
                ->orderByDesc('total')
                ->limit(10)
                ->with('customer')
                ->get();

            return [
                'status' => 'success',
                'data' => [
                    'total_sales' => $totalSales,
                    'avg_daily_sales' => $avgDailySales,
                    'max_daily_sales' => $maxDailySales,
                    'min_daily_sales' => $minDailySales,
                    'daily_sales' => $dailySales,
                    'top_customers' => $topCustomers,
                    'period_days' => $days
                ]
            ];
        } catch (\Exception $e) {
            Log::error('Sales analytics error: ' . $e->getMessage());
            return ['status' => 'error', 'message' => 'Analytics حاصل کرنے میں خرابی'];
        }
    }

    /**
     * Get inventory analytics
     */
    public function getInventoryAnalytics()
    {
        try {
            $totalProducts = InventoryProduct::count();
            $totalStock = InventoryProduct::sum('current_stock');
            $totalValue = InventoryProduct::sum(DB::raw('current_stock * selling_price'));

            // Low stock products
            $lowStockProducts = InventoryProduct::where('current_stock', '<=', DB::raw('reorder_level'))
                ->count();

            // Stock by category
            $stockByCategory = InventoryProduct::selectRaw('category_id, COUNT(*) as count, SUM(current_stock) as total_stock')
                ->groupBy('category_id')
                ->with('category')
                ->get();

            // Top products by value
            $topProducts = InventoryProduct::selectRaw('name, current_stock, selling_price, (current_stock * selling_price) as value')
                ->orderByDesc('value')
                ->limit(10)
                ->get();

            return [
                'status' => 'success',
                'data' => [
                    'total_products' => $totalProducts,
                    'total_stock' => $totalStock,
                    'total_value' => $totalValue,
                    'low_stock_count' => $lowStockProducts,
                    'stock_by_category' => $stockByCategory,
                    'top_products' => $topProducts
                ]
            ];
        } catch (\Exception $e) {
            Log::error('Inventory analytics error: ' . $e->getMessage());
            return ['status' => 'error', 'message' => 'Analytics حاصل کرنے میں خرابی'];
        }
    }

    /**
     * Get customer analytics
     */
    public function getCustomerAnalytics()
    {
        try {
            $totalCustomers = Customer::count();
            $activeCustomers = Customer::where('is_active', true)->count();
            $inactiveCustomers = Customer::where('is_active', false)->count();

            // Customer spending
            $totalSpending = PosSale::sum('total');
            $avgCustomerSpending = $totalCustomers > 0 ? $totalSpending / $totalCustomers : 0;

            // Top customers
            $topCustomers = Customer::selectRaw('name, total_purchases')
                ->orderByDesc('total_purchases')
                ->limit(10)
                ->get();

            // New customers this month
            $newCustomersThisMonth = Customer::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count();

            return [
                'status' => 'success',
                'data' => [
                    'total_customers' => $totalCustomers,
                    'active_customers' => $activeCustomers,
                    'inactive_customers' => $inactiveCustomers,
                    'total_spending' => $totalSpending,
                    'avg_customer_spending' => $avgCustomerSpending,
                    'top_customers' => $topCustomers,
                    'new_customers_this_month' => $newCustomersThisMonth
                ]
            ];
        } catch (\Exception $e) {
            Log::error('Customer analytics error: ' . $e->getMessage());
            return ['status' => 'error', 'message' => 'Analytics حاصل کرنے میں خرابی'];
        }
    }

    /**
     * Get employee analytics
     */
    public function getEmployeeAnalytics()
    {
        try {
            $totalEmployees = Employee::count();
            $activeEmployees = Employee::where('status', 'active')->count();
            $inactiveEmployees = Employee::where('status', 'inactive')->count();

            // Salary analytics
            $totalSalary = Employee::sum('salary');
            $avgSalary = $totalEmployees > 0 ? $totalSalary / $totalEmployees : 0;
            $maxSalary = Employee::max('salary');
            $minSalary = Employee::min('salary');

            // Employees by position
            $employeesByPosition = Employee::selectRaw('position, COUNT(*) as count, AVG(salary) as avg_salary')
                ->groupBy('position')
                ->get();

            return [
                'status' => 'success',
                'data' => [
                    'total_employees' => $totalEmployees,
                    'active_employees' => $activeEmployees,
                    'inactive_employees' => $inactiveEmployees,
                    'total_salary' => $totalSalary,
                    'avg_salary' => $avgSalary,
                    'max_salary' => $maxSalary,
                    'min_salary' => $minSalary,
                    'employees_by_position' => $employeesByPosition
                ]
            ];
        } catch (\Exception $e) {
            Log::error('Employee analytics error: ' . $e->getMessage());
            return ['status' => 'error', 'message' => 'Analytics حاصل کرنے میں خرابی'];
        }
    }

    /**
     * Get purchase analytics
     */
    public function getPurchaseAnalytics($days = 30)
    {
        try {
            $startDate = Carbon::now()->subDays($days);

            // Total purchases
            $totalPurchases = PurchaseOrder::where('created_at', '>=', $startDate)->sum('total_amount');
            $totalOrders = PurchaseOrder::where('created_at', '>=', $startDate)->count();
            $avgOrderValue = $totalOrders > 0 ? $totalPurchases / $totalOrders : 0;

            // Purchases by status
            $purchasesByStatus = PurchaseOrder::selectRaw('status, COUNT(*) as count, SUM(total_amount) as total')
                ->where('created_at', '>=', $startDate)
                ->groupBy('status')
                ->get();

            // Top suppliers
            $topSuppliers = PurchaseOrder::selectRaw('supplier_id, COUNT(*) as count, SUM(total_amount) as total')
                ->where('created_at', '>=', $startDate)
                ->groupBy('supplier_id')
                ->orderByDesc('total')
                ->limit(10)
                ->with('supplier')
                ->get();

            return [
                'status' => 'success',
                'data' => [
                    'total_purchases' => $totalPurchases,
                    'total_orders' => $totalOrders,
                    'avg_order_value' => $avgOrderValue,
                    'purchases_by_status' => $purchasesByStatus,
                    'top_suppliers' => $topSuppliers,
                    'period_days' => $days
                ]
            ];
        } catch (\Exception $e) {
            Log::error('Purchase analytics error: ' . $e->getMessage());
            return ['status' => 'error', 'message' => 'Analytics حاصل کرنے میں خرابی'];
        }
    }

    /**
     * Get service analytics
     */
    public function getServiceAnalytics()
    {
        try {
            $totalJobs = ServiceJob::count();
            $completedJobs = ServiceJob::where('status', 'completed')->count();
            $pendingJobs = ServiceJob::where('status', 'pending')->count();

            // Revenue
            $totalRevenue = ServiceJob::sum('final_charge');
            $avgJobValue = $totalJobs > 0 ? $totalRevenue / $totalJobs : 0;

            // Jobs by status
            $jobsByStatus = ServiceJob::selectRaw('status, COUNT(*) as count, SUM(final_charge) as total')
                ->groupBy('status')
                ->get();

            return [
                'status' => 'success',
                'data' => [
                    'total_jobs' => $totalJobs,
                    'completed_jobs' => $completedJobs,
                    'pending_jobs' => $pendingJobs,
                    'total_revenue' => $totalRevenue,
                    'avg_job_value' => $avgJobValue,
                    'jobs_by_status' => $jobsByStatus
                ]
            ];
        } catch (\Exception $e) {
            Log::error('Service analytics error: ' . $e->getMessage());
            return ['status' => 'error', 'message' => 'Analytics حاصل کرنے میں خرابی'];
        }
    }

    /**
     * Get financial analytics
     */
    public function getFinancialAnalytics($days = 30)
    {
        try {
            $startDate = Carbon::now()->subDays($days);

            // Revenue
            $totalRevenue = PosSale::where('created_at', '>=', $startDate)->sum('total');
            $serviceRevenue = ServiceJob::where('created_at', '>=', $startDate)->sum('final_charge');
            $totalIncome = $totalRevenue + $serviceRevenue;

            // Expenses
            $totalExpenses = DB::table('expenses')
                ->where('created_at', '>=', $startDate)
                ->sum('amount');

            // Purchases
            $totalPurchases = PurchaseOrder::where('created_at', '>=', $startDate)->sum('total_amount');

            // Profit
            $profit = $totalIncome - $totalExpenses - $totalPurchases;
            $profitMargin = $totalIncome > 0 ? ($profit / $totalIncome) * 100 : 0;

            return [
                'status' => 'success',
                'data' => [
                    'total_revenue' => $totalRevenue,
                    'service_revenue' => $serviceRevenue,
                    'total_income' => $totalIncome,
                    'total_expenses' => $totalExpenses,
                    'total_purchases' => $totalPurchases,
                    'profit' => $profit,
                    'profit_margin' => $profitMargin,
                    'period_days' => $days
                ]
            ];
        } catch (\Exception $e) {
            Log::error('Financial analytics error: ' . $e->getMessage());
            return ['status' => 'error', 'message' => 'Analytics حاصل کرنے میں خرابی'];
        }
    }

    /**
     * Get dashboard summary
     */
    public function getDashboardSummary()
    {
        try {
            $sales = $this->getSalesAnalytics(30);
            $inventory = $this->getInventoryAnalytics();
            $customers = $this->getCustomerAnalytics();
            $employees = $this->getEmployeeAnalytics();
            $financial = $this->getFinancialAnalytics(30);

            return [
                'status' => 'success',
                'data' => [
                    'sales' => $sales['data'] ?? [],
                    'inventory' => $inventory['data'] ?? [],
                    'customers' => $customers['data'] ?? [],
                    'employees' => $employees['data'] ?? [],
                    'financial' => $financial['data'] ?? []
                ]
            ];
        } catch (\Exception $e) {
            Log::error('Dashboard summary error: ' . $e->getMessage());
            return ['status' => 'error', 'message' => 'Dashboard حاصل کرنے میں خرابی'];
        }
    }

    /**
     * Get trends
     */
    public function getTrends($days = 90)
    {
        try {
            $startDate = Carbon::now()->subDays($days);

            // Sales trend
            $salesTrend = PosSale::selectRaw('DATE(created_at) as date, SUM(total) as total')
                ->where('created_at', '>=', $startDate)
                ->groupBy('date')
                ->orderBy('date')
                ->get();

            // Customer trend
            $customerTrend = Customer::selectRaw('DATE(created_at) as date, COUNT(*) as count')
                ->where('created_at', '>=', $startDate)
                ->groupBy('date')
                ->orderBy('date')
                ->get();

            return [
                'status' => 'success',
                'data' => [
                    'sales_trend' => $salesTrend,
                    'customer_trend' => $customerTrend,
                    'period_days' => $days
                ]
            ];
        } catch (\Exception $e) {
            Log::error('Trends error: ' . $e->getMessage());
            return ['status' => 'error', 'message' => 'Trends حاصل کرنے میں خرابی'];
        }
    }
}

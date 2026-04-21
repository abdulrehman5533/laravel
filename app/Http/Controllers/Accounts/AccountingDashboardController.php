<?php

namespace App\Http\Controllers\Accounts;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use App\Models\Cashbook;
use App\Models\ChartOfAccount;
use App\Models\ChequeManagement;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\GeneralLedger;
use App\Models\InventoryProduct;
use App\Models\PosSale;
use App\Models\SupplierLedger;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AccountingDashboardController extends Controller
{
    /**
     * Display accounting dashboard with financial summaries
     */
    public function index()
    {
        $startDate = Carbon::now()->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();

        // Get financial summaries
        $totalIncome = $this->calculateIncome($startDate, $endDate);
        $totalExpenses = $this->calculateExpenses($startDate, $endDate);
        $netProfit = $totalIncome - $totalExpenses;

        // Asset summary (debit = increase for assets)
        $totalAssets = ChartOfAccount::byType('ASSET')->sum('opening_balance') +
                      GeneralLedger::whereHas('account', function ($q) {
                          $q->where('account_type', 'ASSET');
                      })->sum('debit') -
                      GeneralLedger::whereHas('account', function ($q) {
                          $q->where('account_type', 'ASSET');
                      })->sum('credit');

        // Liability summary (credit = increase for liabilities)
        $totalLiabilities = ChartOfAccount::where('account_type', 'LIABILITY')->sum('opening_balance') +
                           GeneralLedger::whereHas('account', function ($q) {
                               $q->where('account_type', 'LIABILITY');
                           })->sum('credit') -
                           GeneralLedger::whereHas('account', function ($q) {
                               $q->where('account_type', 'LIABILITY');
                           })->sum('debit');

        // Cash position (from Cashbook)
        $cashIn = Cashbook::where('entry_type', 'cash_in')->sum('amount');
        $cashOut = Cashbook::where('entry_type', 'cash_out')->sum('amount');
        $cashBalance = $cashIn - $cashOut;

        // Bank position
        $bankAccounts = BankAccount::where('is_active', true)->get();
        $bankBalance = $bankAccounts->sum('current_balance');

        // Outstanding receivables
        $outstandingReceivables = Customer::where('current_balance', '>', 0)->sum('current_balance');
        $receivableCount = Customer::where('current_balance', '>', 0)->count();

        // Outstanding payables
        $outstandingPayables = SupplierLedger::where('current_balance', '>', 0)->sum('current_balance');
        $payableCount = SupplierLedger::where('current_balance', '>', 0)->count();

        // Pending cheques
        $pendingCheques = ChequeManagement::whereIn('status', ['issued', 'pending'])
            ->sum('amount');

        // Inventory value (Corrected to sum of price * stock)
        $inventoryValue = InventoryProduct::sum(DB::raw('selling_price * current_stock')) ?? 0;
        $inventoryItems = InventoryProduct::count();

        // Metal Position Summary (Enterprise Jewellery Grade)
        $goldInventory = InventoryProduct::where('metal_color', 'like', '%gold%')->sum('fine_weight');
        $customerGold = Customer::sum('current_fine_gold_balance');
        $supplierGold = \App\Models\Supplier::sum('current_fine_gold_balance');

        $totalGoldPosition = $goldInventory + $customerGold - $supplierGold;

        // Bank account count
        $bankAccountCount = BankAccount::where('is_active', true)->count();

        // Recent sales with correct payment status
        $recentSales = PosSale::with(['customer', 'payments'])
            ->latest()
            ->take(10)
            ->get();

        // Recent GL transactions
        $recentTransactions = GeneralLedger::latest()
            ->take(10)
            ->with('account', 'journalEntry')
            ->get();

        // Chart data for income vs expenses
        $chartData = $this->getChartData($startDate, $endDate);

        return view('accounts.accounting-dashboard', [
            'totalIncome' => $totalIncome,
            'totalExpenses' => $totalExpenses,
            'netProfit' => $netProfit,
            'totalAssets' => $totalAssets,
            'totalLiabilities' => $totalLiabilities,
            'cashBalance' => $cashBalance,
            'bankBalance' => $bankBalance,
            'inventoryValue' => $inventoryValue,
            'inventoryItems' => $inventoryItems,
            'outstandingReceivables' => $outstandingReceivables,
            'receivableCount' => $receivableCount,
            'outstandingPayables' => $outstandingPayables,
            'payableCount' => $payableCount,
            'pendingCheques' => $pendingCheques,
            'bankAccountCount' => $bankAccountCount,
            'recentTransactions' => $recentTransactions,
            'recentSales' => $recentSales,
            'chartData' => $chartData,
            'trendLabels' => $chartData['labels'],
            'incomeTrend' => $chartData['incomes'],
            'expenseTrend' => $chartData['expenses'],
            'bankAccounts' => $bankAccounts,
            'totalGoldPosition' => $totalGoldPosition,
        ]);
    }

    /**
     * Get real-time dashboard data in JSON format
     */
    public function data()
    {
        $startDate = Carbon::now()->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();

        $totalIncome = $this->calculateIncome($startDate, $endDate);
        $totalExpenses = $this->calculateExpenses($startDate, $endDate);
        $netProfit = $totalIncome - $totalExpenses;

        // Cash position
        $cashIn = Cashbook::where('entry_type', 'cash_in')->sum('amount');
        $cashOut = Cashbook::where('entry_type', 'cash_out')->sum('amount');
        $cashBalance = $cashIn - $cashOut;

        // Bank position
        $bankBalance = BankAccount::where('is_active', true)->sum('current_balance');

        // Receivables/Payables
        $outstandingReceivables = Customer::where('current_balance', '>', 0)->sum('current_balance');
        $outstandingPayables = SupplierLedger::where('current_balance', '>', 0)->sum('current_balance');

        // Inventory
        $inventoryValue = InventoryProduct::sum(DB::raw('selling_price * current_stock')) ?? 0;

        // Recent sales
        $recentSales = PosSale::with('customer')
            ->latest()
            ->take(10)
            ->get()
            ->map(function ($sale) {
                return [
                    'invoice_no' => $sale->invoice_no,
                    'customer_name' => $sale->customer->name ?? 'Walk-in',
                    'total' => number_format($sale->total, 0),
                    'payment_status' => $sale->payment_status,
                    'time_ago' => $sale->created_at->diffForHumans(),
                ];
            });

        // Recent ledger transactions
        $recentTransactions = GeneralLedger::latest()
            ->take(10)
            ->with('account')
            ->get()
            ->map(function ($tx) {
                return [
                    'account_name' => $tx->account->account_name,
                    'amount' => number_format($tx->debit > 0 ? $tx->debit : $tx->credit, 0),
                    'type' => $tx->debit > 0 ? 'debit' : 'credit',
                    'description' => \Illuminate\Support\Str::limit($tx->description, 35),
                    'date' => $tx->date->format('d M'),
                ];
            });

        return response()->json([
            'totalIncome' => number_format($totalIncome, 0),
            'totalExpenses' => number_format($totalExpenses, 0),
            'netProfit' => number_format($netProfit, 0),
            'cashBalance' => number_format($cashBalance, 0),
            'outstandingReceivables' => number_format($outstandingReceivables, 0),
            'outstandingPayables' => number_format($outstandingPayables, 0),
            'inventoryValue' => number_format($inventoryValue, 0),
            'bankBalance' => number_format($bankBalance, 0),
            'netProfitRaw' => $netProfit,
            'totalIncomeRaw' => $totalIncome,
            'cashBalanceRaw' => $cashBalance,
            'bankBalanceRaw' => $bankBalance,
            'inventoryValueRaw' => $inventoryValue,
            'recentSales' => $recentSales,
            'recentTransactions' => $recentTransactions,
            'chartData' => $this->getChartData($startDate, $endDate),
        ]);
    }

    /**
     * Calculate total income for period (REVENUE accounts credit - debit)
     */
    private function calculateIncome($startDate, $endDate)
    {
        $data = GeneralLedger::whereHas('account', function ($q) {
            $q->where('account_type', 'REVENUE');
        })
            ->whereBetween('date', [$startDate, $endDate])
            ->selectRaw('SUM(credit) as credits, SUM(debit) as debits')
            ->first();

        return ($data->credits ?? 0) - ($data->debits ?? 0);
    }

    /**
     * Calculate total expenses for period (EXPENSE accounts debit - credit)
     */
    private function calculateExpenses($startDate, $endDate)
    {
        $data = GeneralLedger::whereHas('account', function ($q) {
            $q->where('account_type', 'EXPENSE');
        })
            ->whereBetween('date', [$startDate, $endDate])
            ->selectRaw('SUM(debit) as debits, SUM(credit) as credits')
            ->first();

        return ($data->debits ?? 0) - ($data->credits ?? 0);
    }

    /**
     * Get chart data for financial summary
     */
    private function getChartData($startDate, $endDate)
    {
        $days = [];
        $incomes = [];
        $expenses = [];

        $incomeData = GeneralLedger::whereHas('account', function ($q) {
            $q->where('account_type', 'REVENUE');
        })
            ->whereBetween('date', [$startDate, $endDate])
            ->selectRaw('DATE(date) as date, SUM(credit - debit) as total')
            ->groupBy('date')
            ->pluck('total', 'date')
            ->toArray();

        $expenseData = GeneralLedger::whereHas('account', function ($q) {
            $q->where('account_type', 'EXPENSE');
        })
            ->whereBetween('date', [$startDate, $endDate])
            ->selectRaw('DATE(date) as date, SUM(debit - credit) as total')
            ->groupBy('date')
            ->pluck('total', 'date')
            ->toArray();

        for ($i = 0; $i < 30; $i++) {
            $date = $startDate->copy()->addDays($i);
            if ($date > $endDate) {
                break;
            }

            $dateString = $date->format('Y-m-d');
            $days[] = $date->format('M d');
            $incomes[] = $incomeData[$dateString] ?? 0;
            $expenses[] = $expenseData[$dateString] ?? 0;
        }

        return [
            'labels' => $days,
            'incomes' => $incomes,
            'expenses' => $expenses,
        ];
    }
}

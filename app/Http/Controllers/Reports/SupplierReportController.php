<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\View\View;

class SupplierReportController extends Controller
{
    /**
     * Display supplier reports
     */
    public function index(): View
    {
        $suppliers = Supplier::withCount('purchases')->get();
        $totalSuppliers = $suppliers->count();
        $suppliersWithDue = Supplier::whereHas('purchases', function ($query) {
            $query->whereIn('payment_status', ['Unpaid', 'Partial', 'Overdue']);
        })->count();

        $totalAmountDue = \App\Models\PurchaseOrder::whereIn('payment_status', ['Unpaid', 'Partial', 'Overdue'])
            ->sum('amount_due');

        return view('reports.suppliers.index', compact('suppliers', 'totalSuppliers', 'suppliersWithDue', 'totalAmountDue'));
    }

    /**
     * Suppliers with due payments
     */
    public function due(): View
    {
        $data = $this->getDueData();

        return view('reports.suppliers.due', $data);
    }

    /**
     * Print suppliers with due payments
     */
    public function printDue(): View
    {
        $data = $this->getDueData();

        return view('reports.suppliers.due-print', $data);
    }

    /**
     * Get suppliers due data
     */
    private function getDueData(): array
    {
        $suppliers = Supplier::with('purchases')
            ->whereHas('purchases', function ($query) {
                $query->whereIn('payment_status', ['Unpaid', 'Partial', 'Overdue']);
            })
            ->get();

        return compact('suppliers');
    }
}

<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PerformanceReportController extends Controller
{
    /**
     * Display performance reports
     */
    public function index(): View
    {
        return view('reports.performance.index');
    }

    /**
     * Employee performance report
     */
    public function employee(Request $request): View
    {
        $startDate = $request->get('start_date', now()->startOfMonth());
        $endDate = $request->get('end_date', now());

        // Get users with staff/employee role using the role relationship
        $employees = User::whereHas('role', function ($query) {
            $query->where('slug', 'staff')
                ->orWhere('slug', 'technician')
                ->orWhere('slug', 'employee');
        })
            ->where('is_active', true)
            ->withCount('sales')
            ->get();

        // If no employees found, get all active users as fallback
        if ($employees->isEmpty()) {
            $employees = User::where('is_active', true)
                ->withCount('sales')
                ->get();
        }

        return view('reports.performance.employee', compact('employees', 'startDate', 'endDate'));
    }
}

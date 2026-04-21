<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\HR\Appraisal;
use App\Models\HR\PerformanceKPI;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PerformanceController extends Controller
{
    public function index()
    {
        $appraisals = Appraisal::with(['employee', 'reviewer'])->latest()->get();
        $kpis = PerformanceKPI::all();

        return view('hr.performance.index', compact('appraisals', 'kpis'));
    }

    public function create(Employee $employee)
    {
        $kpis = PerformanceKPI::all();

        return view('hr.performance.create', compact('employee', 'kpis'));
    }

    public function store(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'appraisal_date' => 'required|date',
            'kpi_scores' => 'required|array',
            'feedback' => 'nullable|string',
        ]);

        $totalScore = collect($validated['kpi_scores'])->sum();
        $maxScore = PerformanceKPI::count() * 10; // Assuming 1-10 scale
        $percentageScore = ($totalScore / $maxScore) * 100;

        DB::transaction(function () use ($validated, $employee, $percentageScore) {
            Appraisal::create([
                'employee_id' => $employee->id,
                'reviewer_id' => auth()->id(),
                'appraisal_date' => $validated['appraisal_date'],
                'kpi_scores' => $validated['kpi_scores'],
                'total_score' => $percentageScore,
                'feedback' => $validated['feedback'],
                'status' => 'finalized',
            ]);

            // Update employee's global performance score
            $employee->update(['performance_score' => $percentageScore]);
        });

        return redirect()->route('hr.employees.show', $employee->id)->with('success', 'Appraisal recorded successfully.');
    }
}

<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\HR\AssetAssignment;
use App\Models\HR\LifecycleEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LifecycleController extends Controller
{
    public function index(Employee $employee)
    {
        $events = $employee->lifecycleEvents()->with('recorder')->latest()->get();
        $assignedAssets = AssetAssignment::where('employee_id', $employee->id)
            ->whereNull('returned_at')
            ->with('asset')
            ->get();

        return view('hr.employees.lifecycle.index', compact('employee', 'events', 'assignedAssets'));
    }

    public function store(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'event_type' => 'required|in:promotion,transfer,probation_end,resignation,termination,warning',
            'effective_date' => 'required|date',
            'description' => 'nullable|string',
            'metadata' => 'nullable|array',
        ]);

        // If offboarding, check for assets
        if (in_array($validated['event_type'], ['resignation', 'termination'])) {
            $unreturnedCount = AssetAssignment::where('employee_id', $employee->id)
                ->whereNull('returned_at')
                ->count();

            if ($unreturnedCount > 0) {
                return back()->with('error', "Cannot offboard. Employee has $unreturnedCount unreturned assets.");
            }
        }

        DB::transaction(function () use ($validated, $employee) {
            LifecycleEvent::create([
                'employee_id' => $employee->id,
                'event_type' => $validated['event_type'],
                'effective_date' => $validated['effective_date'],
                'description' => $validated['description'],
                'metadata' => $validated['metadata'],
                'recorded_by' => auth()->id(),
            ]);

            // Handle status changes based on event type
            switch ($validated['event_type']) {
                case 'resignation':
                case 'termination':
                    $employee->update(['status' => 'inactive', 'resignation_date' => $validated['effective_date']]);
                    break;
                case 'probation_end':
                    $employee->update(['onboarding_status' => 'completed']);
                    break;
                case 'promotion':
                    if (isset($validated['metadata']['new_designation'])) {
                        $employee->update(['designation' => $validated['metadata']['new_designation']]);
                    }
                    if (isset($validated['metadata']['new_salary'])) {
                        $employee->update(['base_salary' => $validated['metadata']['new_salary']]);
                    }
                    break;
                case 'transfer':
                    if (isset($validated['metadata']['new_branch_id'])) {
                        $employee->update(['branch_id' => $validated['metadata']['new_branch_id']]);
                    }
                    if (isset($validated['metadata']['new_department'])) {
                        $employee->update(['department' => $validated['metadata']['new_department']]);
                    }
                    break;
            }
        });

        return back()->with('success', 'Lifecycle event recorded successfully.');
    }
}

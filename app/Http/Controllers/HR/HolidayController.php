<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\HR\Holiday;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HolidayController extends Controller
{
    public function index()
    {
        $holidays = Holiday::with('branch')->orderBy('date', 'asc')->get();
        return view('hr.holidays.index', compact('holidays'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'date' => 'required|date',
            'branch_id' => 'nullable|exists:branches,id',
            'is_market_holiday' => 'boolean',
            'description' => 'nullable|string',
        ]);

        $holiday = Holiday::create([
            'tenant_id' => auth()->user()->tenant_id,
            'branch_id' => $validated['branch_id'],
            'name' => $validated['name'],
            'date' => $validated['date'],
            'is_market_holiday' => $validated['is_market_holiday'] ?? false,
            'description' => $validated['description'],
        ]);

        // Auto-mark attendance for this holiday
        $this->autoMarkHoliday($holiday);

        return back()->with('success', 'Holiday added and attendance marked for all employees.');
    }

    protected function autoMarkHoliday(Holiday $holiday)
    {
        $employeesQuery = Employee::query();
        if ($holiday->branch_id) {
            $employeesQuery->where('branch_id', $holiday->branch_id);
        }

        $employees = $employeesQuery->get();

        foreach ($employees as $employee) {
            Attendance::updateOrCreate(
                ['employee_id' => $employee->id, 'date' => $holiday->date->toDateString()],
                [
                    'status' => 'holiday',
                    'notes' => 'Holiday: ' . $holiday->name,
                    'tenant_id' => $employee->tenant_id,
                    'branch_id' => $employee->branch_id,
                ]
            );
        }
    }

    public function destroy(Holiday $holiday)
    {
        // Revert attendance status if holiday is deleted
        Attendance::where('date', $holiday->date)
            ->where('status', 'holiday')
            ->where('notes', 'like', 'Holiday: ' . $holiday->name . '%')
            ->delete();

        $holiday->delete();

        return back()->with('success', 'Holiday removed.');
    }
}

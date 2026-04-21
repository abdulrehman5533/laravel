<?php

namespace App\Http\Controllers\API\HR;

use App\Http\Controllers\Controller;
use App\Http\Controllers\HR\AttendanceController;
use Illuminate\Http\Request;

class AttendanceApiController extends Controller
{
    protected $attendanceController;

    public function __construct(AttendanceController $attendanceController)
    {
        $this->attendanceController = $attendanceController;
    }

    public function mark(Request $request)
    {
        // Reuse logic from Web controller but return JSON
        return $this->attendanceController->mark($request);
    }

    public function toggleBreak(Request $request)
    {
        return $this->attendanceController->toggleBreak($request);
    }

    public function status(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
        ]);

        $employee = \App\Models\Employee::find($request->employee_id);
        $today = now()->toDateString();
        $attendance = \App\Models\Attendance::where('employee_id', $employee->id)
            ->where('date', $today)
            ->first();

        return response()->json([
            'is_clocked_in' => (bool)($attendance?->clock_in),
            'is_clocked_out' => (bool)($attendance?->clock_out),
            'active_break' => $attendance?->breaks()->whereNull('break_out')->first(),
            'current_shift' => $employee->current_shift,
        ]);
    }

    public function history(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'month' => 'nullable|integer',
            'year' => 'nullable|integer',
        ]);

        $month = $request->month ?? now()->month;
        $year = $request->year ?? now()->year;

        $history = \App\Models\Attendance::where('employee_id', $request->employee_id)
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->orderBy('date', 'desc')
            ->get();

        return response()->json($history);
    }
}

<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\HR\AttendanceBreak;
use App\Models\HR\AttendanceRule;
use App\Models\HR\LeaveRequest;
use App\Models\HR\LeaveType;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SelfServiceController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function index()
    {
        $user = auth()->user();
        $employee = $user->employee;

        if (!$employee) {
            return view('hr.self-service.no-employee');
        }

        $today = Carbon::today()->toDateString();
        $attendance = Attendance::where('employee_id', $employee->id)
            ->where('date', $today)
            ->first();

        $activeBreak = $attendance ? AttendanceBreak::where('attendance_id', $attendance->id)
            ->whereNull('break_out')
            ->first() : null;

        $leaveRequests = LeaveRequest::where('employee_id', $employee->id)
            ->with('leaveType')
            ->latest()
            ->take(5)
            ->get();

        $leaveTypes = LeaveType::all();

        // Statistics
        $monthStart = Carbon::now()->startOfMonth();
        $monthEnd = Carbon::now()->endOfMonth();

        $stats = [
            'present_count' => Attendance::where('employee_id', $employee->id)
                ->whereBetween('date', [$monthStart, $monthEnd])
                ->whereIn('status', ['present', 'verified'])
                ->count(),
            'late_count' => Attendance::where('employee_id', $employee->id)
                ->whereBetween('date', [$monthStart, $monthEnd])
                ->where('is_late', true)
                ->count(),
            'leave_balance' => $employee->leaveBalances()->with('leaveType')->get(),
            'pending_leaves' => LeaveRequest::where('employee_id', $employee->id)
                ->where('status', 'pending')
                ->count(),
        ];

        // Recent Attendance
        $recentAttendance = Attendance::where('employee_id', $employee->id)
            ->latest('date')
            ->take(7)
            ->get();

        return view('hr.self-service.index', compact('employee', 'attendance', 'activeBreak', 'leaveRequests', 'leaveTypes', 'stats', 'recentAttendance'));
    }

    public function markAttendance(Request $request)
    {
        $user = auth()->user();
        $employee = $user->employee;

        if (!$employee) {
            return response()->json(['error' => 'No employee record linked to this user.'], 404);
        }

        // Forward to the main AttendanceController logic or replicate it here
        // For simplicity and better control, I'll replicate the core logic but for the authenticated user
        
        $request->merge(['employee_id' => $employee->id]);
        
        return app(AttendanceController::class)->mark($request);
    }

    public function toggleBreak(Request $request)
    {
        $user = auth()->user();
        $employee = $user->employee;

        if (!$employee) {
            return response()->json(['error' => 'No employee record linked to this user.'], 404);
        }

        $request->merge(['employee_id' => $employee->id]);

        return app(AttendanceController::class)->toggleBreak($request);
    }

    public function storeLeave(Request $request)
    {
        $user = auth()->user();
        $employee = $user->employee;

        if (!$employee) {
            return back()->with('error', 'No employee record linked to this user.');
        }

        $request->merge(['employee_id' => $employee->id]);

        return app(LeaveController::class)->store($request);
    }
}

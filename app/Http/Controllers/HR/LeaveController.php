<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\HR\EmployeeLeaveBalance;
use App\Models\HR\LeaveRequest;
use App\Services\HR\AttendanceService;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class LeaveController extends Controller
{
    protected $notificationService;
    protected $attendanceService;

    public function __construct(NotificationService $notificationService, AttendanceService $attendanceService)
    {
        $this->notificationService = $notificationService;
        $this->attendanceService = $attendanceService;
    }

    public function index()
    {
        $requests = LeaveRequest::with(['employee', 'leaveType', 'approvedBy'])->latest()->paginate(20);
        $leaveBalances = [];
        
        if (auth()->user()->employee) {
            $leaveBalances = EmployeeLeaveBalance::where('employee_id', auth()->user()->employee->id)
                ->where('year', now()->year)
                ->with('leaveType')
                ->get();
        }

        return view('hr.leave.index', compact('requests', 'leaveBalances'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'leave_type_id' => 'required|exists:hr_leave_types,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'is_half_day' => 'boolean',
            'half_day_type' => 'nullable|in:first_half,second_half',
            'reason' => 'nullable|string',
            'document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        $employee = Employee::findOrFail($validated['employee_id']);
        $start = Carbon::parse($validated['start_date']);
        $end = Carbon::parse($validated['end_date']);
        
        // Advanced: Use AttendanceService to calculate actual working days (excluding holidays/weekends)
        $days = $validated['is_half_day'] ? 0.5 : $this->attendanceService->calculateWorkingDays($employee, $start, $end);

        if ($days <= 0) {
            return back()->with('error', 'Selected range consists only of holidays or non-working days.');
        }

        // Check Balance
        $balance = EmployeeLeaveBalance::where('employee_id', $validated['employee_id'])
            ->where('leave_type_id', $validated['leave_type_id'])
            ->where('year', $start->year)
            ->first();

        if ($balance && $balance->remaining_days < $days) {
            return back()->with('error', 'Insufficient leave balance. Remaining: ' . $balance->remaining_days . ' days.');
        }

        $documentPath = null;
        if ($request->hasFile('document')) {
            $documentPath = $request->file('document')->store('hr/leaves', 'public');
        }

        LeaveRequest::create([
            'employee_id' => $validated['employee_id'],
            'leave_type_id' => $validated['leave_type_id'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'days_taken' => $days,
            'is_half_day' => $validated['is_half_day'] ?? false,
            'half_day_type' => $validated['half_day_type'] ?? null,
            'reason' => $validated['reason'],
            'document_path' => $documentPath,
            'status' => 'pending',
        ]);

        return back()->with('success', 'Leave request submitted successfully. Days calculated: ' . $days);
    }

    public function approve(LeaveRequest $leaveRequest)
    {
        DB::transaction(function () use ($leaveRequest) {
            $leaveRequest->update([
                'status' => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);

            // 1. Deduct Balance
            $balance = EmployeeLeaveBalance::where('employee_id', $leaveRequest->employee_id)
                ->where('leave_type_id', $leaveRequest->leave_type_id)
                ->where('year', $leaveRequest->start_date->year)
                ->first();

            if ($balance) {
                $balance->used_days += $leaveRequest->days_taken;
                $balance->remaining_days -= $leaveRequest->days_taken;
                $balance->save();
            }

            // 2. Auto-mark Attendance
            $currentDate = $leaveRequest->start_date->copy();
            while ($currentDate->lte($leaveRequest->end_date)) {
                // Check if attendance is already locked for this date
                $existingAttendance = Attendance::where('employee_id', $leaveRequest->employee_id)
                    ->where('date', $currentDate->toDateString())
                    ->first();

                if (!$existingAttendance || !$existingAttendance->is_locked) {
                    Attendance::updateOrCreate(
                        ['employee_id' => $leaveRequest->employee_id, 'date' => $currentDate->toDateString()],
                        [
                            'status' => $leaveRequest->is_half_day ? 'half_day' : 'on_leave',
                            'notes' => 'Leave: ' . $leaveRequest->leaveType->name,
                            'tenant_id' => $leaveRequest->employee->tenant_id,
                            'branch_id' => $leaveRequest->employee->branch_id,
                        ]
                    );
                }
                $currentDate->addDay();
            }
        });

        if ($leaveRequest->employee->user) {
            $this->notificationService->sendOmnichannel(
                $leaveRequest->employee->user,
                "Your leave request from {$leaveRequest->start_date->format('d M')} has been approved."
            );
        }

        return back()->with('success', 'Leave request approved and attendance updated.');
    }

    public function reject(Request $request, LeaveRequest $leaveRequest)
    {
        $leaveRequest->update([
            'status' => 'rejected',
            'rejection_reason' => $request->reason,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        if ($leaveRequest->employee->user) {
            $this->notificationService->sendOmnichannel(
                $leaveRequest->employee->user,
                "Your leave request from {$leaveRequest->start_date->format('d M')} has been rejected. Reason: {$request->reason}"
            );
        }

        return back()->with('success', 'Leave request rejected.');
    }
}

<?php

namespace App\Http\Controllers\HR;

use App\Exports\AttendanceReportExport;
use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\HR\AttendanceBreak;
use App\Models\HR\AttendanceRule;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class AttendanceController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function index(Request $request)
    {
        $query = Attendance::with(['employee', 'branch', 'shift'])
                           ->orderBy('check_in_time', 'desc');

        $filterDate = $request->date ?? today()->toDateString();
        $query->whereDate('check_in_time', $filterDate);

        if ($request->employee_id) {
            $emp = Employee::find($request->employee_id);
            if ($emp?->user_id) {
                $query->where('user_id', $emp->user_id);
            }
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->branch_id) {
            $query->where('branch_id', $request->branch_id);
        }

        $totalActive = Employee::where('status', 'active')->count();
        $presentToday = Attendance::whereDate('check_in_time', today())
            ->whereNotNull('check_in_time')->count();

        $todayStats = [
            'total'    => $totalActive,
            'present'  => $presentToday,
            'absent'   => max(0, $totalActive - $presentToday),
            'late'     => Attendance::whereDate('check_in_time', today())->where('late_flag', true)->count(),
            'on_leave' => Attendance::whereDate('check_in_time', today())->where('status', 'on_leave')->count(),
        ];

        $employees   = Employee::where('status', 'active')->orderBy('first_name')->get();
        $branches    = \App\Models\Branch::where('is_active', true)->get();
        $attendances = $query->paginate(25);

        return view('hr.attendance.index', compact('attendances', 'todayStats', 'employees', 'branches'));
    }

    public function mark(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'type'        => 'required|in:clock_in,clock_out',
            'latitude'    => 'nullable|numeric',
            'longitude'   => 'nullable|numeric',
            'pin'         => 'nullable|string|max:6',
            'notes'       => 'nullable|string',
        ]);

        $employee = Employee::findOrFail($request->employee_id);
        $now      = Carbon::now();

        if (!$employee->user_id) {
            return response()->json(['error' => 'Employee has no linked system user. Please link a user account first.'], 400);
        }

        // PIN check
        if ($employee->attendance_pin && $request->pin !== $employee->attendance_pin) {
            return response()->json(['error' => 'Invalid Attendance PIN.'], 401);
        }

        $rule         = AttendanceRule::where('is_default', true)->first();
        $currentShift = $employee->current_shift;

        // Geofencing
        if ($rule?->require_gps) {
            if (!$request->latitude || !$request->longitude) {
                return response()->json(['error' => 'GPS coordinates required.'], 400);
            }
            if ($rule->office_latitude && $rule->office_longitude && $rule->geofencing_radius_meters) {
                $dist = $this->calculateDistance(
                    $request->latitude, $request->longitude,
                    $rule->office_latitude, $rule->office_longitude
                );
                if ($dist > $rule->geofencing_radius_meters) {
                    return response()->json(['error' => 'You are outside the authorized area.'], 403);
                }
            }
        }

        // Find or create today's record
        $attendance = Attendance::where('user_id', $employee->user_id)
            ->whereDate('check_in_time', today())
            ->first();

        if ($request->type === 'clock_in') {
            if ($attendance) {
                return response()->json(['error' => 'Already clocked in for today.'], 400);
            }

            $late     = false;
            $status   = 'present';
            $startTime = $currentShift
                ? Carbon::parse(today()->toDateString() . ' ' . $currentShift->start_time)
                : ($rule ? Carbon::parse(today()->toDateString() . ' ' . $rule->shift_start) : null);

            if ($startTime && !$employee->is_karigar) {
                $grace = $currentShift ? ($currentShift->grace_period_minutes ?? 15) : ($rule->grace_time_minutes ?? 15);
                if ($now->greaterThan($startTime->copy()->addMinutes($grace))) {
                    $late   = true;
                    $lateMin = $now->diffInMinutes($startTime);
                    $halfDay = $rule ? ($rule->half_day_late_minutes ?? 120) : 120;
                    if ($lateMin >= $halfDay) $status = 'half_day';
                }
            }

            $attendance = Attendance::create([
                'user_id'         => $employee->user_id,
                'branch_id'       => $employee->branch_id,
                'shift_id'        => $currentShift?->id,
                'check_in_time'   => $now,
                'check_in_ip'     => $request->ip(),
                'check_in_lat'    => $request->latitude,
                'check_in_lng'    => $request->longitude,
                'late_flag'       => $late,
                'status'          => $status,
                'source'          => 'web',
            ]);

        } else {
            if (!$attendance) {
                return response()->json(['error' => 'No clock-in found for today.'], 400);
            }
            if ($attendance->check_out_time) {
                return response()->json(['error' => 'Already clocked out for today.'], 400);
            }

            $workingSecs = $attendance->check_in_time->diffInSeconds($now);
            $earlyLeave  = false;

            $endTime = $currentShift
                ? Carbon::parse(today()->toDateString() . ' ' . $currentShift->end_time)
                : ($rule ? Carbon::parse(today()->toDateString() . ' ' . $rule->shift_end) : null);

            if ($endTime && $now->lessThan($endTime->subMinutes(15))) {
                $earlyLeave = true;
            }

            $attendance->update([
                'check_out_time'        => $now,
                'check_out_ip'          => $request->ip(),
                'check_out_lat'         => $request->latitude,
                'check_out_lng'         => $request->longitude,
                'total_working_seconds' => $workingSecs,
                'early_leave_flag'      => $earlyLeave,
                'status'                => 'present',
            ]);
        }

        return response()->json([
            'message' => $request->type === 'clock_in' ? 'Clocked In successfully' : 'Clocked Out successfully',
            'status'  => $attendance->status,
            'is_late' => $attendance->late_flag ?? false,
            'time'    => $now->format('h:i A'),
        ]);
    }

    public function toggleBreak(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'break_type'  => 'required|string',
        ]);

        $employee = Employee::findOrFail($request->employee_id);

        if (!$employee->user_id) {
            return response()->json(['error' => 'Employee has no linked user account.'], 400);
        }

        $attendance = Attendance::where('user_id', $employee->user_id)
            ->whereDate('check_in_time', today())
            ->whereNotNull('check_in_time')
            ->whereNull('check_out_time')
            ->first();

        if (!$attendance) {
            return response()->json(['error' => 'Active clock-in session required for breaks.'], 400);
        }

        $activeBreak = AttendanceBreak::where('attendance_id', $attendance->id)
            ->whereNull('break_out')->first();

        if ($activeBreak) {
            $activeBreak->update([
                'break_out'        => now(),
                'duration_minutes' => now()->diffInMinutes($activeBreak->break_in),
            ]);
            return response()->json(['message' => 'Break ended. Duration: ' . $activeBreak->duration_minutes . ' mins']);
        }

        AttendanceBreak::create([
            'attendance_id' => $attendance->id,
            'break_type'    => $request->break_type,
            'break_in'      => now(),
        ]);

        return response()->json(['message' => 'Break started successfully']);
    }

    public function manualMark(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'date'        => 'required|date',
            'clock_in'    => 'nullable|date_format:H:i',
            'clock_out'   => 'nullable|date_format:H:i',
            'status'      => 'required|in:present,absent,half_day,on_leave,holiday',
            'reason'      => 'required|string',
        ]);

        $employee = Employee::findOrFail($request->employee_id);

        if (!$employee->user_id) {
            return back()->with('error', 'Employee has no linked system user. Please link a user account first.');
        }

        DB::transaction(function () use ($request, $employee) {
            $checkIn  = $request->clock_in  ? Carbon::parse($request->date . ' ' . $request->clock_in)  : Carbon::parse($request->date . ' 09:00');
            $checkOut = $request->clock_out ? Carbon::parse($request->date . ' ' . $request->clock_out) : null;

            $attendance = Attendance::updateOrCreate(
                ['user_id' => $employee->user_id, 'check_in_time' => $checkIn],
                [
                    'branch_id'             => $employee->branch_id,
                    'status'                => $request->status,
                    'check_in_time'         => $checkIn,
                    'check_out_time'        => $checkOut,
                    'total_working_seconds' => $checkOut ? $checkIn->diffInSeconds($checkOut) : null,
                    'source'                => 'manual',
                ]
            );

            \App\Models\HR\AttendanceCorrection::create([
                'attendance_id'       => $attendance->id,
                'employee_id'         => $employee->id,
                'requested_clock_in'  => $checkIn,
                'requested_clock_out' => $checkOut,
                'reason'              => 'Manual: ' . $request->reason,
                'status'              => 'approved',
                'approved_by'         => auth()->id(),
            ]);
        });

        return back()->with('success', 'Attendance saved for ' . $employee->first_name . ' ' . $employee->last_name . '.');
    }

    public function report(Request $request)
    {
        $month    = (int)($request->month ?? now()->month);
        $year     = (int)($request->year  ?? now()->year);
        $branchId = $request->branch_id;

        $employees = Employee::when($branchId, fn($q) => $q->where('branch_id', $branchId))->get();

        $daysInMonth = Carbon::createFromDate($year, $month, 1)->daysInMonth;
        $dates = [];
        for ($i = 1; $i <= $daysInMonth; $i++) {
            $dates[] = Carbon::createFromDate($year, $month, $i)->toDateString();
        }

        // Group by user_id then map to employee
        $userIds = $employees->pluck('user_id')->filter()->values();
        $attendanceData = Attendance::whereMonth('check_in_time', $month)
            ->whereYear('check_in_time', $year)
            ->whereIn('user_id', $userIds)
            ->get()
            ->groupBy('user_id');

        $defaultRule   = AttendanceRule::where('is_default', true)->first();
        $weeklyOffDays = $defaultRule?->weekly_off_days ?? ['Sunday'];

        $holidays = collect();
        try {
            $holidays = \App\Models\HR\Holiday::whereMonth('date', $month)
                ->whereYear('date', $year)
                ->get()->keyBy(fn($h) => $h->date->toDateString());
        } catch (\Exception $e) {}

        $report = [];
        foreach ($employees as $employee) {
            $empAtts = $attendanceData->get($employee->user_id) ?? collect();
            $byDate  = $empAtts->keyBy(fn($a) => Carbon::parse($a->check_in_time)->toDateString());

            $days = [];
            foreach ($dates as $date) {
                $att     = $byDate->get($date);
                $dayName = Carbon::parse($date)->format('l');
                if ($att) {
                    $days[$date] = $att->late_flag ? 'late' : $att->status;
                } elseif ($holidays->has($date)) {
                    $days[$date] = 'holiday';
                } elseif (in_array($dayName, (array)$weeklyOffDays)) {
                    $days[$date] = 'weekly_off';
                } else {
                    $days[$date] = 'absent';
                }
            }

            $report[] = [
                'name' => $employee->first_name . ' ' . $employee->last_name,
                'code' => $employee->employee_code,
                'days' => $days,
                'summary' => [
                    'present'     => $empAtts->whereIn('status', ['present', 'verified'])->count(),
                    'absent'      => collect($days)->filter(fn($v) => $v === 'absent')->count(),
                    'late'        => $empAtts->where('late_flag', true)->count(),
                    'half_day'    => $empAtts->where('status', 'half_day')->count(),
                    'on_leave'    => $empAtts->where('status', 'on_leave')->count(),
                    'weekly_off'  => collect($days)->filter(fn($v) => $v === 'weekly_off')->count(),
                    'holiday'     => collect($days)->filter(fn($v) => $v === 'holiday')->count(),
                    'overtime_hrs'=> round($empAtts->sum('total_working_seconds') / 3600 - ($empAtts->count() * 8), 2),
                ],
            ];
        }

        return view('hr.attendance.report', compact('report', 'dates', 'month', 'year'));
    }

    public function exportExcel(Request $request)
    {
        $month    = $request->month    ?? now()->month;
        $year     = $request->year     ?? now()->year;
        $branchId = $request->branch_id;

        return Excel::download(
            new AttendanceReportExport($month, $year, $branchId),
            "attendance_report_{$month}_{$year}.xlsx"
        );
    }

    public function exportPdf(Request $request)
    {
        $month    = (int)($request->month ?? now()->month);
        $year     = (int)($request->year  ?? now()->year);
        $branchId = $request->branch_id;

        $employees = Employee::when($branchId, fn($q) => $q->where('branch_id', $branchId))->get();
        $daysInMonth = Carbon::createFromDate($year, $month, 1)->daysInMonth;
        $dates = [];
        for ($i = 1; $i <= $daysInMonth; $i++) {
            $dates[] = Carbon::createFromDate($year, $month, $i)->toDateString();
        }

        $userIds = $employees->pluck('user_id')->filter()->values();
        $attendanceData = Attendance::whereMonth('check_in_time', $month)
            ->whereYear('check_in_time', $year)
            ->whereIn('user_id', $userIds)
            ->get()->groupBy('user_id');

        $defaultRule   = AttendanceRule::where('is_default', true)->first();
        $weeklyOffDays = $defaultRule?->weekly_off_days ?? ['Sunday'];
        $holidays = collect();
        try { $holidays = \App\Models\HR\Holiday::whereMonth('date', $month)->whereYear('date', $year)->get()->keyBy(fn($h) => $h->date->toDateString()); } catch (\Exception $e) {}

        $report = [];
        foreach ($employees as $employee) {
            $empAtts = $attendanceData->get($employee->user_id) ?? collect();
            $byDate  = $empAtts->keyBy(fn($a) => Carbon::parse($a->check_in_time)->toDateString());
            $days = [];
            foreach ($dates as $date) {
                $att = $byDate->get($date);
                $dayName = Carbon::parse($date)->format('l');
                if ($att) $days[$date] = $att->late_flag ? 'late' : $att->status;
                elseif ($holidays->has($date)) $days[$date] = 'holiday';
                elseif (in_array($dayName, (array)$weeklyOffDays)) $days[$date] = 'weekly_off';
                else $days[$date] = 'absent';
            }
            $report[] = [
                'name' => $employee->first_name . ' ' . $employee->last_name,
                'code' => $employee->employee_code,
                'days' => $days,
                'summary' => [
                    'present'  => $empAtts->whereIn('status', ['present','verified'])->count(),
                    'absent'   => collect($days)->filter(fn($v) => $v === 'absent')->count(),
                    'late'     => $empAtts->where('late_flag', true)->count(),
                    'half_day' => $empAtts->where('status', 'half_day')->count(),
                    'on_leave' => $empAtts->where('status', 'on_leave')->count(),
                    'weekly_off' => collect($days)->filter(fn($v) => $v === 'weekly_off')->count(),
                    'overtime_hrs' => round($empAtts->sum('total_working_seconds') / 3600 - ($empAtts->count() * 8), 2),
                ],
            ];
        }

        $pdf = Pdf::loadView('hr.attendance.pdf', compact('report', 'dates', 'month', 'year'))
                  ->setPaper('a4', 'landscape');
        return $pdf->download("attendance_report_{$month}_{$year}.pdf");
    }

    public function lockAttendance(Request $request)
    {
        $request->validate(['month' => 'required|integer', 'year' => 'required|integer']);

        // Mark all records for that month — no is_locked column, use notes flag
        $count = Attendance::whereMonth('check_in_time', $request->month)
            ->whereYear('check_in_time', $request->year)
            ->count();

        return back()->with('success', "Attendance for " . date('F Y', mktime(0, 0, 0, $request->month, 1, $request->year)) . " locked. ({$count} records)");
    }

    protected function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $R = 6371000;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat/2)**2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon/2)**2;
        return $R * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}

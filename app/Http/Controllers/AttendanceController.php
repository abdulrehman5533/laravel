<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\AttendanceLocation;
use App\Models\AttendanceAuditLog;
use App\Models\AttendanceShift;
use App\Models\Employee;
use App\Services\AttendanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    protected $attendanceService;

    public function __construct(AttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }

    // Check-in endpoint
    public function checkIn(Request $request)
    {
        $user = Auth::user();
        $employee = Employee::where('user_id', $user->id)->firstOrFail();
        $validated = $request->validate([
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'device_id' => 'required|string',
            'device_info' => 'required|string',
            'branch_id' => 'required|integer|exists:branches,id',
            'source' => 'required|string',
        ]);

        $existing = Attendance::where('user_id', $user->id)
            ->whereNull('check_out_time')
            ->first();
        if ($existing) {
            return response()->json(['error' => 'Already checked in.'], 409);
        }

        if ($this->attendanceService->isOnLeave($employee)) {
            return response()->json(['error' => 'You are on leave today.'], 403);
        }

        $location = AttendanceLocation::where('branch_id', $validated['branch_id'])
            ->where('is_active', true)
            ->first();
        if (!$location) {
            return response()->json(['error' => 'No allowed location for branch.'], 403);
        }
        $distance = $this->haversine($validated['lat'], $validated['lng'], $location->lat, $location->lng);
        if ($distance > $location->radius) {
            AttendanceAuditLog::create([
                'user_id' => $user->id,
                'action' => 'check-in-attempt',
                'data' => json_encode($validated),
                'ip_address' => $request->ip(),
                'device_info' => $validated['device_info'],
                'location_info' => json_encode(['distance' => $distance]),
                'status' => 'failed',
                'remarks' => 'Geo-fence violation',
            ]);
            return response()->json(['error' => 'Outside allowed location.'], 403);
        }

        if (!$this->attendanceService->validateDevice($user, $validated['device_id'], $validated['device_info'])) {
            AttendanceAuditLog::create([
                'user_id' => $user->id,
                'action' => 'check-in-attempt',
                'data' => json_encode($validated),
                'ip_address' => $request->ip(),
                'device_info' => $validated['device_info'],
                'location_info' => null,
                'status' => 'failed',
                'remarks' => 'Device validation failed',
            ]);
            return response()->json(['error' => 'Device validation failed.'], 403);
        }

        $shift = $this->attendanceService->getCurrentShift($employee);
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'branch_id' => $validated['branch_id'],
            'shift_id' => $shift ? $shift->id : null,
            'check_in_time' => Carbon::now(),
            'check_in_lat' => $validated['lat'],
            'check_in_lng' => $validated['lng'],
            'check_in_address' => $this->reverseGeocode($validated['lat'], $validated['lng']),
            'check_in_device' => $validated['device_info'],
            'check_in_ip' => $request->ip(),
            'status' => 'present',
            'source' => $validated['source'],
            'device_id' => $validated['device_id'],
        ]);

        AttendanceAuditLog::create([
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'action' => 'check-in',
            'data' => json_encode($attendance->toArray()),
            'ip_address' => $request->ip(),
            'device_info' => $validated['device_info'],
            'location_info' => json_encode(['distance' => $distance]),
            'status' => 'success',
            'remarks' => null,
        ]);

        return response()->json(['success' => true, 'attendance' => $attendance]);
    }

    // Check-out endpoint
    public function checkOut(Request $request)
    {
        $user = Auth::user();
        $validated = $request->validate([
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'device_id' => 'required|string',
            'device_info' => 'required|string',
        ]);

        $attendance = Attendance::where('user_id', $user->id)
            ->whereNull('check_out_time')
            ->first();
        if (!$attendance) {
            return response()->json(['error' => 'No open attendance.'], 404);
        }

        $attendance->check_out_time = Carbon::now();
        $attendance->check_out_lat = $validated['lat'];
        $attendance->check_out_lng = $validated['lng'];
        $attendance->check_out_address = $this->reverseGeocode($validated['lat'], $validated['lng']);
        $attendance->check_out_device = $validated['device_info'];
        $attendance->check_out_ip = $request->ip();
        $attendance->total_working_seconds = $attendance->check_out_time->diffInSeconds($attendance->check_in_time);
        $attendance->save();

        AttendanceAuditLog::create([
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'action' => 'check-out',
            'data' => json_encode($attendance->toArray()),
            'ip_address' => $request->ip(),
            'device_info' => $validated['device_info'],
            'location_info' => null,
            'status' => 'success',
            'remarks' => null,
        ]);

        return response()->json(['success' => true, 'attendance' => $attendance]);
    }

    // Manual override endpoint
    public function manualOverride(Request $request)
    {
        $user = Auth::user();
        if (!$user->is_admin) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        $validated = $request->validate([
            'attendance_id' => 'required|integer|exists:attendances,id',
            'action' => 'required|string|in:approve,reject,edit',
            'data' => 'nullable|array',
            'reason' => 'required|string',
        ]);

        $attendance = Attendance::findOrFail($validated['attendance_id']);
        $oldData = $attendance->toArray();
        $status = 'pending';

        if ($validated['action'] === 'approve') {
            if (!empty($validated['data'])) {
                $attendance->fill($validated['data']);
            }
            $attendance->status = 'manual';
            $attendance->save();
            $status = 'approved';
        } elseif ($validated['action'] === 'reject') {
            $status = 'rejected';
        } elseif ($validated['action'] === 'edit') {
            if (!empty($validated['data'])) {
                $attendance->fill($validated['data']);
                $attendance->status = 'manual';
                $attendance->save();
            }
            $status = 'approved';
        }

        \App\Models\AttendanceOverride::create([
            'attendance_id' => $attendance->id,
            'admin_id' => $user->id,
            'action' => $validated['action'],
            'data' => json_encode(['old' => $oldData, 'new' => $attendance->toArray()]),
            'reason' => $validated['reason'],
            'status' => $status,
        ]);

        AttendanceAuditLog::create([
            'attendance_id' => $attendance->id,
            'user_id' => $attendance->user_id,
            'action' => 'manual-override',
            'data' => json_encode(['old' => $oldData, 'new' => $attendance->toArray()]),
            'ip_address' => $request->ip(),
            'device_info' => $request->header('User-Agent'),
            'location_info' => null,
            'status' => $status,
            'remarks' => $validated['reason'],
        ]);

        $attendanceUser = $attendance->user;
        if ($attendanceUser) {
            $attendanceUser->notify(new \App\Notifications\AttendanceOverrideNotification($attendance, $status, $validated['reason']));
        }

        return response()->json([
            'success' => true,
            'attendance' => $attendance,
            'override_status' => $status
        ]);
    }

    // API: Attendance analytics
    public function analytics(Request $request)
    {
        $period = $request->query('period', 'daily');
        $start = $request->query('start');
        $end = $request->query('end');
        $query = Attendance::query();
        if ($start) $query->whereDate('check_in_time', '>=', $start);
        if ($end) $query->whereDate('check_in_time', '<=', $end);
        $data = [];
        if ($period === 'daily') {
            $data = $query->selectRaw('DATE(check_in_time) as date, COUNT(*) as total, SUM(late_flag) as late, SUM(early_leave_flag) as early')
                ->groupByRaw('DATE(check_in_time)')->orderBy('date')->get();
        } elseif ($period === 'monthly') {
            $data = $query->selectRaw('DATE_FORMAT(check_in_time, "%Y-%m") as month, COUNT(*) as total, SUM(late_flag) as late, SUM(early_leave_flag) as early')
                ->groupByRaw('DATE_FORMAT(check_in_time, "%Y-%m")')->orderBy('month')->get();
        }
        return response()->json(['analytics' => $data]);
    }

    // API: Absent report
    public function absentReport(Request $request)
    {
        $start = $request->query('start');
        $end = $request->query('end');
        $users = \App\Models\User::whereDoesntHave('attendances', function($q) use ($start, $end) {
            $q->whereBetween('check_in_time', [$start, $end]);
        })->get(['id', 'name', 'email']);
        return response()->json(['absent' => $users]);
    }

    // API: Today's attendance for map view
    public function todayMap(Request $request)
    {
        $date = $request->query('date', now()->toDateString());
        $records = Attendance::with(['user', 'branch'])
            ->whereDate('check_in_time', $date)
            ->get()
            ->map(function($a) {
                return [
                    'id' => $a->id,
                    'user_name' => $a->user ? ($a->user->name ?? $a->user->email) : 'Unknown',
                    'branch_name' => $a->branch ? $a->branch->name : '',
                    'check_in_time' => $a->check_in_time,
                    'check_out_time' => $a->check_out_time,
                    'check_in_lat' => $a->check_in_lat,
                    'check_in_lng' => $a->check_in_lng,
                    'check_out_lat' => $a->check_out_lat,
                    'check_out_lng' => $a->check_out_lng,
                    'status' => $a->status,
                ];
            });
        return response()->json(['records' => $records]);
    }

    // Haversine formula
    private function haversine($lat1, $lng1, $lat2, $lng2)
    {
        $earthRadius = 6371000;
        $lat1 = deg2rad($lat1);
        $lat2 = deg2rad($lat2);
        $deltaLat = $lat2 - $lat1;
        $deltaLng = deg2rad($lng2 - $lng1);
        $a = sin($deltaLat/2) * sin($deltaLat/2) + cos($lat1) * cos($lat2) * sin($deltaLng/2) * sin($deltaLng/2);
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        return $earthRadius * $c;
    }

    // Reverse geocode
    private function reverseGeocode($lat, $lng)
    {
        return "Lat: $lat, Lng: $lng";
    }
}

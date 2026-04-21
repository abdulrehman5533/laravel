<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\AttendanceLocation;
use App\Models\AttendanceAuditLog;
use Carbon\Carbon;

class AttendanceService
{
    public function validateGeoFence($lat, $lng, $branchId)
    {
        $location = AttendanceLocation::where('branch_id', $branchId)
            ->where('is_active', true)
            ->first();
        if (!$location) return false;
        $distance = $this->haversine($lat, $lng, $location->lat, $location->lng);
        return $distance <= $location->radius;
    }

    public function haversine($lat1, $lng1, $lat2, $lng2)
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

    public function reverseGeocode($lat, $lng)
    {
        return "Lat: $lat, Lng: $lng";
    }

    public function getCurrentShift($employee, $date = null)
    {
        $date = $date ?: now()->toDateString();
        $shift = $employee->shifts()
            ->where('start_date', '<=', $date)
            ->where(function($q) use ($date) {
                $q->whereNull('end_date')->orWhere('end_date', '>=', $date);
            })
            ->with('shift')
            ->latest('start_date')
            ->first();
        return $shift ? $shift->shift : null;
    }

    public function isOnLeave($employee, $date = null)
    {
        $date = $date ?: now()->toDateString();
        return $employee->leaveRequests()
            ->where('status', 'approved')
            ->where('start_date', '<=', $date)
            ->where('end_date', '>=', $date)
            ->exists();
    }

    public function validateDevice($user, $deviceId, $deviceInfo)
    {
        return true;
    }

    public function antiSpoofingChecks($request)
    {
        $integrity = $request->input('device_integrity', 'unknown');
        $isMock = $request->input('is_mock_location', false);
        
        if ($integrity !== 'pass' || $isMock) {
            return [false, 'Device or location spoofing detected'];
        }
        return [true, null];
    }

    public function processOfflineBatch($user, $records)
    {
        $results = [];
        foreach ($records as $rec) {
            $attendance = Attendance::create([
                'user_id' => $user->id,
                'branch_id' => $rec['branch_id'] ?? null,
                'check_in_time' => $rec['check_in_time'] ?? null,
                'check_in_lat' => $rec['check_in_lat'] ?? null,
                'check_in_lng' => $rec['check_in_lng'] ?? null,
                'check_out_time' => $rec['check_out_time'] ?? null,
                'check_out_lat' => $rec['check_out_lat'] ?? null,
                'check_out_lng' => $rec['check_out_lng'] ?? null,
                'is_offline' => true,
                'offline_payload' => json_encode($rec),
                'synced_at' => now(),
            ]);
            $results[] = $attendance->id;
        }
        return $results;
    }
}

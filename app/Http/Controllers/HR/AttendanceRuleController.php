<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\HR\AttendanceRule;
use Illuminate\Http\Request;

class AttendanceRuleController extends Controller
{
    public function index()
    {
        $rules = AttendanceRule::with('branch')->get();
        return view('hr.attendance_rules.index', compact('rules'));
    }

    public function create()
    {
        $branches = Branch::all();
        return view('hr.attendance_rules.create', compact('branches'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'rule_name' => 'required|string|max:255',
            'branch_id' => 'nullable|exists:branches,id',
            'shift_start' => 'required',
            'shift_end' => 'required',
            'grace_time_minutes' => 'nullable|integer|min:0',
            'half_day_late_minutes' => 'nullable|integer|min:0',
            'ot_rate_multiplier' => 'nullable|numeric|min:1',
            'require_gps' => 'boolean',
            'require_selfie' => 'boolean',
            'allowed_ip_range' => 'nullable|string',
            'geofencing_radius_meters' => 'nullable|integer|min:0',
            'office_latitude' => 'nullable|numeric',
            'office_longitude' => 'nullable|numeric',
            'is_default' => 'boolean',
            'weekly_off_days' => 'nullable|array',
        ]);

        if ($request->is_default) {
            AttendanceRule::where('is_default', true)->update(['is_default' => false]);
        }

        AttendanceRule::create($validated);

        return redirect()->route('hr.attendance-rules.index')->with('success', 'Attendance rule created successfully.');
    }

    public function edit(AttendanceRule $attendanceRule)
    {
        $branches = Branch::all();
        return view('hr.attendance_rules.edit', compact('attendanceRule', 'branches'));
    }

    public function update(Request $request, AttendanceRule $attendanceRule)
    {
        $validated = $request->validate([
            'rule_name' => 'required|string|max:255',
            'branch_id' => 'nullable|exists:branches,id',
            'shift_start' => 'required',
            'shift_end' => 'required',
            'grace_time_minutes' => 'nullable|integer|min:0',
            'half_day_late_minutes' => 'nullable|integer|min:0',
            'ot_rate_multiplier' => 'nullable|numeric|min:1',
            'require_gps' => 'boolean',
            'require_selfie' => 'boolean',
            'allowed_ip_range' => 'nullable|string',
            'geofencing_radius_meters' => 'nullable|integer|min:0',
            'office_latitude' => 'nullable|numeric',
            'office_longitude' => 'nullable|numeric',
            'is_default' => 'boolean',
            'weekly_off_days' => 'nullable|array',
        ]);

        if ($request->is_default) {
            AttendanceRule::where('is_default', true)->where('id', '!=', $attendanceRule->id)->update(['is_default' => false]);
        }

        $attendanceRule->update($validated);

        return redirect()->route('hr.attendance-rules.index')->with('success', 'Attendance rule updated successfully.');
    }

    public function destroy(AttendanceRule $attendanceRule)
    {
        if ($attendanceRule->is_default) {
            return back()->with('error', 'Cannot delete default rule.');
        }

        $attendanceRule->delete();

        return redirect()->route('hr.attendance-rules.index')->with('success', 'Attendance rule deleted successfully.');
    }
}

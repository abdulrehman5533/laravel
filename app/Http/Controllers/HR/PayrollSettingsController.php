<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\HR\AttendanceRule;
use App\Models\HR\SalaryComponent;
use Illuminate\Http\Request;

class PayrollSettingsController extends Controller
{
    public function index()
    {
        $components = SalaryComponent::all();
        $rules = AttendanceRule::all();

        return view('hr.payroll.settings', compact('components', 'rules'));
    }

    public function storeComponent(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:earning,deduction',
            'calculation_type' => 'required|in:fixed,percentage_of_basic',
            'default_value' => 'required|numeric',
            'is_taxable' => 'boolean',
            'is_mandatory' => 'boolean',
        ]);

        SalaryComponent::create($request->all());

        return back()->with('success', 'Salary component created successfully.');
    }

    public function storeRule(Request $request)
    {
        $request->validate([
            'rule_name' => 'required|string|max:255',
            'shift_start' => 'required',
            'shift_end' => 'required',
            'grace_time_minutes' => 'required|integer',
            'half_day_late_minutes' => 'required|integer',
            'ot_rate_multiplier' => 'required|numeric',
        ]);

        if ($request->has('is_default')) {
            AttendanceRule::where('is_default', true)->update(['is_default' => false]);
        }

        AttendanceRule::create($request->all());

        return back()->with('success', 'Attendance rule created successfully.');
    }
}

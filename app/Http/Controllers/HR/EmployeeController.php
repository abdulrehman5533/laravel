<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmployeeController extends Controller
{
    public function index()
    {
        $employees = Employee::with('branch')->get();

        return view('hr.employees.index', compact('employees'));
    }

    public function create()
    {
        $branches = Branch::all();
        $users = User::all();

        return view('hr.employees.create', compact('branches', 'users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'employee_code' => 'required|unique:employees',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:employees',
            'phone' => 'nullable|string|max:20',
            'branch_id' => 'required|exists:branches,id',
            'department' => 'required|string|max:255',
            'designation' => 'required|string|max:255',
            'joining_date' => 'required|date',
            'user_id' => 'nullable|exists:users,id|unique:employees,user_id',
            'base_salary' => 'required|numeric',
            'hra' => 'nullable|numeric|min:0',
            'medical_allowance' => 'nullable|numeric|min:0',
            'transport_allowance' => 'nullable|numeric|min:0',
            'eobi_deduction' => 'nullable|numeric|min:0',
            'pessi_deduction' => 'nullable|numeric|min:0',
            'income_tax' => 'nullable|numeric|min:0',
            'country_code' => 'required|string|max:5',
            'is_karigar' => 'boolean',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|string|in:Male,Female,Other',
            'address' => 'nullable|string',
            'national_id' => 'nullable|string|max:100',
            'tax_id' => 'nullable|string|max:100',
            'probation_end_date' => 'nullable|date',
            'biometric_id' => 'nullable|string|max:50',
            'status' => 'required|string|in:active,inactive,on_leave,terminated',
            'commission_percentage' => 'nullable|numeric|min:0|max:100',
            'emergency_contact' => 'nullable|array',
            'bank_details' => 'nullable|array',
            'skills' => 'nullable|string',
        ]);

        DB::transaction(function () use ($request) {
            $data = $request->all();

            if ($request->filled('skills')) {
                $data['skills'] = array_map('trim', explode(',', $request->skills));
            }

            $employee = Employee::create($data);

            $employee->lifecycleEvents()->create([
                'event_type' => 'onboarding',
                'effective_date' => $request->joining_date,
                'description' => 'Employee joined the company',
                'recorded_by' => auth()->id(),
            ]);
        });

        return redirect()->route('hr.employees.index')->with('success', 'Employee created successfully.');
    }

    public function show(Employee $employee)
    {
        $employee->load([
            'branch',
            'lifecycleEvents',
            'documents',
            'trainings',
            'appraisals',
            'loans',
            'expenses',
            'shifts',
            'attendances' => fn($q) => $q->orderBy('date', 'desc')->limit(30),
            'leaveApplications' => fn($q) => $q->orderBy('start_date', 'desc'),
            'salaryStructures.component',
            'karigarRates',
            'salesCommissionRates',
        ]);

        return view('hr.employees.show', compact('employee'));
    }

    public function edit(Employee $employee)
    {
        $branches = Branch::all();
        $users = User::all();

        return view('hr.employees.edit', compact('employee', 'branches', 'users'));
    }

    public function update(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'employee_code' => 'required|unique:employees,employee_code,'.$employee->id,
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:employees,email,'.$employee->id,
            'phone' => 'nullable|string|max:20',
            'branch_id' => 'required|exists:branches,id',
            'department' => 'required|string|max:255',
            'designation' => 'required|string|max:255',
            'joining_date' => 'required|date',
            'user_id' => 'nullable|exists:users,id|unique:employees,user_id,'.$employee->id,
            'base_salary' => 'required|numeric',
            'hra' => 'nullable|numeric|min:0',
            'medical_allowance' => 'nullable|numeric|min:0',
            'transport_allowance' => 'nullable|numeric|min:0',
            'eobi_deduction' => 'nullable|numeric|min:0',
            'pessi_deduction' => 'nullable|numeric|min:0',
            'income_tax' => 'nullable|numeric|min:0',
            'country_code' => 'required|string|max:5',
            'is_karigar' => 'boolean',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|string|in:Male,Female,Other',
            'address' => 'nullable|string',
            'national_id' => 'nullable|string|max:100',
            'tax_id' => 'nullable|string|max:100',
            'probation_end_date' => 'nullable|date',
            'biometric_id' => 'nullable|string|max:50',
            'status' => 'required|string|in:active,inactive,on_leave,terminated',
            'commission_percentage' => 'nullable|numeric|min:0|max:100',
            'emergency_contact' => 'nullable|array',
            'bank_details' => 'nullable|array',
            'skills' => 'nullable|string',
        ]);

        DB::transaction(function () use ($request, $employee) {
            $data = $request->all();
            
            // Handle skills string to array
            if ($request->filled('skills')) {
                $data['skills'] = array_map('trim', explode(',', $request->skills));
            } else {
                $data['skills'] = [];
            }

            $employee->update($data);
        });

        return redirect()->route('hr.employees.index')->with('success', 'Employee updated successfully.');
    }

    public function destroy(Employee $employee)
    {
        $employee->delete();

        return redirect()->route('hr.employees.index')->with('success', 'Employee deleted successfully.');
    }

    public function assignShift(Request $request, Employee $employee)
    {
        $request->validate([
            'shift_id' => 'required|exists:hr_shifts,id',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $employee->assignShift($request->shift_id, $request->start_date, $request->end_date);

        return back()->with('success', 'Shift assigned successfully.');
    }
}

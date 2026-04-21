<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\HR\EmployeeSalaryStructure;
use App\Models\HR\KarigarRate;
use App\Models\HR\SalesCommissionRate;
use Illuminate\Http\Request;

class CompensationController extends Controller
{
    public function updateSalaryStructure(Request $request, Employee $employee)
    {
        $request->validate([
            'component_id' => 'required|exists:hr_salary_components,id',
            'amount' => 'required|numeric',
        ]);

        EmployeeSalaryStructure::updateOrCreate(
            ['employee_id' => $employee->id, 'component_id' => $request->component_id],
            ['amount' => $request->amount]
        );

        return back()->with('success', 'Salary structure updated.');
    }

    public function updateKarigarRate(Request $request, Employee $employee)
    {
        $request->validate([
            'item_category' => 'required|string',
            'rate_per_gram' => 'required|numeric',
            'rate_per_piece' => 'required|numeric',
        ]);

        KarigarRate::updateOrCreate(
            ['employee_id' => $employee->id, 'item_category' => $request->item_category],
            [
                'rate_per_gram' => $request->rate_per_gram,
                'rate_per_piece' => $request->rate_per_piece,
            ]
        );

        return back()->with('success', 'Karigar rate updated.');
    }

    public function updateSalesCommission(Request $request, Employee $employee)
    {
        $request->validate([
            'product_category' => 'required|string',
            'commission_percent' => 'required|numeric',
        ]);

        SalesCommissionRate::updateOrCreate(
            ['employee_id' => $employee->id, 'product_category' => $request->product_category],
            ['commission_percent' => $request->commission_percent]
        );

        return back()->with('success', 'Sales commission updated.');
    }
}

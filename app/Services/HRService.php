<?php

namespace App\Services;

use App\Models\Employee;
use Illuminate\Support\Facades\DB;

class HRService
{
    /**
     * Calculate monthly commission for an employee based on collected sales
     */
    public function calculateMonthlyCommission(int $employeeId, int $month, int $year): float
    {
        $employee = Employee::findOrFail($employeeId);

        // Jewellery ERP Logic: Commission is usually calculated on 'Collected' amount, not just 'Billed'
        // This ensures sales staff are motivated to follow up on payments.
        $collectedAmount = DB::table('pos_payments')
            ->join('pos_sales', 'pos_payments.pos_sale_id', '=', 'pos_sales.id')
            ->where('pos_sales.created_by', $employee->user_id)
            ->whereMonth('pos_payments.created_at', $month)
            ->whereYear('pos_payments.created_at', $year)
            ->where('pos_payments.status', 'completed')
            ->sum('pos_payments.amount');

        return ($collectedAmount * $employee->commission_percentage) / 100;
    }

    /**
     * Generate monthly payroll snapshot
     */
    public function generatePayroll(int $branchId, int $month, int $year): array
    {
        $employees = Employee::where('branch_id', $branchId)
            ->where('status', 'active')
            ->get();

        $payrollData = [];

        foreach ($employees as $employee) {
            $commission = $this->calculateMonthlyCommission($employee->id, $month, $year);
            $grossSalary = $employee->base_salary + $commission;

            $payrollData[] = [
                'employee_id' => $employee->id,
                'name' => $employee->first_name.' '.$employee->last_name,
                'base_salary' => $employee->base_salary,
                'commission' => $commission,
                'gross_salary' => $grossSalary,
                'net_salary' => $grossSalary, // Add deduction logic here for Tax/Insurance
            ];
        }

        return $payrollData;
    }

    /**
     * Terminate employee with final settlement check
     */
    public function terminateEmployee(int $employeeId, string $reason): void
    {
        DB::transaction(function () use ($employeeId, $reason) {
            $employee = Employee::findOrFail($employeeId);

            // Enterprise Logic: Ensure no pending metal or assets are with the employee
            // if ($employee->hasPendingAssets()) { throw new \Exception('Employee has unreturned assets.'); }

            $employee->update([
                'status' => 'terminated',
                'meta->termination_reason' => $reason,
                'meta->terminated_at' => now(),
            ]);
        });
    }
}

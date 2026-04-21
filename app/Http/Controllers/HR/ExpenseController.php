<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\HR\EmployeeExpense;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function index()
    {
        $expenses = EmployeeExpense::with(['employee', 'approvedBy'])->latest()->get();

        return view('hr.expenses.index', compact('expenses'));
    }

    public function store(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'title' => 'required|string',
            'amount' => 'required|numeric',
            'expense_date' => 'required|date',
            'category' => 'required|string',
            'description' => 'nullable|string',
            'receipt' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        $path = null;
        if ($request->hasFile('receipt')) {
            $path = $request->file('receipt')->store('hr/expenses', 'public');
            $path = 'storage/'.$path;
        }

        EmployeeExpense::create([
            'employee_id' => $employee->id,
            'title' => $validated['title'],
            'amount' => $validated['amount'],
            'expense_date' => $validated['expense_date'],
            'category' => $validated['category'],
            'description' => $validated['description'],
            'receipt_path' => $path,
            'status' => 'pending',
        ]);

        return back()->with('success', 'Expense claim submitted successfully.');
    }

    public function approve(EmployeeExpense $expense)
    {
        $expense->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
        ]);

        // In a real system, this would also trigger a payment or accounting entry
        return back()->with('success', 'Expense claim approved.');
    }
}

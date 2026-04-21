<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\ExpenseSubcategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ExpenseController extends Controller
{
    public function index(Request $request): View
    {
        $query = Expense::query();

        if ($request->branch_id) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->category_id) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->date_from && $request->date_to) {
            $query->whereBetween('date', [$request->date_from, $request->date_to]);
        }

        $expenses = $query->with(['branch', 'category', 'subcategory', 'createdBy', 'attachments'])
            ->orderBy('date', 'desc')
            ->paginate(20);

        $branches = Branch::where('is_active', true)->get();
        $categories = ExpenseCategory::where('is_active', true)->get();

        return view('accounts.expense.index', compact('expenses', 'branches', 'categories'));
    }

    public function create(): View
    {
        $branches = Branch::where('is_active', true)->get();
        $categories = ExpenseCategory::where('is_active', true)->get();

        return view('accounts.expense.create', compact('branches', 'categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'category_id' => 'required|exists:expense_categories,id',
            'subcategory_id' => 'nullable|exists:expense_subcategories,id',
            'date' => 'required|date',
            'vendor_name' => 'nullable|string|max:100',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string',
            'description' => 'nullable|string|max:500',
            'is_recurring' => 'boolean',
            'recurrence_type' => 'nullable|in:daily,weekly,monthly,yearly',
        ]);

        $validated['created_by'] = Auth::id();
        $validated['status'] = 'pending';
        $validated['is_recurring'] = $request->boolean('is_recurring', false);

        $expense = Expense::create($validated);

        // Handle file uploads
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('expenses', 'public');
                $expense->attachments()->create([
                    'file_path' => $path,
                    'file_type' => $request->input("file_type.{$file->hashName()}") ?? $file->getClientMimeType(),
                    'original_name' => $file->getClientOriginalName(),
                ]);
            }
        }

        return redirect()->route('accounts.expense.index')
            ->with('success', 'Expense created successfully. Awaiting approval.');
    }

    public function show(Expense $expense): View
    {
        $expense->load(['branch', 'category', 'subcategory', 'createdBy', 'approvedBy', 'attachments']);

        return view('accounts.expense.show', compact('expense'));
    }

    public function edit(Expense $expense)
    {
        if ($expense->status !== 'pending') {
            return redirect()->back()->with('error', 'Cannot edit approved or rejected expenses');
        }

        $branches = Branch::where('is_active', true)->get();
        $categories = ExpenseCategory::where('is_active', true)->get();
        $subcategories = ExpenseSubcategory::where('category_id', $expense->category_id)->get();

        return view('accounts.expense.edit', compact('expense', 'branches', 'categories', 'subcategories'));
    }

    public function update(Request $request, Expense $expense): RedirectResponse
    {
        if ($expense->status !== 'pending') {
            return back()->with('error', 'Cannot edit approved or rejected expenses');
        }

        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'category_id' => 'required|exists:expense_categories,id',
            'subcategory_id' => 'nullable|exists:expense_subcategories,id',
            'date' => 'required|date',
            'vendor_name' => 'nullable|string|max:100',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string',
            'description' => 'nullable|string|max:500',
        ]);

        $expense->update($validated);

        return redirect()->route('accounts.expense.index')
            ->with('success', 'Expense updated successfully');
    }

    public function destroy(Expense $expense): RedirectResponse
    {
        if ($expense->status !== 'pending') {
            return back()->with('error', 'Cannot delete approved or rejected expenses');
        }

        $expense->attachments()->delete();
        $expense->delete();

        return redirect()->route('accounts.expense.index')
            ->with('success', 'Expense deleted successfully');
    }

    public function approve(Request $request, Expense $expense): RedirectResponse
    {
        if ($expense->status !== 'pending') {
            return back()->with('error', 'Only pending expenses can be approved');
        }

        $expense->update([
            'status' => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Expense approved successfully');
    }

    public function reject(Request $request, Expense $expense): RedirectResponse
    {
        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);
        $expense->update([
            'status' => 'rejected',
            'rejection_reason' => $validated['rejection_reason'],
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Expense rejected successfully');
    }

    public function getSubcategories(Request $request): array
    {
        $categoryId = $request->query('category_id');
        $subcategories = ExpenseSubcategory::where('category_id', $categoryId)
            ->where('is_active', true)
            ->get();

        return $subcategories->toArray();
    }

    public function export(Request $request)
    {
        $query = Expense::query();

        if ($request->branch_id) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->category_id) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->date_from && $request->date_to) {
            $query->whereBetween('date', [$request->date_from, $request->date_to]);
        }

        $rows = $query->with(['branch', 'category', 'subcategory'])->orderBy('date', 'desc')->get();

        $filename = 'expenses_export_'.now()->format('Ymd_His').'.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $columns = ['date', 'branch', 'category', 'subcategory', 'vendor', 'amount', 'payment_method', 'status', 'description'];

        $callback = function () use ($rows, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($rows as $row) {
                fputcsv($file, [
                    $row->date->toDateString(),
                    $row->branch->name ?? '',
                    $row->category->name ?? '',
                    $row->subcategory->name ?? '',
                    $row->vendor_name,
                    $row->amount,
                    $row->payment_method,
                    $row->status,
                    $row->description,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Models\Branch;
use App\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $query = Customer::query();

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('customer_code', 'like', "%{$search}%");
            });
        }

        // Filters
        if ($request->filled('type')) {
            $query->where('customer_type', $request->type);
        }

        if ($request->filled('level')) {
            $query->where('membership_level', $request->level);
        }

        if ($request->has('active') && $request->active !== null) {
            $query->where('is_active', $request->active);
        }

        $customers = $query->latest()->paginate(15)->withQueryString();

        return view('customers.index', compact('customers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $branches = Branch::all();

        return view('customers.create', compact('branches'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCustomerRequest $request): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $data = $request->validated();

        // Generate unique customer code if not provided
        if (! isset($data['customer_code']) || empty($data['customer_code'])) {
            $data['customer_code'] = 'CUST-'.strtoupper(Str::random(8));
        }

        // Also sync 'name' for compatibility
        $data['name'] = ($data['first_name'] ?? '').' '.($data['last_name'] ?? '');
        $data['created_by'] = auth()->id();

        $customer = Customer::create($data);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Customer created successfully.',
                'customer' => [
                    'id' => $customer->id,
                    'name' => $customer->full_name,
                    'phone' => $customer->phone,
                    'email' => $customer->email,
                    'customer_type' => $customer->customer_type,
                    'vip_tier' => $customer->membership_level,
                    'current_balance' => (float) $customer->current_balance,
                    'current_gold_balance' => (float) $customer->current_gold_balance,
                    'current_silver_balance' => (float) $customer->current_silver_balance,
                    'available_credit' => (float) $customer->credit_limit - (float) $customer->current_balance,
                    'loyalty_points' => $customer->loyalty_points,
                ],
            ]);
        }

        return redirect()->route('customers.index')
            ->with('success', 'Customer created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Customer $customer): View
    {
        $customer->load(['sales', 'interactions', 'branch']);

        return view('customers.show', compact('customer'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Customer $customer): View
    {
        $branches = Branch::all();

        return view('customers.edit', compact('customer', 'branches'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCustomerRequest $request, Customer $customer): RedirectResponse
    {
        $data = $request->validated();

        // Sync 'name' for compatibility
        $data['name'] = $data['first_name'].' '.$data['last_name'];

        $customer->update($data);

        return redirect()->route('customers.index')
            ->with('success', 'Customer updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Customer $customer): RedirectResponse
    {
        $customer->delete();

        return redirect()->route('customers.index')
            ->with('success', 'Customer deleted successfully.');
    }

    /**
     * Display customer interactions.
     */
    public function interactions(Customer $customer): View
    {
        $interactions = $customer->interactions()->latest()->paginate(10);

        return view('customers.interactions', compact('customer', 'interactions'));
    }

    /**
     * Add interaction for customer.
     */
    public function addInteraction(Request $request, Customer $customer): RedirectResponse
    {
        $data = $request->validate([
            'interaction_type' => 'required|string',
            'description' => 'required|string',
            'interaction_date' => 'required|date',
        ]);

        $customer->interactions()->create([
            'interaction_type' => $data['interaction_type'],
            'description' => $data['description'],
            'interaction_date' => $data['interaction_date'],
            'status' => 'completed',
            'created_by' => auth()->id(),
        ]);

        return back()->with('success', 'Interaction added successfully.');
    }

    /**
     * Display customer purchase history.
     */
    public function purchaseHistory(Customer $customer): View
    {
        $sales = $customer->sales()->latest()->paginate(10);

        return view('customers.purchase_history', compact('customer', 'sales'));
    }

    /**
     * Display customer ledger.
     */
    public function ledger(Customer $customer): View
    {
        // Implementation for customer ledger
        return view('customers.ledger', compact('customer'));
    }

    /**
     * Add loyalty points to customer.
     */
    public function addLoyaltyPoints(Request $request, Customer $customer): RedirectResponse
    {
        $request->validate(['points' => 'required|integer|min:1']);
        $customer->addLoyaltyPoints($request->points);

        return back()->with('success', "Added {$request->points} loyalty points.");
    }

    /**
     * Deduct loyalty points from customer.
     */
    public function deductLoyaltyPoints(Request $request, Customer $customer): RedirectResponse
    {
        $request->validate(['points' => 'required|integer|min:1']);
        if ($customer->deductLoyaltyPoints($request->points)) {
            return back()->with('success', "Deducted {$request->points} loyalty points.");
        }

        return back()->with('error', 'Insufficient loyalty points.');
    }

    /**
     * Lookup customers via AJAX for POS.
     */
    public function lookup(Request $request): \Illuminate\Http\JsonResponse
    {
        $q = $request->get('q');

        if (! $q) {
            return response()->json([]);
        }

        $customers = Customer::where('first_name', 'like', "%{$q}%")
            ->orWhere('last_name', 'like', "%{$q}%")
            ->orWhere('phone', 'like', "%{$q}%")
            ->orWhere('email', 'like', "%{$q}%")
            ->orWhere('customer_code', 'like', "%{$q}%")
            ->limit(10)
            ->get();

        $formatted = $customers->map(function ($c) {
            return [
                'id' => $c->id,
                'name' => $c->full_name,
                'phone' => $c->phone,
                'email' => $c->email,
                'customer_type' => $c->customer_type,
                'vip_tier' => $c->membership_level,
                'current_balance' => (float) $c->current_balance,
                'current_gold_balance' => (float) $c->current_gold_balance,
                'current_silver_balance' => (float) $c->current_silver_balance,
                'available_credit' => (float) $c->credit_limit - (float) $c->current_balance,
                'loyalty_points' => $c->loyalty_points,
            ];
        });

        return response()->json($formatted);
    }

    /**
     * Export customer details to PDF.
     */
    public function exportPdf(Customer $customer)
    {
        // Implementation for PDF export
        return back()->with('info', 'PDF Export functionality coming soon.');
    }

    /**
     * Export customer details to Excel.
     */
    public function exportExcel(Customer $customer)
    {
        // Implementation for Excel export
        return back()->with('info', 'Excel Export functionality coming soon.');
    }
}

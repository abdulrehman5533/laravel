<?php

namespace App\Http\Controllers\Girvi;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Girvi;
use App\Models\GoldRate;
use App\Services\GirviService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GirviController extends Controller
{
    protected $girviService;

    public function __construct(GirviService $girviService)
    {
        $this->girviService = $girviService;
    }

    public function index(Request $request)
    {
        $query = Girvi::with(['customer', 'branch', 'items', 'payments']);

        if ($request->status)          $query->where('status', $request->status);
        if ($request->branch_id)       $query->where('branch_id', $request->branch_id);
        if ($request->interest_cycle)  $query->where('interest_cycle', $request->interest_cycle);

        if ($request->customer) {
            $query->whereHas('customer', function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->customer}%")
                  ->orWhere('phone', 'like', "%{$request->customer}%")
                  ->orWhere('id', $request->customer);
            })->orWhere('girvi_number', 'like', "%{$request->customer}%");
        }

        if ($request->from_date) $query->whereDate('girvi_date', '>=', $request->from_date);
        if ($request->to_date)   $query->whereDate('girvi_date', '<=', $request->to_date);

        if ($request->overdue_days) {
            $days = (int) $request->overdue_days;
            $query->where('status', 'active')
                  ->where('maturity_date', '<', now()->subDays($days));
        }

        // Stats — integrated with real data
        $stats = [
            'active'          => Girvi::where('status', 'active')->count(),
            'outstanding'     => Girvi::where('status', 'active')->sum('outstanding_amount'),
            'overdue'         => Girvi::where('status', 'overdue')->orWhere(function($q){ $q->where('status','active')->where('maturity_date','<',now()); })->count(),
            'settled_month'   => Girvi::whereIn('status', ['settled'])->whereMonth('updated_at', now()->month)->count(),
            'total_disbursed' => Girvi::sum('loan_amount'),
            'interest_month'  => \App\Models\GirviPayment::whereMonth('payment_date', now()->month)->sum('interest_component'),
            'high_risk'       => Girvi::where('is_high_risk', true)->where('status', 'active')->count(),
            'maturing_soon'   => Girvi::where('status', 'active')->whereBetween('maturity_date', [now(), now()->addDays(7)])->count(),
        ];

        $girvis  = $query->latest('girvi_date')->paginate(20);
        $branches = Branch::where('is_active', true)->get();

        return view('girvi.index', compact('girvis', 'stats', 'branches'));
    }

    public function create()
    {
        $customers = Customer::all();
        $branches = Branch::all();
        $currentGoldRate = GoldRate::latest()->first();
        $currentSilverRate = 75.00; // Default if not in DB

        $rates = $this->girviService->getMarketRates();
        $goldRate = $rates['gold'];
        $silverRate = $rates['silver'];

        return view('girvi.create', compact('customers', 'branches', 'goldRate', 'silverRate'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'branch_id' => 'required|exists:branches,id',
            'loan_amount' => 'required|numeric|min:1',
            'interest_rate' => 'nullable|numeric|min:0', // This is monthly from UI
            'interest_cycle' => 'nullable|in:daily,monthly,quarterly,yearly',
            'interest_type' => 'nullable|in:simple,compound',
            'locked_gold_rate' => 'nullable|numeric',
            'locked_silver_rate' => 'nullable|numeric',
            'girvi_date' => 'required|date',
            'maturity_date' => 'nullable|date',
            'kyc_verified' => 'nullable|boolean',
            'internal_notes' => 'nullable|string',
            'loan_purpose' => 'nullable|string',
            'guarantor_name' => 'nullable|string',
            'guarantor_phone' => 'nullable|string',
            'guarantor_id_type' => 'nullable|string',
            'guarantor_id_number' => 'nullable|string',
            'auto_renew' => 'nullable|boolean',
            'items' => 'required|array|min:1',
            'items.*.item_name' => 'required|string',
            'items.*.inventory_product_id' => [
                'nullable',
                'exists:inventory_products,id',
            ],
            'items.*.item_type' => 'required|in:Gold,Silver,Diamond',
            'items.*.gross_weight' => 'required|numeric',
            'items.*.stone_weight' => 'nullable|numeric',
            'items.*.net_weight' => 'nullable|numeric',
            'items.*.purity' => 'required',
            'items.*.valuation_rate' => 'nullable|numeric',
            'items.*.estimated_value' => 'nullable|numeric',
            'items.*.item_condition' => 'nullable|string',
            'items.*.description' => 'nullable|string',
            'items.*.item_photo' => 'nullable|image|max:2048',
            'items.*.locker_location' => 'nullable|string',
            'items.*.bag_number' => 'nullable|string',
            'items.*.box_number' => 'nullable|string',
            'items.*.tag_number' => 'nullable|string',
        ]);

        // Convert monthly rate to annual for DB if needed (migration says annual %)
        if (isset($validated['interest_rate'])) {
            $validated['interest_rate'] = $validated['interest_rate'] * 12;
        }

        try {
            $girvi = $this->girviService->createGirvi($validated, $request->items);

            if ($request->has('print_after_save')) {
                return redirect()->route('girvi.loans.print', $girvi);
            }

            return redirect()->route('girvi.loans.show', $girvi)->with('success', 'Girvi created successfully');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Error creating Girvi: '.$e->getMessage());
        }
    }

    public function show(Girvi $girvi)
    {
        $girvi->load(['customer', 'branch', 'items', 'payments', 'interestPostings', 'reminders']);

        return view('girvi.show', compact('girvi'));
    }

    public function recordPayment(Request $request, Girvi $girvi)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:1',
            'payment_date' => 'required|date',
            'payment_method' => 'required|string',
            'reference_number' => 'nullable|string',
            'waiver_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $payment = $this->girviService->recordPayment($girvi, $validated);

        return back()->with('success', 'Payment recorded successfully')
            ->with('payment_id', $payment->id);
    }

    public function print(Girvi $girvi)
    {
        $girvi->load(['customer', 'branch', 'items']);

        return view('girvi.print', compact('girvi'));
    }

    public function receipt(\App\Models\GirviPayment $payment)
    {
        $payment->load(['girvi.customer', 'girvi.branch']);

        return view('girvi.receipt', compact('payment'));
    }

    public function customerHistory(Customer $customer)
    {
        $activeLoans = Girvi::where('customer_id', $customer->id)
            ->where('status', 'active')
            ->get();

        return response()->json([
            'count' => $activeLoans->count(),
            'total_principal' => $activeLoans->sum('loan_amount'),
            'total_outstanding' => $activeLoans->sum('outstanding_amount'),
            'risk_score' => $activeLoans->count() > 3 ? 'High' : ($activeLoans->count() > 1 ? 'Medium' : 'Low'),
            'loans' => $activeLoans->map(function ($l) {
                return [
                    'number' => $l->girvi_number,
                    'amount' => $l->loan_amount,
                    'date' => $l->girvi_date->format('d M Y'),
                ];
            }),
        ]);
    }

    public function lookupProducts(Request $request)
    {
        $q = $request->get('q');
        if (! $q) {
            return response()->json([]);
        }

        $products = \App\Models\InventoryProduct::with(['purity', 'category', 'images'])
            ->where(function ($query) use ($q) {
                $query->where('name', 'like', "%$q%")
                    ->orWhere('sku', 'like', "%$q%")
                    ->orWhere('barcode', 'like', "%$q%");
            })
            ->limit(20)
            ->get()
            ->map(function ($p) {
                // Guess item type from category or name
                $type = 'Gold';
                $categoryName = strtolower($p->category?->name ?? '');
                $productName = strtolower($p->name);
                $purityName = strtolower($p->purity?->name ?? '');

                if (str_contains($categoryName, 'silver') || str_contains($productName, 'silver') || str_contains($purityName, '925') || str_contains($purityName, '90')) {
                    $type = 'Silver';
                } elseif (str_contains($categoryName, 'diamond') || str_contains($productName, 'diamond')) {
                    $type = 'Diamond';
                }

                return [
                    'id' => $p->id,
                    'text' => $p->name.' ['.($p->sku ?? 'NO-SKU').'] - '.($p->purity?->name ?? 'N/A'),
                    'name' => $p->name,
                    'type' => $type,
                    'gross_weight' => (float) $p->weight,
                    'net_weight' => (float) $p->weight,
                    'purity' => $p->purity?->name ?? '22K',
                    'cost_price' => (float) $p->cost_price,
                    'selling_price' => (float) $p->selling_price,
                    'image' => $p->images->first()?->image_path ? asset('storage/'.$p->images->first()->image_path) : null,
                ];
            });

        return response()->json($products);
    }

    public function postInterest(Girvi $girvi)
    {
        $posting = $this->girviService->calculateAndPostInterest($girvi, true);

        if ($posting) {
            return back()->with('success', 'Interest posted successfully');
        }

        return back()->with('info', 'No interest to post at this time');
    }

    public function release(Request $request, Girvi $girvi)
    {
        $validated = $request->validate([
            'closure_type' => 'required|in:normal,early,loss,auction',
            'payment.amount' => 'nullable|numeric|min:0',
            'payment.payment_method' => 'nullable|string',
            'payment.payment_date' => 'nullable|date',
        ]);

        try {
            $this->girviService->releaseGirvi($girvi, $validated);

            return redirect()->route('girvi.loans.index')->with('success', 'Girvi released successfully');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function transfer(Request $request, Girvi $girvi)
    {
        $validated = $request->validate([
            'transfer_type' => 'required|in:Bank,Third Party,Finance Co',
            'transferred_to' => 'required|string|max:255',
            'transfer_amount' => 'required|numeric|min:0',
            'transfer_date' => 'required|date',
            'transfer_charges' => 'nullable|numeric|min:0',
            'transfer_terms' => 'nullable|string',
        ]);

        try {
            $this->girviService->transferGirvi($girvi, $validated);

            return redirect()->route('girvi.loans.show', $girvi)->with('success', 'Girvi transferred successfully');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function renew(Request $request, Girvi $girvi)
    {
        $validated = $request->validate([
            'new_maturity_date' => 'required|date|after:today',
            'new_interest_rate' => 'nullable|numeric|min:0',
            'payment_amount' => 'nullable|numeric|min:0',
            'payment_method' => 'nullable|string',
        ]);

        try {
            $this->girviService->renewGirvi($girvi, $validated);

            return redirect()->route('girvi.loans.show', $girvi)->with('success', 'Girvi renewed successfully');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function requestWaiver(Request $request, Girvi $girvi)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:1',
            'reason' => 'required|string',
        ]);

        $this->girviService->requestWaiver($girvi, $validated['amount'], $validated['reason']);

        return back()->with('success', 'Waiver request submitted for approval');
    }

    public function export(Request $request)
    {
        $query = Girvi::with(['customer', 'branch', 'items']);

        if ($request->status)     $query->where('status', $request->status);
        if ($request->branch_id)  $query->where('branch_id', $request->branch_id);
        if ($request->from_date)  $query->whereDate('girvi_date', '>=', $request->from_date);
        if ($request->to_date)    $query->whereDate('girvi_date', '<=', $request->to_date);
        if ($request->customer) {
            $query->whereHas('customer', fn($q) => $q->where('name', 'like', "%{$request->customer}%")
                ->orWhere('phone', 'like', "%{$request->customer}%"))
                ->orWhere('girvi_number', 'like', "%{$request->customer}%");
        }

        $girvis = $query->latest('girvi_date')->get();

        if ($request->format === 'pdf') {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('girvi.exports.pdf', compact('girvis'));
            return $pdf->download('girvi-registry-' . now()->format('Y-m-d') . '.pdf');
        }

        // Excel export using simple CSV
        $filename = 'girvi-registry-' . now()->format('Y-m-d') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
        ];

        $callback = function () use ($girvis) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Girvi No', 'Date', 'Customer', 'Phone', 'Branch', 'Principal', 'Interest Rate', 'Outstanding', 'Maturity Date', 'Status', 'KYC']);
            foreach ($girvis as $g) {
                fputcsv($file, [
                    $g->girvi_number,
                    $g->girvi_date->format('d/m/Y'),
                    $g->customer->name,
                    $g->customer->phone,
                    $g->branch->name,
                    $g->loan_amount,
                    $g->interest_rate . '%',
                    $g->outstanding_amount,
                    $g->maturity_date?->format('d/m/Y') ?? 'N/A',
                    strtoupper($g->status),
                    $g->kyc_verified ? 'Yes' : 'No',
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}

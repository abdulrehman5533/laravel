<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Buyback;
use App\Models\Customer;
use App\Models\GoldRate;
use App\Services\BuybackService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BuybackController extends Controller
{
    public function __construct(protected BuybackService $buybackService) {}

    public function index()
    {
        $buybacks = Buyback::with(['customer', 'branch', 'creator'])->latest()->paginate(20);
        $stats = [
            'total'     => Buyback::count(),
            'pending'   => Buyback::where('status', 'pending')->count(),
            'completed' => Buyback::where('status', 'completed')->count(),
            'total_value' => Buyback::where('status', 'completed')->sum('total_value'),
        ];
        return view('buyback.index', compact('buybacks', 'stats'));
    }

    public function create()
    {
        $customers  = Customer::orderBy('name')->get();
        $branches   = Branch::where('is_active', true)->get();
        $goldRate   = GoldRate::latest('date')->first();
        return view('buyback.create', compact('customers', 'branches', 'goldRate'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id'          => 'required|exists:customers,id',
            'branch_id'            => 'required|exists:branches,id',
            'item_description'     => 'required|string|max:500',
            'metal_type'           => 'required|in:Gold,Silver,Platinum',
            'gross_weight'         => 'required|numeric|min:0.001',
            'stone_weight'         => 'nullable|numeric|min:0',
            'purity_reported'      => 'required|numeric|min:0|max:100',
            'purity_tested'        => 'required|numeric|min:0|max:100',
            'melting_loss_expected'=> 'nullable|numeric|min:0',
            'rate_applied'         => 'required|numeric|min:0',
            'total_value'          => 'required|numeric|min:0',
            'exchange_type'        => 'required|in:cash,exchange,account_credit',
            'internal_notes'       => 'nullable|string',
        ]);

        $validated['created_by'] = Auth::id();
        $validated['status']     = 'pending';
        $validated['net_weight'] = $validated['gross_weight'] - ($validated['stone_weight'] ?? 0);
        $validated['net_fine_weight'] = ($validated['net_weight'] * $validated['purity_tested']) / 100;
        $validated['buyback_number'] = 'BB-' . date('Ymd') . '-' . str_pad(Buyback::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT);

        $buyback = Buyback::create($validated);
        return redirect()->route('buyback.show', $buyback)->with('success', 'Buyback created successfully.');
    }

    public function show(Buyback $buyback)
    {
        $buyback->load(['customer', 'branch', 'creator']);
        return view('buyback.show', compact('buyback'));
    }

    public function approve(Buyback $buyback)
    {
        $buyback->update(['status' => 'approved']);
        return redirect()->back()->with('success', 'Buyback approved.');
    }

    public function complete(Request $request, Buyback $buyback)
    {
        $request->validate(['payment_reference' => 'nullable|string|max:100']);
        $buyback->update(['status' => 'completed']);

        // Update customer balance if account_credit
        if ($buyback->exchange_type === 'account_credit' && $buyback->customer) {
            $buyback->customer->decrement('current_balance', $buyback->total_value);
        }

        return redirect()->back()->with('success', 'Buyback completed and payment processed.');
    }
}

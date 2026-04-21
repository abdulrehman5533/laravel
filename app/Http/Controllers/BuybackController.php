<?php

namespace App\Http\Controllers;

use App\Models\Buyback;
use App\Models\Customer;
use App\Services\BuybackService;
use Illuminate\Http\Request;

class BuybackController extends Controller
{
    protected $buybackService;

    public function __construct(BuybackService $buybackService)
    {
        $this->buybackService = $buybackService;
    }

    public function index()
    {
        $buybacks = Buyback::with(['customer', 'branch'])->latest()->paginate(20);

        return view('buyback.index', compact('buybacks'));
    }

    public function create()
    {
        $customers = Customer::all();

        return view('buyback.create', compact('customers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'branch_id' => 'required|exists:branches,id',
            'item_description' => 'required|string',
            'metal_type' => 'required|in:Gold,Silver,Platinum',
            'gross_weight' => 'required|numeric|min:0.001',
            'stone_weight' => 'nullable|numeric|min:0',
            'purity_reported' => 'required|numeric|min:0|max:100',
            'purity_tested' => 'required|numeric|min:0|max:100',
            'melting_loss_expected' => 'nullable|numeric|min:0',
            'rate_applied' => 'required|numeric|min:0',
            'total_value' => 'required|numeric|min:0',
            'exchange_type' => 'required|in:cash,exchange,account_credit',
            'internal_notes' => 'nullable|string',
        ]);

        try {
            $buyback = $this->buybackService->processBuyback($validated);

            return redirect()->route('buyback.show', $buyback->id)
                ->with('success', 'Buyback processed successfully.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Error processing buyback: '.$e->getMessage());
        }
    }

    public function show(Buyback $buyback)
    {
        $buyback->load(['customer', 'branch', 'creator']);

        return view('buyback.show', compact('buyback'));
    }
}

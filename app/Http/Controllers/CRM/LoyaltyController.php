<?php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\LoyaltyTier;
use App\Models\LoyaltyPointsLedger;
use App\Services\LoyaltyService;
use Illuminate\Http\Request;

class LoyaltyController extends Controller
{
    protected $loyaltyService;

    public function __construct(LoyaltyService $loyaltyService)
    {
        $this->loyaltyService = $loyaltyService;
    }

    public function index()
    {
        $tiers = LoyaltyTier::all();
        $topCustomers = Customer::with('loyaltyTier')
            ->where('total_loyalty_points', '>', 0)
            ->orderBy('total_loyalty_points', 'desc')
            ->take(20)
            ->get();
        
        $ledgers = LoyaltyPointsLedger::with('customer')->latest()->take(50)->get();

        return view('crm.loyalty.index', compact('tiers', 'topCustomers', 'ledgers'));
    }

    public function updatePoints(Request $request, $customerId)
    {
        $request->validate([
            'points' => 'required|integer',
            'type' => 'required|in:award,redeem',
            'description' => 'nullable|string',
        ]);

        $customer = Customer::findOrFail($customerId);

        if ($request->type === 'award') {
            $this->loyaltyService->awardPoints($customer, $request->points * 100, 'Manual');
        } else {
            $success = $this->loyaltyService->redeemPoints($customer, $request->points, 'Manual');
            if (!$success) {
                return back()->with('error', 'Insufficient points.');
            }
        }

        return redirect()->back()->with('success', 'Loyalty points updated successfully.');
    }
}

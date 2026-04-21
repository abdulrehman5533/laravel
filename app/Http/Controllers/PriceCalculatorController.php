<?php

namespace App\Http\Controllers;

use App\Models\GoldRate;
use Illuminate\Http\Request;

class PriceCalculatorController extends Controller
{
    public function index()
    {
        $goldRate = GoldRate::getTodayRate();

        return view('calculator.index', compact('goldRate'));
    }

    public function calculate(Request $request)
    {
        $request->validate([
            'gold_rate' => 'required|numeric|min:0',
            'weight' => 'required|numeric|min:0.001',
            'purity' => 'required|numeric|min:0|max:100',
            'making_charge' => 'required|numeric|min:0',
            'stone_cost' => 'required|numeric|min:0',
            'profit_margin' => 'required|numeric|min:0|max:100',
        ]);

        $goldValue = ($request->weight * $request->purity * $request->gold_rate) / 100;
        $totalCost = $goldValue + $request->making_charge + $request->stone_cost;
        $profit = $totalCost * ($request->profit_margin / 100);
        $sellingPrice = $totalCost + $profit;

        return response()->json([
            'gold_value' => round($goldValue, 2),
            'total_cost' => round($totalCost, 2),
            'profit' => round($profit, 2),
            'selling_price' => round($sellingPrice, 2),
            'breakdown' => [
                'Gold' => round($goldValue, 2),
                'Making Charge' => round($request->making_charge, 2),
                'Stone Cost' => round($request->stone_cost, 2),
                'Profit Margin' => $request->profit_margin.'%',
            ],
        ]);
    }
}

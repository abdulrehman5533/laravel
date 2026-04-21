<?php

namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Promotion;
use App\Services\PromotionService;
use Illuminate\Http\Request;

class PromotionController extends Controller
{
    protected $promotionService;

    public function __construct(PromotionService $promotionService)
    {
        $this->promotionService = $promotionService;
    }

    public function index()
    {
        $promotions = Promotion::withCount('coupons')->latest()->paginate(15);
        return view('marketing.promotions.index', compact('promotions'));
    }

    public function create()
    {
        return view('marketing.promotions.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:percentage,fixed',
            'value' => 'required|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'is_active' => 'boolean',
            'rules' => 'nullable|array',
        ]);

        $data = $request->all();
        $data['is_active'] = $request->has('is_active');

        Promotion::create($data);

        return redirect()->route('marketing.promotions.index')->with('success', 'Promotion created successfully.');
    }

    public function edit(Promotion $promotion)
    {
        return view('marketing.promotions.edit', compact('promotion'));
    }

    public function update(Request $request, Promotion $promotion)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:percentage,fixed',
            'value' => 'required|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'is_active' => 'boolean',
            'rules' => 'nullable|array',
        ]);

        $data = $request->all();
        $data['is_active'] = $request->has('is_active');

        $promotion->update($data);

        return redirect()->route('marketing.promotions.index')->with('success', 'Promotion updated successfully.');
    }

    public function destroy(Promotion $promotion)
    {
        $promotion->delete();
        return redirect()->route('marketing.promotions.index')->with('success', 'Promotion deleted successfully.');
    }

    public function applyCoupon(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
            'total_amount' => 'required|numeric|min:0'
        ]);

        $result = $this->promotionService->validateCoupon($request->code, $request->total_amount);

        if (!$result['valid']) {
            return response()->json(['error' => $result['message']], 422);
        }

        return response()->json([
            'message' => 'Coupon applied successfully.',
            'coupon' => $result['coupon'],
            'discount_amount' => $result['discount_amount'],
            'discount_type' => $result['coupon']->promotion->type,
            'discount_value' => $result['coupon']->promotion->value,
        ]);
    }
}

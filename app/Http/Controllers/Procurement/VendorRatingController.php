<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use App\Models\VendorRating;
use App\Models\PurchaseOrder;
use App\Services\VendorRatingService;
use Illuminate\Http\Request;

class VendorRatingController extends Controller
{
    protected $ratingService;

    public function __construct(VendorRatingService $ratingService)
    {
        $this->ratingService = $ratingService;
    }

    public function index()
    {
        $ratings = VendorRating::with(['supplier', 'purchaseOrder', 'ratedBy'])->latest()->paginate(15);
        $suppliers = Supplier::all();

        return view('procurement.vendors.ratings', compact('ratings', 'suppliers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'purchase_order_id' => 'nullable|exists:purchase_orders,id',
            'rating' => 'required|integer|min:1|max:5',
            'quality_rating' => 'nullable|integer|min:1|max:5',
            'delivery_rating' => 'nullable|integer|min:1|max:5',
            'price_rating' => 'nullable|integer|min:1|max:5',
            'review' => 'nullable|string',
        ]);

        if ($request->purchase_order_id) {
            $po = PurchaseOrder::findOrFail($request->purchase_order_id);
            $this->ratingService->rateFromPurchaseOrder($po, $request->all());
        } else {
            VendorRating::create(array_merge($request->all(), ['rated_by' => auth()->id()]));
            $this->ratingService->updateSupplierOverallRating($request->supplier_id);
        }

        return redirect()->back()->with('success', 'Vendor rating submitted successfully.');
    }

    public function supplierPerformance($supplierId)
    {
        $performance = $this->ratingService->getVendorPerformance($supplierId);
        return response()->json($performance);
    }
}

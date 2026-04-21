<?php

namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use App\Models\PosPricingTier;
use App\Models\PosSale;
use App\Services\POS\POSService;
use Illuminate\View\View;

class POSController extends Controller
{
    public function __construct(private POSService $posService) {}

    /**
     * Main POS dashboard - quick access
     */
    public function index(): View
    {
        $stats = [
            'today_sales' => PosSale::whereDate('sale_time', today())
                ->where('status', 'completed')
                ->sum('total'),
            'pending_holds' => PosSale::where('status', 'held')->count(),
            'today_transactions' => PosSale::whereDate('sale_time', today())->count(),
        ];

        $pricingTiers = PosPricingTier::all();

        $recentSales = PosSale::with(['customer'])
            ->whereIn('status', ['open', 'held'])
            ->latest('updated_at')
            ->limit(10)
            ->get();

        return view('pos.index', compact('stats', 'pricingTiers', 'recentSales'));
    }

    /**
     * Lookup product by SKU or query for POS quick-add
     */
    public function lookup(\Illuminate\Http\Request $request)
    {
        $q = $request->get('q');

        if (! $q) {
            return response()->json([], 200);
        }

        // If looks like an SKU/barcode (no spaces and not too short), try barcode scan
        if (strlen($q) >= 3 && preg_match('/^[A-Za-z0-9\-]+$/', $q)) {
            $found = $this->posService->scanBarcode($q);
            if ($found) {
                return response()->json([$found]);
            }
        }

        // Fallback: fuzzy name search
        $results = \App\Models\InventoryProduct::active()
            ->where(function ($query) use ($q) {
                $query->where('name', 'like', '%'.$q.'%')
                    ->orWhere('sku', 'like', '%'.$q.'%')
                    ->orWhere('barcode', 'like', '%'.$q.'%');
            })
            ->limit(10)
            ->get()
            ->map(function ($p) {
                return [
                    'id' => $p->id,
                    'sku' => $p->sku,
                    'description' => $p->name,
                    'unit_price' => (float) $p->selling_price,
                    'unit' => $p->unit ?? 'pcs',
                    'stock' => (float) $p->current_stock,
                    'weight' => (float) ($p->weight ?? 0),
                    'category_id' => $p->category_id,
                    'purity_id' => $p->purity_id,
                    'purity' => $p->purity?->name,
                    'making_charge' => (float) $p->making_charge_value,
                    'wastage' => (float) $p->wastage_percentage,
                ];
            });

        return response()->json($results);
    }
}

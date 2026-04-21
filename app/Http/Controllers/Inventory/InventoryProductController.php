<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\InventoryProduct;
use App\Models\ProductCategory;
use App\Models\PurityLevel;
use App\Models\StockLocation;
use App\Models\StockMovement;
use App\Services\Inventory\InventoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

// use SimpleSoftwareIO\QrCode\Facades\QrCode;
// use Picqer\Barcode\BarcodeGeneratorPNG;

class InventoryProductController extends Controller
{
    public function __construct(private InventoryService $inventoryService) {}

    public function index(Request $request): View
    {
        $query = InventoryProduct::query();

        if ($request->search) {
            $query->searchByName($request->search);
        }

        if ($request->category) {
            $query->byCategory($request->category);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->stock_status) {
            if ($request->stock_status === 'low') {
                $query->lowStock();
            } elseif ($request->stock_status === 'out') {
                $query->where('current_stock', '<=', 0);
            } elseif ($request->stock_status === 'high') {
                $query->whereColumn('current_stock', '>', 'reorder_level');
            }
        }

        $products = $query->with(['category', 'purity', 'branch'])
            ->paginate(20);

        $categories = ProductCategory::active()->get();
        $metrics = $this->inventoryService->getInventoryMetrics();

        return view('inventory.products.index', [
            'products' => $products,
            'categories' => $categories,
            'metrics' => $metrics,
        ]);
    }

    public function create(): View
    {
        $tenant = app()->has('current_tenant') ? app('current_tenant') : null;
        if ($tenant && $tenant->reachedLimit('products')) {
            return redirect()->route('inventory.products.index')
                ->with('error', 'Product limit reached for your current plan. Please upgrade to add more products.');
        }

        $categories = ProductCategory::active()->get();
        $purities = PurityLevel::active()->get();
        $branches = \App\Models\Branch::active()->get();
        $latestGoldRate = \App\Models\GoldRate::latest('date')->first();

        return view('inventory.products.create', [
            'categories' => $categories,
            'purities' => $purities,
            'branches' => $branches,
            'goldRate' => $latestGoldRate,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $tenant = app()->has('current_tenant') ? app('current_tenant') : null;
        if ($tenant && $tenant->reachedLimit('products')) {
            return redirect()->route('inventory.products.index')
                ->with('error', 'Product limit reached for your current plan. Please upgrade to add more products.');
        }

        $validated = $request->validate([
            'sku' => 'nullable|unique:inventory_products',
            'tag_id' => 'nullable|unique:inventory_products',
            'batch_no' => 'nullable|string|max:100',
            'stamp' => 'nullable|string|max:100',
            'serial_number' => 'nullable|unique:inventory_products',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:product_categories,id',
            'collection' => 'nullable|string|max:255',
            'gender' => 'nullable|in:Men,Women,Unisex,Kids',
            'purity_id' => 'nullable|exists:purity_levels,id',
            'metal_color' => 'nullable|string',
            'weight' => 'required|numeric|min:0',
            'gross_weight' => 'nullable|numeric|min:0',
            'net_weight' => 'nullable|numeric|min:0',
            'wastage_percentage' => 'nullable|numeric|min:0',
            'stone_count' => 'nullable|integer|min:0',
            'stone_carat' => 'nullable|numeric|min:0',
            'stone_type' => 'nullable|string',
            'length' => 'nullable|numeric|min:0',
            'width' => 'nullable|numeric|min:0',
            'height' => 'nullable|numeric|min:0',
            'size' => 'nullable|string',
            'cost_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'making_charge_type' => 'nullable|in:fixed,per_gram,percentage',
            'making_charge_value' => 'nullable|numeric|min:0',
            'labor_charge' => 'nullable|numeric|min:0',
            'hallmark' => 'nullable|string',
            'is_hallmarked' => 'nullable|boolean',
            'certificate_no' => 'nullable|string',
            'current_stock' => 'required|numeric|min:0',
            'current_pieces' => 'required|integer|min:0',
            'reorder_level' => 'required|numeric|min:0',
            'reorder_quantity' => 'required|numeric|min:0',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        // Auto-generate SKU if not provided
        if (empty($validated['sku'])) {
            $validated['sku'] = $this->generateUniqueSKU();
        }

        // Auto-generate Serial Number if not provided
        if (empty($validated['serial_number'])) {
            $validated['serial_number'] = $this->generateUniqueSerialNumber();
        }

        $validated['is_hallmarked'] = $request->has('is_hallmarked');
        $validated['branch_id'] = Auth::user()->branch_id ?? 1;
        $validated['created_by'] = Auth::id();

        /*
        // Generate Barcode - Disabled due to missing package
        $generator = new BarcodeGeneratorPNG();
        $barcode = $generator->getBarcode($validated['sku'], $generator::TYPE_CODE_128);
        $barcodeFile = 'barcodes/' . $validated['sku'] . '.png';
        \Storage::disk('public')->put($barcodeFile, $barcode);
        $validated['barcode'] = $barcodeFile;

        // Generate QR Code with both SKU and Serial Number
        $qrData = json_encode([
            'sku' => $validated['sku'],
            'serial_number' => $validated['serial_number'],
            'name' => $validated['name']
        ]);
        $qrCode = QrCode::format('png')->size(200)->generate($qrData);
        $validated['qr_code'] = base64_encode($qrCode);
        */

        // Handle Images
        $productImages = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $image) {
                $path = $image->store('products', 'public');
                $productImages[] = [
                    'image_path' => $path,
                    'is_primary' => $index === 0,
                    'sort_order' => $index,
                ];

                // Set first image as main image_path for backward compatibility
                if ($index === 0) {
                    $validated['image_path'] = $path;
                }
            }
        }

        // Set default weights if not provided
        $validated['gross_weight'] = $validated['gross_weight'] ?? $validated['weight'];
        $validated['net_weight'] = $validated['net_weight'] ?? $validated['weight'];

        $product = new InventoryProduct($validated);
        $product->calculateFineWeight();
        $product->save();

        // Save multiple images
        if (! empty($productImages)) {
            foreach ($productImages as $imageData) {
                $product->images()->create($imageData);
            }
        }

        return redirect()->route('inventory.products.show', $product)
            ->with('success', 'Product created successfully with SKU: '.$validated['sku'].' and Serial: '.$validated['serial_number']);
    }

    /**
     * Generate a unique SKU
     */
    private function generateUniqueSKU(): string
    {
        do {
            // Format: PRD-YYYYMMDD-XXXX (e.g., PRD-20251226-A5F3)
            $sku = 'PRD-'.date('Ymd').'-'.strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 4));
        } while (InventoryProduct::where('sku', $sku)->exists());

        return $sku;
    }

    /**
     * Generate a unique Serial Number
     */
    private function generateUniqueSerialNumber(): string
    {
        do {
            // Format: SN-YYYYMMDD-XXXXXXXX (e.g., SN-20251226-3F8A9B2C)
            $serialNumber = 'SN-'.date('Ymd').'-'.strtoupper(substr(str_shuffle('0123456789ABCDEF'), 0, 8));
        } while (InventoryProduct::where('serial_number', $serialNumber)->exists());

        return $serialNumber;
    }

    public function show(InventoryProduct $product): View
    {
        $product->load(['category', 'purity', 'branch', 'stoneAttributes', 'stockMovements', 'wastageRecords', 'damageRecords']);

        $stockMovements = $product->stockMovements()->latest()->paginate(10);
        $wastageRecords = $product->wastageRecords()->latest()->take(5)->get();
        $damageRecords = $product->damageRecords()->latest()->take(5)->get();

        $suppliers = \App\Models\Supplier::where('status', 'active')->get();

        $wastageTotal = $product->wastageRecords()->sum('quantity');
        $damageTotal = $product->damageRecords()->sum('quantity');

        $reorderInfo = $this->inventoryService->checkReorderThreshold($product->id);

        $movementStats = [
            'total_sold' => $product->stockMovements()->where('type', 'subtract')->where('reference', 'like', 'sale:%')->sum('quantity'),
            'total_added' => $product->stockMovements()->where('type', 'add')->sum('quantity'),
            'total_wastage' => $wastageTotal,
            'total_damage' => $damageTotal,
        ];

        $financialMetrics = [
            'total_cost' => $product->current_stock * $product->cost_price,
            'total_retail_value' => $product->current_stock * $product->selling_price,
            'profit_potential' => $product->current_stock * ($product->selling_price - $product->cost_price),
            'profit_margin' => $product->profit_margin_percent,
        ];

        $latestGoldRate = \App\Models\GoldRate::latest('date')->first();

        return view('inventory.products.show', [
            'product' => $product,
            'stockMovements' => $stockMovements,
            'wastageRecords' => $wastageRecords,
            'damageRecords' => $damageRecords,
            'wastageTotal' => $wastageTotal,
            'damageTotal' => $damageTotal,
            'reorderInfo' => $reorderInfo,
            'movementStats' => $movementStats,
            'financialMetrics' => $financialMetrics,
            'goldRate' => $latestGoldRate,
            'suppliers' => $suppliers,
        ]);
    }

    public function edit(InventoryProduct $product): View
    {
        $categories = ProductCategory::active()->get();
        $purities = PurityLevel::active()->get();
        $branches = \App\Models\Branch::active()->get();
        $latestGoldRate = \App\Models\GoldRate::latest('date')->first();

        return view('inventory.products.edit', [
            'product' => $product,
            'categories' => $categories,
            'purities' => $purities,
            'branches' => $branches,
            'goldRate' => $latestGoldRate,
        ]);
    }

    public function update(Request $request, InventoryProduct $product): RedirectResponse
    {
        $validated = $request->validate([
            'tag_id' => 'nullable|unique:inventory_products,tag_id,'.$product->id,
            'batch_no' => 'nullable|string|max:100',
            'stamp' => 'nullable|string|max:100',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:product_categories,id',
            'collection' => 'nullable|string|max:255',
            'gender' => 'nullable|in:Men,Women,Unisex,Kids',
            'purity_id' => 'nullable|exists:purity_levels,id',
            'metal_color' => 'nullable|string',
            'weight' => 'required|numeric|min:0',
            'gross_weight' => 'nullable|numeric|min:0',
            'net_weight' => 'nullable|numeric|min:0',
            'wastage_percentage' => 'nullable|numeric|min:0',
            'stone_count' => 'nullable|integer|min:0',
            'stone_carat' => 'nullable|numeric|min:0',
            'stone_type' => 'nullable|string',
            'length' => 'nullable|numeric|min:0',
            'width' => 'nullable|numeric|min:0',
            'height' => 'nullable|numeric|min:0',
            'size' => 'nullable|string',
            'cost_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'making_charge_type' => 'nullable|in:fixed,per_gram,percentage',
            'making_charge_value' => 'nullable|numeric|min:0',
            'labor_charge' => 'nullable|numeric|min:0',
            'hallmark' => 'nullable|string',
            'is_hallmarked' => 'nullable|boolean',
            'certificate_no' => 'nullable|string',
            'reorder_level' => 'required|numeric|min:0',
            'reorder_quantity' => 'required|numeric|min:0',
            'status' => 'required|in:active,inactive,discontinued',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        $validated['is_hallmarked'] = $request->has('is_hallmarked');
        $validated['updated_by'] = Auth::id();

        // Handle Images
        if ($request->hasFile('images')) {
            $hasPrimary = $product->images()->where('is_primary', true)->exists();
            $maxSort = $product->images()->max('sort_order') ?? -1;

            foreach ($request->file('images') as $index => $image) {
                $path = $image->store('products', 'public');

                $isPrimary = ! $hasPrimary && $index === 0;

                $product->images()->create([
                    'image_path' => $path,
                    'is_primary' => $isPrimary,
                    'sort_order' => $maxSort + $index + 1,
                ]);

                // If this is the new primary image or the first ever image, update main path
                if ($isPrimary || ! $product->image_path) {
                    $product->image_path = $path;
                }
            }
        }

        $product->fill($validated);
        $product->calculateFineWeight();
        $product->save();

        return redirect()->route('inventory.products.show', $product)
            ->with('success', 'Product updated successfully!');
    }

    public function addStock(Request $request, InventoryProduct $product): RedirectResponse
    {
        $validated = $request->validate([
            'quantity' => 'required|numeric|min:0.001',
            'pieces' => 'nullable|integer|min:0',
            'weight' => 'nullable|numeric|min:0',
            'type' => 'required|in:add,subtract',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'notes' => 'nullable|string|max:500',
            'reference' => 'nullable|string|max:100',
        ]);

        try {
            // Use quantity as weight if weight not explicitly provided
            $weight = $validated['weight'] ?? $validated['quantity'];
            $pieces = $validated['pieces'] ?? 0;

            $this->inventoryService->updateStock(
                $product->id,
                $validated['quantity'],
                $validated['type'],
                $validated['reference'] ?? 'adjustment',
                $validated['notes'] ?? 'Manual stock '.($validated['type'] === 'add' ? 'addition' : 'subtraction'),
                $weight,
                $pieces
            );

            $message = $validated['type'] === 'add' ? 'Stock replenished successfully!' : 'Stock adjusted successfully!';

            return redirect()->route('inventory.products.show', $product)
                ->with('success', $message.' New balance: '.($product->fresh()->current_stock));
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to update stock: '.$e->getMessage());
        }
    }

    public function recordWastage(Request $request, InventoryProduct $product): RedirectResponse
    {
        $validated = $request->validate([
            'quantity' => 'required|numeric|min:0.01|max:'.$product->current_stock,
            'reason' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        $product->recordWastage(
            $validated['quantity'],
            $validated['reason'],
            $validated['notes'] ?? null
        );

        return redirect()->route('inventory.products.show', $product)
            ->with('success', 'Wastage recorded successfully!');
    }

    public function recordDamage(Request $request, InventoryProduct $product): RedirectResponse
    {
        $validated = $request->validate([
            'quantity' => 'required|numeric|min:0.01',
            'status' => 'required|in:damaged,repairing,repaired,discarded',
            'notes' => 'nullable|string',
        ]);

        $product->recordDamage(
            $validated['quantity'],
            $validated['status'],
            $validated['notes'] ?? null
        );

        return redirect()->route('inventory.products.show', $product)
            ->with('success', 'Damage record added successfully!');
    }

    public function transferStock(Request $request, InventoryProduct $product): RedirectResponse
    {
        $validated = $request->validate([
            'quantity' => 'required|numeric|min:0.01|max:'.$product->current_stock,
            'from_location_id' => 'required|exists:stock_locations,id',
            'to_location_id' => 'required|exists:stock_locations,id|different:from_location_id',
            'notes' => 'nullable|string',
        ]);

        StockMovement::create([
            'product_id' => $product->id,
            'location_id' => $validated['from_location_id'],
            'quantity' => $validated['quantity'],
            'type' => 'transfer',
            'reference' => $validated['to_location_id'],
            'notes' => $validated['notes'] ?? null,
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('inventory.products.show', $product)
            ->with('success', 'Stock transferred successfully!');
    }

    public function lowStockAlert(): View
    {
        $alert = $this->inventoryService->getLowStockAlert();
        $products = $alert['products'];

        $products->each(function ($product) {
            $product->suggested_order = $product->reorder_quantity;
            $avgSubtract = $product->stockMovements()->where('type', 'subtract')->avg('quantity') ?: 1;
            $product->days_until_stockout = $product->current_stock > 0 ?
                ceil($product->current_stock / $avgSubtract) : 0;
        });

        return view('inventory.products.low-stock-alert', [
            'products' => $products,
            'alert' => $alert,
        ]);
    }

    public function stockAgingReport(): View
    {
        $products = $this->inventoryService->getStockAgingReport();
        $ageGroups = $this->inventoryService->getAgeGroupSummary();

        return view('inventory.reports.stock-aging', [
            'products' => $products,
            'ageGroups' => $ageGroups,
        ]);
    }

    public function wastageReport(Request $request): View
    {
        $fromDate = $request->from_date ? \Carbon\Carbon::parse($request->from_date) : now()->subDays(30);
        $toDate = $request->to_date ? \Carbon\Carbon::parse($request->to_date) : now();

        $wastageData = $this->inventoryService->getWastageReport($fromDate, $toDate);

        // Manual pagination for wastage records
        $perPage = 15;
        $page = $request->get('page', 1);
        $wastage = new \Illuminate\Pagination\LengthAwarePaginator(
            $wastageData['records']->forPage($page, $perPage),
            $wastageData['records']->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('inventory.reports.wastage', [
            'wastage' => $wastage,
            'totalWastage' => $wastageData['total_quantity'],
            'totalCost' => $wastageData['total_cost'],
            'byReason' => $wastageData['by_reason'],
            'from_date' => $fromDate->format('Y-m-d'),
            'to_date' => $toDate->format('Y-m-d'),
        ]);
    }

    public function multiLocationStock(): View
    {
        $inventory = $this->inventoryService->getMultiLocationInventory();
        $locations = StockLocation::active()->get();

        // Calculate location stats
        $locationStats = [];
        foreach ($locations as $location) {
            $count = 0;
            foreach ($inventory as $item) {
                if (isset($item['locations'][$location->id]) && $item['locations'][$location->id]['quantity'] > 0) {
                    $count++;
                }
            }
            $locationStats[$location->id] = $count;
        }

        return view('inventory.reports.multi-location-stock', [
            'inventory' => $inventory,
            'locations' => $locations,
            'locationStats' => $locationStats,
        ]);
    }

    public function downloadBarcode(InventoryProduct $product)
    {
        // If a physical file exists in the storage, download it
        if ($product->barcode && file_exists(storage_path('app/public/'.$product->barcode))) {
            return response()->download(storage_path('app/public/'.$product->barcode));
        }

        // Professional Fallback: Generate barcode on-the-fly using a reliable API
        // This ensures the system is "Market Ready" and never shows "Barcode not available"
        $sku = $product->sku;
        $barcodeUrl = 'https://bwipjs-api.metafloor.com/?bcid=code128&text='.urlencode($sku).'&scale=3&rotate=N&includetext';

        try {
            $content = file_get_contents($barcodeUrl);
            if ($content) {
                return response($content)
                    ->header('Content-Type', 'image/png')
                    ->header('Content-Disposition', 'attachment; filename="barcode-'.$sku.'.png"');
            }
        } catch (\Exception $e) {
            // If API fails, redirect to a clean web view that uses JsBarcode for client-side printing
            return view('inventory.tags.barcode-only', compact('product'));
        }

        return redirect()->back()->with('error', 'Unable to generate barcode at this moment.');
    }

    public function downloadQrCode(InventoryProduct $product)
    {
        if (! $product->qr_code) {
            return redirect()->back()->with('error', 'QR Code not available');
        }

        $qrCodeBinary = base64_decode($product->qr_code);

        return response($qrCodeBinary)
            ->header('Content-Type', 'image/png')
            ->header('Content-Disposition', 'attachment; filename="'.$product->sku.'.png"');
    }
}

<?php

namespace App\Http\Controllers\Calculator;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\InventoryProduct;
use App\Models\ProductCategory;
use App\Models\PurityLevel;
use App\Models\WeightCalculation;
use App\Services\WeightCalculatorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WeightCalculatorController extends Controller
{
    private WeightCalculatorService $calculator;

    public function __construct(WeightCalculatorService $calculator)
    {
        $this->calculator = $calculator;
    }

    /**
     * Show calculator page
     */
    public function index(): View
    {
        return view('calculator.index', [
            'karats' => [24, 22, 21, 20, 18, 14, 10],
            'units' => $this->calculator->getSupportedUnits(),
            'rattiTypes' => ['sunari' => 'Sunari Ratti', 'pakki' => 'Pakki Ratti'],
            'categories' => ProductCategory::active()->get(),
            'branches' => Branch::active()->get(),
            'purities' => PurityLevel::active()->get(),
        ]);
    }

    /**
     * Convert weight between units - API endpoint
     */
    public function convert(Request $request): JsonResponse
    {
        try {
            $value = $request->input('value');
            $fromUnit = $request->input('from_unit', 'g');
            $toUnit = $request->input('to_unit', 'ratti');
            $rattiType = $request->input('ratti_type', 'sunari');

            $result = $this->calculator->toGrams($value, $fromUnit, $rattiType);
            $converted = $this->calculator->fromGrams($result, $toUnit, $rattiType);

            return response()->json([
                'success' => true,
                'value' => $value,
                'from_unit' => $fromUnit,
                'to_unit' => $toUnit,
                'result' => $converted,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * Detect unit from user input
     */
    public function detectUnit(Request $request): JsonResponse
    {
        try {
            $input = $request->input('input');
            $unit = $this->calculator->detectUnit($input);

            return response()->json([
                'success' => true,
                'input' => $input,
                'detected_unit' => $unit,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * Calculate price breakdown
     */
    public function calculate(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'input_weight' => 'required|numeric|min:0.001',
                'input_unit' => 'required|string',
                'karat' => 'required|in:24,22,21,20,18,14,10',
                'rate_per_gram' => 'required|numeric|min:0',
                'wastage_type' => 'required|in:percentage,fixed',
                'wastage_value' => 'required|numeric|min:0',
                'making_charge_type' => 'required|in:fixed,per_gram,percentage',
                'making_charge_value' => 'required|numeric|min:0',
                'stone_weight' => 'nullable|numeric|min:0',
                'stone_unit' => 'nullable|string',
                'stone_price_per_carat' => 'nullable|numeric|min:0',
                'tax_percentage' => 'nullable|numeric|min:0',
                'discount_percentage' => 'nullable|numeric|min:0',
                'ratti_type' => 'required|in:sunari,pakki',
                'custom_charges' => 'nullable|numeric|min:0',
            ]);

            $breakdown = $this->calculator->calculatePriceBreakdown(
                $validated['input_weight'],
                $validated['input_unit'],
                $validated['karat'],
                $validated['rate_per_gram'],
                $validated['wastage_type'],
                $validated['wastage_value'],
                $validated['making_charge_type'],
                $validated['making_charge_value'],
                $validated['stone_weight'] ?? 0,
                $validated['stone_unit'] ?? 'carat',
                $validated['stone_price_per_carat'] ?? 0,
                $validated['tax_percentage'] ?? 0,
                $validated['discount_percentage'] ?? 0,
                $validated['ratti_type'],
                $validated['custom_charges'] ?? 0
            );

            return response()->json([
                'success' => true,
                'calculation_details' => $breakdown,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * Save calculation to database
     */
    public function save(Request $request): JsonResponse
    {
        try {
            $calculation = WeightCalculation::create([
                'user_id' => auth()->id(),
                'branch_id' => $request->input('branch_id', 1),
                'input_weight' => $request->input('input_weight'),
                'input_unit' => $request->input('input_unit'),
                'karat_value' => $request->input('karat'),
                'rate_per_gram' => $request->input('rate_per_gram'),
                'wastage_type' => $request->input('wastage_type'),
                'wastage_value' => $request->input('wastage_value'),
                'making_charge_type' => $request->input('making_charge_type'),
                'making_charge_value' => $request->input('making_charge_value'),
                'stone_weight' => $request->input('stone_weight'),
                'stone_unit' => $request->input('stone_unit'),
                'stone_price_per_carat' => $request->input('stone_price_per_carat'),
                'tax_percentage' => $request->input('tax_percentage'),
                'discount_percentage' => $request->input('discount_percentage'),
                'ratti_type' => $request->input('ratti_type'),
                'custom_charges' => $request->input('custom_charges'),
                'calculation_details' => $request->input('calculation_details'),
                'final_price' => $request->input('final_price'),
                'is_saved_as_product' => false,
                'notes' => $request->input('notes', ''),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Calculation saved successfully',
                'id' => $calculation->id,
                'redirect' => route('calculator.view', $calculation->id),
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * Save calculation as inventory product
     */
    public function saveAsProduct(Request $request): JsonResponse
    {
        try {
            $calculation = WeightCalculation::create([
                'user_id' => auth()->id(),
                'branch_id' => $request->input('branch_id', 1),
                'input_weight' => $request->input('input_weight'),
                'input_unit' => $request->input('input_unit'),
                'karat_value' => $request->input('karat'),
                'rate_per_gram' => $request->input('rate_per_gram'),
                'wastage_type' => $request->input('wastage_type'),
                'wastage_value' => $request->input('wastage_value'),
                'making_charge_type' => $request->input('making_charge_type'),
                'making_charge_value' => $request->input('making_charge_value'),
                'stone_weight' => $request->input('stone_weight'),
                'stone_unit' => $request->input('stone_unit'),
                'stone_price_per_carat' => $request->input('stone_price_per_carat'),
                'tax_percentage' => $request->input('tax_percentage'),
                'discount_percentage' => $request->input('discount_percentage'),
                'ratti_type' => $request->input('ratti_type'),
                'custom_charges' => $request->input('custom_charges'),
                'calculation_details' => $request->input('calculation_details'),
                'final_price' => $request->input('final_price'),
                'notes' => $request->input('notes', ''),
            ]);

            // Create inventory product
            $product = InventoryProduct::create([
                'name' => $request->input('product_name', 'Calculation #'.$calculation->id),
                'sku' => 'CALC-'.$calculation->id.'-'.time(),
                'category_id' => $request->input('category_id'),
                'purity_id' => $request->input('purity_id'),
                'branch_id' => $request->input('branch_id', 1),
                'cost_price' => $request->input('final_price'),
                'selling_price' => $request->input('final_price'),
                'quantity' => 1,
                'weight_grams' => $request->input('input_weight'),
                'description' => 'Generated from weight calculator calculation #'.$calculation->id,
                'barcode' => 'CAL-'.time(),
                'is_active' => true,
                'created_by' => auth()->id(),
            ]);

            $calculation->update([
                'is_saved_as_product' => true,
                'product_id' => $product->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Saved as product successfully',
                'calculation_id' => $calculation->id,
                'product_id' => $product->id,
                'redirect' => route('inventory.products.show', $product->id),
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * Show calculation history
     */
    public function history(Request $request): View
    {
        $query = WeightCalculation::where('user_id', auth()->id());

        if ($request->filled('from_date') && $request->filled('to_date')) {
            $query->whereBetween('created_at', [
                $request->input('from_date').' 00:00:00',
                $request->input('to_date').' 23:59:59',
            ]);
        }

        $calculations = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('calculator.history', ['calculations' => $calculations]);
    }

    /**
     * View specific calculation
     */
    public function view($id): View
    {
        $calculation = WeightCalculation::findOrFail($id);

        return view('calculator.view', ['calculation' => $calculation]);
    }

    /**
     * Export calculation as PDF
     */
    public function exportPDF($id)
    {
        try {
            $calculation = WeightCalculation::findOrFail($id);

            $pdf = \PDF::loadView('calculator.print', ['calculation' => $calculation]);
            $filename = 'Calculation-'.$calculation->id.'-'.date('Y-m-d-His').'.pdf';

            return $pdf->download($filename);
        } catch (\Exception $e) {
            return back()->with('error', 'Error exporting PDF: '.$e->getMessage());
        }
    }

    /**
     * Delete calculation (soft delete)
     */
    public function delete($id): JsonResponse
    {
        try {
            $calculation = WeightCalculation::findOrFail($id);

            // Check authorization
            if ($calculation->user_id !== auth()->id()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            $calculation->delete();

            return response()->json([
                'message' => 'Calculation deleted successfully',
                'redirect' => route('calculator.history'),
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get quick presets
     */
    public function presets(): JsonResponse
    {
        return response()->json([
            'presets' => [
                [
                    'name' => '22K Gold + 2% Wastage',
                    'karat' => 22,
                    'wastage_type' => 'percentage',
                    'wastage_value' => 2,
                    'making_charge_type' => 'per_gram',
                    'making_charge_value' => 0,
                ],
                [
                    'name' => '18K Gold + No Wastage',
                    'karat' => 18,
                    'wastage_type' => 'percentage',
                    'wastage_value' => 0,
                    'making_charge_type' => 'per_gram',
                    'making_charge_value' => 0,
                ],
                [
                    'name' => '24K Gold + Making Charges',
                    'karat' => 24,
                    'wastage_type' => 'percentage',
                    'wastage_value' => 1,
                    'making_charge_type' => 'per_gram',
                    'making_charge_value' => 50,
                ],
                [
                    'name' => 'Silver + 5% Wastage',
                    'karat' => 22,
                    'wastage_type' => 'percentage',
                    'wastage_value' => 5,
                    'making_charge_type' => 'percentage',
                    'making_charge_value' => 15,
                ],
            ],
        ]);
    }
}

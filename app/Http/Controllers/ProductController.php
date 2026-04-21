<?php

namespace App\Http\Controllers;

use App\Models\GoldRate;
use App\Models\JewelleryProduct;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = JewelleryProduct::query();

        if ($request->has('search') && $request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%'.$request->search.'%')
                    ->orWhere('sku', 'like', '%'.$request->search.'%')
                    ->orWhere('description', 'like', '%'.$request->search.'%');
            });
        }

        if ($request->has('category') && $request->category) {
            $query->where('category', $request->category);
        }

        if ($request->has('material') && $request->material) {
            $query->where('material', $request->material);
        }

        $products = $query->latest()->paginate(20);

        return view('products.index', compact('products'));
    }

    public function create()
    {
        $goldRate = GoldRate::getTodayRate();

        return view('products.create', compact('goldRate'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string',
            'material' => 'required|string',
            'weight' => 'required|numeric|min:0.001',
            'purity' => 'required|numeric|min:0|max:100',
            'making_charge' => 'required|numeric|min:0',
            'stone_weight' => 'nullable|numeric|min:0',
            'stone_cost' => 'nullable|numeric|min:0',
            'cost_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'gold_rate' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'hallmark' => 'nullable|string',
            'certificate_no' => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        // Generate SKU
        $validated['sku'] = JewelleryProduct::generateSku($request->category, $request->material);

        // Handle images
        if ($request->hasFile('images')) {
            $imagePaths = [];
            foreach ($request->file('images') as $image) {
                $path = $image->store('products', 'public');
                $imagePaths[] = $path;
            }
            $validated['images'] = json_encode($imagePaths);
        }

        JewelleryProduct::create($validated);

        return redirect()->route('products.index')
            ->with('success', 'Product added successfully!');
    }

    public function show(JewelleryProduct $product)
    {
        return view('products.show', compact('product'));
    }

    public function edit(JewelleryProduct $product)
    {
        $goldRate = GoldRate::getTodayRate();

        return view('products.edit', compact('product', 'goldRate'));
    }

    public function update(Request $request, JewelleryProduct $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string',
            'material' => 'required|string',
            'weight' => 'required|numeric|min:0.001',
            'purity' => 'required|numeric|min:0|max:100',
            'making_charge' => 'required|numeric|min:0',
            'stone_weight' => 'nullable|numeric|min:0',
            'stone_cost' => 'nullable|numeric|min:0',
            'cost_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'gold_rate' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'hallmark' => 'nullable|string',
            'certificate_no' => 'nullable|string',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        // Set is_active to false if not checked
        $validated['is_active'] = $request->has('is_active');

        // Handle images update
        if ($request->hasFile('images')) {
            $imagePaths = [];
            foreach ($request->file('images') as $image) {
                $path = $image->store('products', 'public');
                $imagePaths[] = $path;
            }
            $validated['images'] = json_encode($imagePaths);
        }

        $product->update($validated);

        return redirect()->route('products.index')
            ->with('success', 'Product updated successfully!');
    }

    public function destroy(JewelleryProduct $product)
    {
        $product->delete();

        return redirect()->route('products.index')
            ->with('success', 'Product deleted successfully!');
    }

    public function updateStock(Request $request, $id)
    {
        $product = JewelleryProduct::findOrFail($id);

        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $product->increment('stock', $request->quantity);

        return response()->json(['success' => true]);
    }
}

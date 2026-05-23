<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\ProductCategory;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = ProductCategory::withCount('products')->orderBy('name')->get();
        return view('inventory.settings.categories', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:100|unique:product_categories,name',
            'code'        => 'nullable|string|max:20',
            'description' => 'nullable|string|max:255',
            'icon'        => 'nullable|string|max:50',
        ]);
        $data['is_active'] = true;
        ProductCategory::create($data);
        return redirect()->back()->with('success', 'Category created.');
    }

    public function update(Request $request, ProductCategory $category)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:100|unique:product_categories,name,'.$category->id,
            'code'        => 'nullable|string|max:20',
            'description' => 'nullable|string|max:255',
            'is_active'   => 'boolean',
        ]);
        $category->update($data);
        return redirect()->back()->with('success', 'Category updated.');
    }

    public function destroy(ProductCategory $category)
    {
        if ($category->products()->count() > 0) {
            return redirect()->back()->with('error', 'Cannot delete — category has products.');
        }
        $category->delete();
        return redirect()->back()->with('success', 'Category deleted.');
    }
}

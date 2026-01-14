<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Http\Request;

class AdminProductController extends Controller
{
    /**
     * Display all products
     */
    public function index(Request $request)
    {
        $query = Product::with('productCategory')
            ->withCount('listings');

        // Filter by category if provided
        if ($request->filled('category')) {
            $query->where('product_category_id', $request->category);
        }

        // Search by name
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $products = $query->orderBy('product_category_id')
            ->orderBy('name')
            ->paginate(50);

        $categories = ProductCategory::orderBy('name')->get();

        return view('admin.products.index', compact('products', 'categories'));
    }

    /**
     * Show form to create a new product
     */
    public function create()
    {
        $categories = ProductCategory::orderBy('name')->get();
        return view('admin.products.create', compact('categories'));
    }

    /**
     * Store a new product
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_category_id' => 'required|exists:product_categories,id',
            'name' => 'required|string|max:50',
        ]);

        // Check for duplicate within category
        $exists = Product::where('product_category_id', $validated['product_category_id'])
            ->where('name', $validated['name'])
            ->exists();

        if ($exists) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['name' => 'A product with this name already exists in this category.']);
        }

        Product::create($validated);

        return redirect()->route('admin.products.index')
            ->with('success', 'Product created successfully.');
    }

    /**
     * Show form to edit a product
     */
    public function edit(Product $product)
    {
        $categories = ProductCategory::orderBy('name')->get();
        $listingsCount = $product->listings()->count();

        return view('admin.products.edit', compact('product', 'categories', 'listingsCount'));
    }

    /**
     * Update a product
     */
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'product_category_id' => 'required|exists:product_categories,id',
            'name' => 'required|string|max:50',
        ]);

        // Check for duplicate within category (excluding current product)
        $exists = Product::where('product_category_id', $validated['product_category_id'])
            ->where('name', $validated['name'])
            ->where('id', '!=', $product->id)
            ->exists();

        if ($exists) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['name' => 'A product with this name already exists in this category.']);
        }

        $product->update($validated);

        return redirect()->route('admin.products.index')
            ->with('success', 'Product updated successfully.');
    }

    /**
     * Delete a product
     */
    public function destroy(Product $product)
    {
        // Check if product has listings
        $listingsCount = $product->listings()->count();

        if ($listingsCount > 0) {
            return redirect()->back()
                ->withErrors(['error' => "Cannot delete product. It has {$listingsCount} active listing(s)."]);
        }

        $productName = $product->name;
        $product->delete();

        return redirect()->route('admin.products.index')
            ->with('success', "Product '{$productName}' deleted successfully.");
    }
}

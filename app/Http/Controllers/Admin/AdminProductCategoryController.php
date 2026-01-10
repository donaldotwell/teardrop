<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductCategory;
use App\Models\FinalizationWindow;
use Illuminate\Http\Request;

class AdminProductCategoryController extends Controller
{
    /**
     * Display all product categories with finalization settings
     */
    public function index()
    {
        $categories = ProductCategory::with(['finalizationWindow', 'products'])
            ->withCount('listings')
            ->orderBy('name')
            ->get();

        return view('admin.product-categories.index', compact('categories'));
    }

    /**
     * Show edit form for category finalization settings
     */
    public function edit(ProductCategory $productCategory)
    {
        $finalizationWindows = FinalizationWindow::active()
            ->orderBy('display_order')
            ->get();

        // Count listings using direct payment
        $directPaymentListings = $productCategory->listings()
            ->where('payment_method', 'direct')
            ->count();

        return view('admin.product-categories.edit', compact('productCategory', 'finalizationWindows', 'directPaymentListings'));
    }

    /**
     * Update category finalization settings
     */
    public function update(Request $request, ProductCategory $productCategory)
    {
        $validated = $request->validate([
            'allows_early_finalization' => 'required|boolean',
            'finalization_window_id' => 'nullable|exists:finalization_windows,id',
            'min_vendor_level_for_early' => 'required|integer|min:1|max:100',
            'early_finalization_notes' => 'nullable|string|max:1000',
        ]);

        // If early finalization is enabled, window must be selected
        if ($validated['allows_early_finalization'] && !$validated['finalization_window_id']) {
            return redirect()->back()->withErrors([
                'finalization_window_id' => 'A finalization window must be selected when early finalization is enabled.'
            ])->withInput();
        }

        // If early finalization is disabled, clear window
        if (!$validated['allows_early_finalization']) {
            $validated['finalization_window_id'] = null;
        }

        $productCategory->update($validated);

        return redirect()->route('admin.product-categories.index')
            ->with('success', 'Category finalization settings updated successfully.');
    }

    /**
     * Quick toggle early finalization for category
     */
    public function toggleEarlyFinalization(ProductCategory $productCategory)
    {
        $newStatus = !$productCategory->allows_early_finalization;

        // If enabling, ensure a window is set
        if ($newStatus && !$productCategory->finalization_window_id) {
            return redirect()->back()->withErrors([
                'error' => 'Cannot enable early finalization without setting a finalization window first.'
            ]);
        }

        $productCategory->update([
            'allows_early_finalization' => $newStatus
        ]);

        $status = $newStatus ? 'enabled' : 'disabled';

        return redirect()->back()
            ->with('success', "Early finalization {$status} for category {$productCategory->name}.");
    }
}

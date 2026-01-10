<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FinalizationWindow;
use Illuminate\Http\Request;

class AdminFinalizationWindowController extends Controller
{
    /**
     * Display all finalization windows
     */
    public function index()
    {
        $windows = FinalizationWindow::withCount(['productCategories', 'orders'])
            ->orderBy('display_order')
            ->get();

        return view('admin.finalization-windows.index', compact('windows'));
    }

    /**
     * Show create form
     */
    public function create()
    {
        return view('admin.finalization-windows.create');
    }

    /**
     * Store new finalization window
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:finalization_windows,name',
            'duration_minutes' => 'required|integer|min:0|max:525600', // Max 1 year
            'description' => 'nullable|string|max:1000',
            'display_order' => 'required|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        FinalizationWindow::create($validated);

        return redirect()->route('admin.finalization-windows.index')
            ->with('success', 'Finalization window created successfully.');
    }

    /**
     * Show edit form
     */
    public function edit(FinalizationWindow $finalizationWindow)
    {
        return view('admin.finalization-windows.edit', compact('finalizationWindow'));
    }

    /**
     * Update finalization window
     */
    public function update(Request $request, FinalizationWindow $finalizationWindow)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:finalization_windows,name,' . $finalizationWindow->id,
            'duration_minutes' => 'required|integer|min:0|max:525600',
            'description' => 'nullable|string|max:1000',
            'display_order' => 'required|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', $finalizationWindow->is_active);

        $finalizationWindow->update($validated);

        return redirect()->route('admin.finalization-windows.index')
            ->with('success', 'Finalization window updated successfully.');
    }

    /**
     * Delete finalization window
     */
    public function destroy(FinalizationWindow $finalizationWindow)
    {
        // Check if window is in use
        if ($finalizationWindow->productCategories()->count() > 0) {
            return redirect()->back()->withErrors([
                'error' => 'Cannot delete this window as it is currently in use by product categories.'
            ]);
        }

        if ($finalizationWindow->orders()->count() > 0) {
            return redirect()->back()->withErrors([
                'error' => 'Cannot delete this window as it has been used in orders.'
            ]);
        }

        $finalizationWindow->delete();

        return redirect()->route('admin.finalization-windows.index')
            ->with('success', 'Finalization window deleted successfully.');
    }

    /**
     * Toggle active status
     */
    public function toggleStatus(FinalizationWindow $finalizationWindow)
    {
        $finalizationWindow->update([
            'is_active' => !$finalizationWindow->is_active
        ]);

        $status = $finalizationWindow->is_active ? 'activated' : 'deactivated';

        return redirect()->back()
            ->with('success', "Finalization window {$status} successfully.");
    }
}

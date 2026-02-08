<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HarmReductionContent;
use Illuminate\Http\Request;

class AdminHarmReductionController extends Controller
{
    /**
     * Display a listing of the harm reduction content.
     */
    public function index()
    {
        $contents = HarmReductionContent::orderBy('category')->orderBy('order')->paginate(20);
        return view('admin.harm-reduction.index', compact('contents'));
    }

    /**
     * Show the form for creating new harm reduction content.
     */
    public function create()
    {
        $categories = ['general', 'opioid', 'stimulant', 'psychedelic', 'resources'];
        return view('admin.harm-reduction.create', compact('categories'));
    }

    /**
     * Store newly created harm reduction content in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'category' => 'required|in:general,opioid,stimulant,psychedelic,resources',
            'order' => 'required|integer|min:0',
            'is_active' => 'boolean',
        ]);

        HarmReductionContent::create($validated);

        return redirect()->route('admin.harm-reduction.index')
            ->with('success', 'Harm reduction content created successfully.');
    }

    /**
     * Show the form for editing the specified harm reduction content.
     */
    public function edit(HarmReductionContent $harmReduction)
    {
        $categories = ['general', 'opioid', 'stimulant', 'psychedelic', 'resources'];
        return view('admin.harm-reduction.edit', compact('harmReduction', 'categories'));
    }

    /**
     * Update the specified harm reduction content in storage.
     */
    public function update(Request $request, HarmReductionContent $harmReduction)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'category' => 'required|in:general,opioid,stimulant,psychedelic,resources',
            'order' => 'required|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $harmReduction->update($validated);

        return redirect()->route('admin.harm-reduction.index')
            ->with('success', 'Harm reduction content updated successfully.');
    }

    /**
     * Remove the specified harm reduction content from storage.
     */
    public function destroy(HarmReductionContent $harmReduction)
    {
        $harmReduction->delete();

        return redirect()->route('admin.harm-reduction.index')
            ->with('success', 'Harm reduction content deleted successfully.');
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Rule;
use Illuminate\Http\Request;

class AdminRuleController extends Controller
{
    /**
     * Display a listing of the rules.
     */
    public function index()
    {
        $rules = Rule::orderBy('category')->orderBy('order')->paginate(20);
        return view('admin.rules.index', compact('rules'));
    }

    /**
     * Show the form for creating a new rule.
     */
    public function create()
    {
        $categories = ['general', 'vendor', 'buyer', 'forum', 'consequences'];
        return view('admin.rules.create', compact('categories'));
    }

    /**
     * Store a newly created rule in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'category' => 'required|in:general,vendor,buyer,forum,consequences',
            'order' => 'required|integer|min:0',
            'is_active' => 'boolean',
        ]);

        Rule::create($validated);

        return redirect()->route('admin.rules.index')
            ->with('success', 'Rule created successfully.');
    }

    /**
     * Show the form for editing the specified rule.
     */
    public function edit(Rule $rule)
    {
        $categories = ['general', 'vendor', 'buyer', 'forum', 'consequences'];
        return view('admin.rules.edit', compact('rule', 'categories'));
    }

    /**
     * Update the specified rule in storage.
     */
    public function update(Request $request, Rule $rule)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'category' => 'required|in:general,vendor,buyer,forum,consequences',
            'order' => 'required|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $rule->update($validated);

        return redirect()->route('admin.rules.index')
            ->with('success', 'Rule updated successfully.');
    }

    /**
     * Remove the specified rule from storage.
     */
    public function destroy(Rule $rule)
    {
        $rule->delete();

        return redirect()->route('admin.rules.index')
            ->with('success', 'Rule deleted successfully.');
    }
}

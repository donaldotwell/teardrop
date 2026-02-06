<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Canary;
use Illuminate\Http\Request;

class AdminCanaryController extends Controller
{
    /**
     * Display a listing of all canaries.
     */
    public function index()
    {
        $canaries = Canary::orderBy('created_at', 'desc')->paginate(20);
        
        return view('admin.canaries.index', compact('canaries'));
    }

    /**
     * Show the form for creating a new canary.
     */
    public function create()
    {
        return view('admin.canaries.create');
    }

    /**
     * Store a newly created canary.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:10000',
        ]);

        Canary::create($validated);

        return redirect()->route('admin.canaries.index')
            ->with('success', 'Canary created successfully.');
    }

    /**
     * Show the form for editing the specified canary.
     */
    public function edit(Canary $canary)
    {
        return view('admin.canaries.edit', compact('canary'));
    }

    /**
     * Update the specified canary.
     */
    public function update(Request $request, Canary $canary)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:10000',
        ]);

        $canary->update($validated);

        return redirect()->route('admin.canaries.index')
            ->with('success', 'Canary updated successfully.');
    }

    /**
     * Remove the specified canary.
     */
    public function destroy(Canary $canary)
    {
        $canary->delete();

        return redirect()->route('admin.canaries.index')
            ->with('success', 'Canary deleted successfully.');
    }
}

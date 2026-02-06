<?php

namespace App\Http\Controllers;

use App\Models\Canary;
use Illuminate\Http\Request;

class CanaryController extends Controller
{
    /**
     * Display the latest canary as plain text.
     * Accessible to guests and authenticated users.
     */
    public function index()
    {
        $canary = Canary::latest();

        // Determine which layout to use based on authentication
        $layout = auth()->check() ? 'layouts.app' : 'layouts.auth';

        return view('canary.index', compact('canary', 'layout'));
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HarmReductionController extends Controller
{
    /**
     * Display harm reduction information.
     * Accessible to guests and authenticated users.
     */
    public function index()
    {
        // Determine which layout to use based on authentication
        $layout = auth()->check() ? 'layouts.app' : 'layouts.auth';

        return view('harm-reduction.index', compact('layout'));
    }
}

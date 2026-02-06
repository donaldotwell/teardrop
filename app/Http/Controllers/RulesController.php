<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RulesController extends Controller
{
    /**
     * Display the site rules.
     * Accessible to guests and authenticated users.
     */
    public function index()
    {
        // Determine which layout to use based on authentication
        $layout = auth()->check() ? 'layouts.app' : 'layouts.auth';

        return view('rules.index', compact('layout'));
    }
}

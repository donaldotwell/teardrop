<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;

class ModeratorSettingsController extends Controller
{
    public function index()
    {
        return view('moderator.settings.index');
    }
}

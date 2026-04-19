<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Fullz;
use App\Models\FullzBase;
use App\Models\FullzPurchase;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AdminAutoshopController extends Controller
{
    public function index(): View
    {
        $stats = [
            'total_bases'    => FullzBase::count(),
            'active_bases'   => FullzBase::where('is_active', true)->count(),
            'total_records'  => Fullz::count(),
            'available'      => Fullz::where('status', 'available')->count(),
            'sold'           => Fullz::where('status', 'sold')->count(),
            'total_purchases'=> FullzPurchase::count(),
            'revenue_usd'    => (float) FullzPurchase::sum('total_usd'),
        ];

        $bases = FullzBase::with('vendor:id,username_pub')
            ->withCount('purchases')
            ->orderByDesc('created_at')
            ->paginate(30);

        $recentPurchases = FullzPurchase::with([
                'buyer:id,username_pub',
                'vendor:id,username_pub',
                'base:id,name',
            ])
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();

        return view('admin.autoshop.index', compact('stats', 'bases', 'recentPurchases'));
    }

    /**
     * Force-deactivate a base.
     */
    public function toggleBase(FullzBase $base): RedirectResponse
    {
        $base->update(['is_active' => !$base->is_active]);
        return back()->with('success', 'Base ' . ($base->is_active ? 'activated' : 'deactivated') . '.');
    }
}

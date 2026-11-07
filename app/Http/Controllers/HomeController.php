<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Show the application dashboard.
     */
    public function home(Request $request): \Illuminate\View\View
    {
        $categoryUuid    = $request->get('cat');
        $subcategoryUuid = $request->get('scat');
        $searchQuery     = $request->get('search');
        $filter          = $request->get('filter');
        $shuffleSeed     = $this->shuffleSeed($request);

        // Base eager-loads shared by all branches — home page only needs one image per listing.
        $with = [
            'user',
            'originCountry',
            'destinationCountry',
            'product.productCategory',
            'firstMedia',
        ];

        // Core constraints shared by all branches
        $base = fn(bool $featured = null) => Listing::with($with)
            ->inStock()
            ->where('listings.is_active', true)
            ->when($featured !== null, fn($q) => $q->where('listings.is_featured', $featured))
            ->whereHas('product', fn($q) => $q
                ->where('products.is_active', true)
                ->whereHas('productCategory', fn($q2) => $q2->where('product_categories.is_active', true))
            )
            ->when($searchQuery, fn($q) => $q->where(fn($q2) => $q2
                ->where('listings.title', 'like', "%{$searchQuery}%")
                ->orWhere('listings.short_description', 'like', "%{$searchQuery}%")
                ->orWhere('listings.description', 'like', "%{$searchQuery}%")
            ))
            ->when($categoryUuid, fn($q) => $q->whereHas('product.productCategory', fn($q2) => $q2
                ->where('product_categories.uuid', $categoryUuid)
                ->where('product_categories.is_active', true)
            ))
            ->when($subcategoryUuid, fn($q) => $q->whereHas('product', fn($q2) => $q2
                ->where('products.uuid', $subcategoryUuid)
                ->where('products.is_active', true)
            ));

        if ($filter === 'all') {
            $all_listings = $base()
                ->orderBy('listings.is_featured', 'desc')
                ->orderByDesc('listings.created_at')
                ->paginate(20)
                ->withQueryString();

            $featured_listings = collect();
            $regular_listings  = collect();

        } elseif ($filter === 'featured') {
            $all_listings = $base(true)
                ->orderByDesc('listings.created_at')
                ->paginate(10)
                ->withQueryString();

            $featured_listings = collect();
            $regular_listings  = collect();

        } else {
            $featured_listings = $base(true)
                ->inRandomOrder()
                ->limit(20)
                ->get();

            // Seeded shuffle instead of ORDER BY random(): random() re-rolls on every
            // request, so page 2 was a fresh shuffle of the same set and listings
            // repeated or vanished between pages.
            $regular_listings = $base(false)
                ->orderByRaw('md5(listings.id::text || ?)', [$shuffleSeed])
                ->paginate(20)
                ->withQueryString();

            $all_listings = collect();
        }

        // $productCategories is already shared by ShareViewData middleware; no re-query needed.
        // Lightweight point-lookups for breadcrumb display (only when filters are active).
        $selectedCategory    = $categoryUuid
            ? ProductCategory::where('uuid', $categoryUuid)->first(['id', 'uuid', 'name'])
            : null;
        $selectedSubcategory = ($selectedCategory && $subcategoryUuid)
            ? Product::where('uuid', $subcategoryUuid)->where('product_category_id', $selectedCategory->id)->first(['id', 'uuid', 'name'])
            : null;

        return view('home', [
            'featured_listings'   => $featured_listings,
            'regular_listings'    => $regular_listings,
            'all_listings'        => $all_listings,
            'filter'              => $filter,
            'selectedCategory'    => $selectedCategory,
            'selectedSubcategory' => $selectedSubcategory,
        ]);
    }

    /**
     * Per-visitor shuffle seed, stable across page links so pagination is
     * consistent. Rotates after 10 minutes, but never while the visitor is on
     * page 2+, so a slow browse through the pages is never reshuffled underneath them.
     */
    private function shuffleSeed(Request $request): string
    {
        $stored = $request->session()->get('home_shuffle');
        $onFirstPage = (int) $request->get('page', 1) <= 1;

        if ($stored && (!$onFirstPage || $stored['at'] > time() - 600)) {
            return $stored['seed'];
        }

        $seed = bin2hex(random_bytes(8));
        $request->session()->put('home_shuffle', ['seed' => $seed, 'at' => time()]);

        return $seed;
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use App\Models\ProductCategory;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Show the application dashboard.
     */
    public function home(Request $request): \Illuminate\View\View
    {
        // Get category and subcategory filters from request
        $categoryUuid = $request->get('cat');
        $subcategoryUuid = $request->get('scat');
        $searchQuery = $request->get('search');
        $filter = $request->get('filter'); // 'featured', 'all', or null (default)

        // Determine which listings to show based on filter
        if ($filter === 'all') {
            // Show all listings combined (no separation)
            $allListingsQuery = Listing::with([
                'user',
                'originCountry',
                'destinationCountry',
                'product.productCategory',
                'media' => function ($query) {
                    $query->orderBy('order');
                }
            ])
            ->where('is_active', true)
            ->whereHas('product', function ($query) {
                $query->where('products.is_active', true)
                    ->whereHas('productCategory', function ($q) {
                        $q->where('product_categories.is_active', true);
                    });
            });

            // Apply search filter
            if ($searchQuery) {
                $allListingsQuery->where(function ($query) use ($searchQuery) {
                    $query->where('title', 'like', "%{$searchQuery}%")
                        ->orWhere('short_description', 'like', "%{$searchQuery}%")
                        ->orWhere('description', 'like', "%{$searchQuery}%");
                });
            }

            // Apply category filter
            if ($categoryUuid) {
                $allListingsQuery->whereHas('product.productCategory', function ($query) use ($categoryUuid) {
                    $query->where('product_categories.uuid', $categoryUuid)
                          ->where('product_categories.is_active', true);
                });
            }

            // Apply subcategory filter
            if ($subcategoryUuid) {
                $allListingsQuery->whereHas('product', function ($query) use ($subcategoryUuid) {
                    $query->where('products.uuid', $subcategoryUuid)
                          ->where('products.is_active', true);
                });
            }

            $all_listings = $allListingsQuery
                ->orderBy('is_featured', 'desc')
                ->inRandomOrder()
                ->get()
                ->filter(function ($listing) {
                    return $listing->isInStock();
                });

            // Paginate manually after filtering
            $page = request()->get('page', 1);
            $perPage = 20;
            $all_listings = new \Illuminate\Pagination\LengthAwarePaginator(
                $all_listings->forPage($page, $perPage),
                $all_listings->count(),
                $perPage,
                $page,
                ['path' => request()->url(), 'query' => request()->query()]
            );

            $featured_listings = collect();
            $regular_listings = collect();

        } elseif ($filter === 'featured') {
            // Show only featured listings
            $featuredQuery = Listing::with([
                'user',
                'originCountry',
                'destinationCountry',
                'product.productCategory',
                'media' => function ($query) {
                    $query->orderBy('order');
                }
            ])
            ->where('is_featured', true)
            ->where('is_active', true)
            ->whereHas('product', function ($query) {
                $query->where('products.is_active', true)
                    ->whereHas('productCategory', function ($q) {
                        $q->where('product_categories.is_active', true);
                    });
            });

            // Apply search filter
            if ($searchQuery) {
                $featuredQuery->where(function ($query) use ($searchQuery) {
                    $query->where('title', 'like', "%{$searchQuery}%")
                        ->orWhere('short_description', 'like', "%{$searchQuery}%")
                        ->orWhere('description', 'like', "%{$searchQuery}%");
                });
            }

            // Apply category filter
            if ($categoryUuid) {
                $featuredQuery->whereHas('product.productCategory', function ($query) use ($categoryUuid) {
                    $query->where('product_categories.uuid', $categoryUuid)
                          ->where('product_categories.is_active', true);
                });
            }

            // Apply subcategory filter
            if ($subcategoryUuid) {
                $featuredQuery->whereHas('product', function ($query) use ($subcategoryUuid) {
                    $query->where('products.uuid', $subcategoryUuid)
                          ->where('products.is_active', true);
                });
            }

            $all_listings = $featuredQuery
                ->inRandomOrder()
                ->get()
                ->filter(function ($listing) {
                    return $listing->isInStock();
                });

            // Paginate manually after filtering
            $page = request()->get('page', 1);
            $perPage = 20;
            $all_listings = new \Illuminate\Pagination\LengthAwarePaginator(
                $all_listings->forPage($page, $perPage),
                $all_listings->count(),
                $perPage,
                $page,
                ['path' => request()->url(), 'query' => request()->query()]
            );

            $featured_listings = collect();
            $regular_listings = collect();

        } else {
            // Default: Show featured section + regular section separately
            // Default: Show featured section + regular section separately
            // Base query for featured listings (sponsored)
            $featuredQuery = Listing::with([
                'user',
                'originCountry',
                'destinationCountry',
                'product.productCategory',
                'media' => function ($query) {
                    $query->orderBy('order');
                }
            ])
            ->where('is_featured', true)
            ->where('is_active', true)
            ->whereHas('product', function ($query) {
                $query->where('products.is_active', true)
                    ->whereHas('productCategory', function ($q) {
                        $q->where('product_categories.is_active', true);
                    });
            });

            // Base query for regular listings (not sponsored)
            $regularQuery = Listing::with([
                'user',
                'originCountry',
                'destinationCountry',
                'product.productCategory',
                'media' => function ($query) {
                    $query->orderBy('order');
                }
            ])
            ->where('is_featured', false)
            ->where('is_active', true)
            ->whereHas('product', function ($query) {
                $query->where('products.is_active', true)
                    ->whereHas('productCategory', function ($q) {
                        $q->where('product_categories.is_active', true);
                    });
            });

            // Apply search filter to both queries
            if ($searchQuery) {
                $featuredQuery->where(function ($query) use ($searchQuery) {
                    $query->where('title', 'like', "%{$searchQuery}%")
                        ->orWhere('short_description', 'like', "%{$searchQuery}%")
                        ->orWhere('description', 'like', "%{$searchQuery}%");
                });

                $regularQuery->where(function ($query) use ($searchQuery) {
                    $query->where('title', 'like', "%{$searchQuery}%")
                        ->orWhere('short_description', 'like', "%{$searchQuery}%")
                        ->orWhere('description', 'like', "%{$searchQuery}%");
                });
            }

            // Apply category filter if provided
            if ($categoryUuid) {
                $featuredQuery->whereHas('product.productCategory', function ($query) use ($categoryUuid) {
                    $query->where('product_categories.uuid', $categoryUuid)
                          ->where('product_categories.is_active', true);
                });

                $regularQuery->whereHas('product.productCategory', function ($query) use ($categoryUuid) {
                    $query->where('product_categories.uuid', $categoryUuid)
                          ->where('product_categories.is_active', true);
                });
            }

            // Apply subcategory (product) filter if provided
            if ($subcategoryUuid) {
                $featuredQuery->whereHas('product', function ($query) use ($subcategoryUuid) {
                    $query->where('products.uuid', $subcategoryUuid)
                          ->where('products.is_active', true);
                });

                $regularQuery->whereHas('product', function ($query) use ($subcategoryUuid) {
                    $query->where('products.uuid', $subcategoryUuid)
                          ->where('products.is_active', true);
                });
            }

            // Get featured listings (sponsored) - filter for stock
            $featured_listings = $featuredQuery
                ->inRandomOrder()
                ->limit(50) // Get more than needed before filtering
                ->get()
                ->filter(function ($listing) {
                    return $listing->isInStock();
                })
                ->take(20); // Take 20 after filtering

            // Get regular listings (not sponsored) - filter for stock
            $regular_listings_query = $regularQuery
                ->inRandomOrder()
                ->get()
                ->filter(function ($listing) {
                    return $listing->isInStock();
                });

            // Paginate manually after filtering
            $page = request()->get('page', 1);
            $perPage = 20;
            $regular_listings = new \Illuminate\Pagination\LengthAwarePaginator(
                $regular_listings_query->forPage($page, $perPage),
                $regular_listings_query->count(),
                $perPage,
                $page,
                ['path' => request()->url(), 'query' => request()->query()]
            );

            $all_listings = collect();
        }

        // Get product categories with their products and listing counts for sidebar
        $productCategories = ProductCategory::where('product_categories.is_active', true)
            ->with([
                'products' => function ($query) {
                    $query->where('products.is_active', true)
                        ->withCount(['listings' => function ($q) {
                            $q->where('listings.is_active', true);
                        }]);
                }
            ])
            ->withCount(['listings' => function ($query) {
                $query->where('listings.is_active', true);
            }])
            ->orderBy('name')
            ->get();

        // Get selected category and subcategory for display
        $selectedCategory = $categoryUuid ? $productCategories->firstWhere('uuid', $categoryUuid) : null;
        $selectedSubcategory = null;
        if ($selectedCategory && $subcategoryUuid) {
            $selectedSubcategory = $selectedCategory->products->firstWhere('uuid', $subcategoryUuid);
        }

        return view('home', [
            'featured_listings' => $featured_listings,
            'regular_listings' => $regular_listings,
            'all_listings' => $all_listings ?? collect(),
            'productCategories' => $productCategories,
            'filter' => $filter,
            'selectedCategory' => $selectedCategory,
            'selectedSubcategory' => $selectedSubcategory,
        ]);
    }
}

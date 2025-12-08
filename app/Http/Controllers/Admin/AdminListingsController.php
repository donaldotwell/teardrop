<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Models\ProductCategory;
use Illuminate\Http\Request;

class AdminListingsController extends Controller
{
    /**
     * Display listings management page
     */
    public function index(Request $request)
    {
        $query = Listing::with(['user', 'product.productCategory', 'originCountry', 'destinationCountry']);

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('uuid', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('username_pub', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('status')) {
            if ($request->get('status') === 'active') {
                $query->where('is_active', true);
            } elseif ($request->get('status') === 'inactive') {
                $query->where('is_active', false);
            }
        }

        if ($request->filled('featured')) {
            if ($request->get('featured') === 'yes') {
                $query->where('is_featured', true);
            } elseif ($request->get('featured') === 'no') {
                $query->where('is_featured', false);
            }
        }

        if ($request->filled('category')) {
            $query->whereHas('product.productCategory', function ($categoryQuery) use ($request) {
                $categoryQuery->where('uuid', $request->get('category'));
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->get('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->get('date_to'));
        }

        // Sorting
        $sortBy = $request->get('sort', 'created_at');
        $sortOrder = $request->get('order', 'desc');

        $allowedSorts = ['created_at', 'title', 'price', 'views', 'updated_at'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortOrder);
        }

        $listings = $query->paginate(20);

        // Calculate stats
        $stats = [
            'total_listings' => Listing::count(),
            'active_listings' => Listing::where('is_active', true)->count(),
            'featured_listings' => Listing::where('is_featured', true)->count(),
            'inactive_listings' => Listing::where('is_active', false)->count(),
            'total_views' => Listing::sum('views'),
        ];

        // Get categories for filter dropdown
        $categories = ProductCategory::orderBy('name')->get();

        return view('admin.listings.index', compact('listings', 'stats', 'categories'));
    }

    /**
     * Show listing details
     */
    public function show(Listing $listing)
    {
        $listing->load([
            'user',
            'product.productCategory',
            'originCountry',
            'destinationCountry',
            'media'
        ]);

        return view('admin.listings.show', compact('listing'));
    }

    /**
     * Feature a listing
     */
    public function feature(Listing $listing)
    {
        $listing->update(['is_featured' => true]);

        return redirect()->back()
            ->with('success', "Listing '{$listing->title}' has been featured.");
    }

    /**
     * Unfeature a listing
     */
    public function unfeature(Listing $listing)
    {
        $listing->update(['is_featured' => false]);

        return redirect()->back()
            ->with('success', "Listing '{$listing->title}' has been unfeatured.");
    }

    /**
     * Disable a listing
     */
    public function disable(Listing $listing)
    {
        $listing->update(['is_active' => false]);

        return redirect()->back()
            ->with('success', "Listing '{$listing->title}' has been disabled.");
    }

    /**
     * Enable a listing
     */
    public function enable(Listing $listing)
    {
        $listing->update(['is_active' => true]);

        return redirect()->back()
            ->with('success', "Listing '{$listing->title}' has been enabled.");
    }

    /**
     * Bulk actions on multiple listings
     */
    public function bulkAction(Request $request)
    {
        $validated = $request->validate([
            'action' => 'required|in:feature,unfeature,enable,disable,delete',
            'listing_ids' => 'required|array',
            'listing_ids.*' => 'exists:listings,id',
        ]);

        $listingIds = $validated['listing_ids'];
        $action = $validated['action'];
        $count = count($listingIds);

        switch ($action) {
            case 'feature':
                Listing::whereIn('id', $listingIds)->update(['is_featured' => true]);
                $message = "{$count} listing(s) have been featured.";
                break;

            case 'unfeature':
                Listing::whereIn('id', $listingIds)->update(['is_featured' => false]);
                $message = "{$count} listing(s) have been unfeatured.";
                break;

            case 'enable':
                Listing::whereIn('id', $listingIds)->update(['is_active' => true]);
                $message = "{$count} listing(s) have been enabled.";
                break;

            case 'disable':
                Listing::whereIn('id', $listingIds)->update(['is_active' => false]);
                $message = "{$count} listing(s) have been disabled.";
                break;

            case 'delete':
                Listing::whereIn('id', $listingIds)->delete();
                $message = "{$count} listing(s) have been deleted.";
                break;

            default:
                return redirect()->back()->with('error', 'Invalid action.');
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Export listings to CSV
     */
    public function export(Request $request)
    {
        $query = Listing::with(['user', 'product.productCategory', 'originCountry', 'destinationCountry']);

        // Apply same filters as index
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('uuid', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('username_pub', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('status')) {
            if ($request->get('status') === 'active') {
                $query->where('is_active', true);
            } elseif ($request->get('status') === 'inactive') {
                $query->where('is_active', false);
            }
        }

        if ($request->filled('featured')) {
            if ($request->get('featured') === 'yes') {
                $query->where('is_featured', true);
            } elseif ($request->get('featured') === 'no') {
                $query->where('is_featured', false);
            }
        }

        if ($request->filled('category')) {
            $query->whereHas('product.productCategory', function ($categoryQuery) use ($request) {
                $categoryQuery->where('uuid', $request->get('category'));
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->get('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->get('date_to'));
        }

        $listings = $query->orderBy('created_at', 'desc')->get();

        $filename = 'listings_export_' . date('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($listings) {
            $file = fopen('php://output', 'w');

            // CSV headers
            fputcsv($file, [
                'Listing ID',
                'Title',
                'Vendor',
                'Category',
                'Price',
                'Shipping Price',
                'Quantity',
                'Origin Country',
                'Destination Country',
                'Status',
                'Featured',
                'Views',
                'Created At',
                'Updated At',
            ]);

            foreach ($listings as $listing) {
                fputcsv($file, [
                    $listing->uuid,
                    $listing->title,
                    $listing->user->username_pub,
                    $listing->product->productCategory->name ?? 'N/A',
                    $listing->price,
                    $listing->price_shipping,
                    $listing->quantity,
                    $listing->originCountry->name,
                    $listing->destinationCountry->name,
                    $listing->is_active ? 'Active' : 'Inactive',
                    $listing->is_featured ? 'Yes' : 'No',
                    $listing->views,
                    $listing->created_at->format('Y-m-d H:i:s'),
                    $listing->updated_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}

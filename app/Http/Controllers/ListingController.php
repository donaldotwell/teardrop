<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use Illuminate\Http\Request;

class ListingController extends Controller
{

    /**
     * Create a new listing.
     * This is done by a vendor.
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application| \Illuminate\Contracts\View\View
     */
    public function create() : \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View|\Illuminate\Foundation\Application| \Illuminate\Contracts\View\View
    {
        $countries = \App\Models\Country::all();
        $productCategories = \App\Models\ProductCategory::with('products')->get();
        return view('listings.create', [
            'countries' => $countries,
            'productCategories' => $productCategories,
        ]);
    }

    /**
     * Display the listing.
     * @param Request $request
     * @param Listing $listing
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function show(Request $request, Listing $listing)
    {
        // Record unique view for authenticated users only
        $listing->recordView(auth()->id());

        $listing->load(['media', 'user', 'originCountry', 'destinationCountry', 'reviews.user']);

        $productCategories = \App\Models\ProductCategory::with(['products' => function($query) {
            $query->withCount('listings');
        }])
        ->withCount('listings')
        ->get();

        // Calculate total price including shipping
        $totalPrice = $listing->price + $listing->price_shipping;

        return view('listings.show', [
            'listing' => $listing,
            'productCategories' => $productCategories,
            'totalPrice' => $totalPrice,
        ]);
    }

    /**
     * Store a new listing.
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request) : \Illuminate\Http\RedirectResponse
    {
        $data = $request->validate([
            'title' => 'required|string|max:140',
            'short_description' => 'required|string|max:255',
            'description' => 'required',
            'price' => 'required|numeric|min:0',
            'price_shipping' => 'required|numeric|min:0',
            'end_date' => 'nullable|date',
            'quantity' => 'required|numeric|min:1',
            'origin_country_id' => 'required|exists:countries,id',
            'destination_country_id' => 'required|exists:countries,id',
            'tags' => 'nullable|string',
            'images' => 'required|array|min:1|max:3',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'return_policy' => 'required|string',
            'product_category_id' => 'required|exists:product_categories,id',
            'product_id' => 'required|exists:products,id',
            'shipping_method' => 'required|in:shipping,pickup,delivery',
            'payment_method' => 'required|in:escrow,direct',
        ]);

        // Ensure product belongs to selected category
        $product = \App\Models\Product::where('id', $data['product_id'])
            ->where('product_category_id', $data['product_category_id'])
            ->firstOrFail();

        $listing = Listing::create([
            'product_id' => $product->id,
            'user_id' => $request->user()->id,
            'title' => $data['title'],
            'short_description' => $data['short_description'],
            'description' => $data['description'],
            'price' => $data['price'],
            'price_shipping' => $data['price_shipping'],
            'shipping_method' => $data['shipping_method'],
            'payment_method' => $data['payment_method'],
            'end_date' => $data['end_date'],
            'quantity' => $data['quantity'],
            'origin_country_id' => $data['origin_country_id'],
            'destination_country_id' => $data['destination_country_id'],
            'tags' => !empty($data['tags']) ? array_map('trim', explode(',', $data['tags'])) : null,
            'return_policy' => $data['return_policy'],
            'is_active' => $request->boolean('is_active'),
        ]);

        // Store the images as base64 encoded content
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $image) {
                $imageContent = base64_encode(file_get_contents($image->getRealPath()));
                $mimeType = $image->getMimeType();

                $listing->media()->create([
                    'content' => $imageContent,
                    'type' => $mimeType,
                    'order' => $index,
                ]);
            }
        }

        return redirect()->route('listings.show', $listing);
    }
}

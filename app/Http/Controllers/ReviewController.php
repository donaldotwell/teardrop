<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    /**
     * Store a new review for an order.
     *
     * @param Request $request
     * @param Order $order
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request, Order $order): \Illuminate\Http\RedirectResponse
    {
        $user = $request->user();

        // Validate that the order is completed
        if ($order->status !== 'completed') {
            return redirect()->back()->withErrors([
                'error' => 'You can only review completed orders.'
            ]);
        }

        // Validate that the user is the buyer, not the vendor
        if ($order->user_id !== $user->id) {
            return redirect()->back()->withErrors([
                'error' => 'Only the buyer can review this order.'
            ]);
        }

        // Validate that the order doesn't already have a review
        if ($order->review) {
            return redirect()->back()->withErrors([
                'error' => 'You have already reviewed this order.'
            ]);
        }

        // Validate the request data
        $data = $request->validate([
            'rating_stealth' => 'required|integer|min:1|max:5',
            'rating_quality' => 'required|integer|min:1|max:5',
            'rating_delivery' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|max:140',
        ]);

        // Create the review
        Review::create([
            'order_id' => $order->id,
            'listing_id' => $order->listing_id,
            'user_id' => $user->id,
            'rating_stealth' => $data['rating_stealth'],
            'rating_quality' => $data['rating_quality'],
            'rating_delivery' => $data['rating_delivery'],
            'comment' => $data['comment'],
            'buyer_price' => $order->usd_price,
        ]);

        return redirect()->route('orders.show', $order)
            ->with('success', 'Review submitted successfully!');
    }
}

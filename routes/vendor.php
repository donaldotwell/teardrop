<?php

use App\Http\Controllers\Vendor\VendorController;
use App\Http\Controllers\Vendor\VendorListingController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:vendor'])->name('vendor.')->group(function () {
    // Vendor Dashboard
    Route::get('/', [VendorController::class, 'dashboard'])->name('dashboard');

    // Vendor Profile/Stats
    Route::get('/profile', [VendorController::class, 'profile'])->name('profile');
    Route::get('/sales', [VendorController::class, 'sales'])->name('sales');
    Route::get('/analytics', [VendorController::class, 'analytics'])->name('analytics');

    // Listing Management Routes
    Route::prefix('listings')->name('listings.')->group(function () {
        Route::get('/', [VendorListingController::class, 'index'])->name('index');
        Route::get('/create', [VendorListingController::class, 'create'])->name('create');
        Route::post('/store', [VendorListingController::class, 'store'])->name('store');
        Route::get('/{listing}/edit', [VendorListingController::class, 'edit'])->name('edit');
        Route::put('/{listing}', [VendorListingController::class, 'update'])->name('update');
        Route::delete('/{listing}', [VendorListingController::class, 'destroy'])->name('destroy');

        // Toggle listing active status
        Route::post('/{listing}/toggle-status', [VendorListingController::class, 'toggleStatus'])->name('toggle-status');

        // Feature listing (requires payment)
        Route::get('/{listing}/feature', [VendorListingController::class, 'showFeatureForm'])->name('feature-form');
        Route::post('/{listing}/feature', [VendorListingController::class, 'featureListing'])->name('feature');
    });

    // Vendor Orders (orders where user is the vendor)
    Route::prefix('orders')->name('orders.')->group(function () {
        Route::get('/', [VendorController::class, 'orders'])->name('index');
        Route::get('/{order}', [VendorController::class, 'showOrder'])->name('show');
        Route::post('/{order}/ship', [VendorController::class, 'shipOrder'])->name('ship');
        Route::post('/{order}/cancel', [VendorController::class, 'cancelOrder'])->name('cancel');
        Route::post('/{order}/message', [VendorController::class, 'sendOrderMessage'])->name('message');
    });

    // Vendor Reviews
    Route::get('/reviews', [VendorController::class, 'reviews'])->name('reviews');

    // Vendor Disputes (orders where vendor is disputed against)
    Route::prefix('disputes')->name('disputes.')->group(function () {
        Route::get('/', [VendorController::class, 'disputes'])->name('index');
        Route::get('/{dispute}', [VendorController::class, 'showDispute'])->name('show');
        Route::post('/{dispute}/add-message', [VendorController::class, 'addDisputeMessage'])->name('add-message');
        Route::post('/{dispute}/upload-evidence', [VendorController::class, 'uploadDisputeEvidence'])->name('upload-evidence');
        Route::get('/{dispute}/evidence/{evidence}/download', [VendorController::class, 'downloadDisputeEvidence'])->name('download-evidence');
    });
});

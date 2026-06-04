<?php

use App\Http\Controllers\AutoshopController;
use App\Http\Controllers\CardController;
use App\Http\Controllers\FsaidController;
use Illuminate\Support\Facades\Route;

// Fullz — browse and purchase fullz records
Route::middleware(['auth'])->prefix('fullz')->name('autoshop.fullz.')->group(function () {
    Route::get('/',                                    [AutoshopController::class, 'index'])       ->name('index');
    Route::get('/base/{base}',                         [AutoshopController::class, 'show'])        ->name('show');
    Route::post('/purchase',                           [AutoshopController::class, 'purchase'])    ->name('purchase')->middleware('throttle:writes');
    Route::get('/my-purchases',                        [AutoshopController::class, 'myPurchases']) ->name('my-purchases');
    Route::get('/my-purchases/{purchase}',             [AutoshopController::class, 'receipt'])     ->name('receipt');
    Route::get('/my-purchases/{purchase}/download',    [AutoshopController::class, 'download'])    ->name('download');
});

// FSAID — browse and purchase FSAID records
Route::middleware(['auth'])->prefix('fsaid')->name('autoshop.fsaid.')->group(function () {
    Route::get('/',                                    [FsaidController::class, 'index'])       ->name('index');
    Route::get('/base/{base}',                         [FsaidController::class, 'show'])        ->name('show');
    Route::post('/purchase',                           [FsaidController::class, 'purchase'])    ->name('purchase')->middleware('throttle:writes');
    Route::get('/my-purchases',                        [FsaidController::class, 'myPurchases']) ->name('my-purchases');
    Route::get('/my-purchases/{purchase}',             [FsaidController::class, 'receipt'])     ->name('receipt');
    Route::get('/my-purchases/{purchase}/download',    [FsaidController::class, 'download'])    ->name('download');
});

// Cards — browse and purchase card records
Route::middleware(['auth'])->prefix('cards')->name('autoshop.cards.')->group(function () {
    Route::get('/',                                    [CardController::class, 'index'])       ->name('index');
    Route::get('/base/{base}',                         [CardController::class, 'show'])        ->name('show');
    Route::post('/purchase',                           [CardController::class, 'purchase'])    ->name('purchase')->middleware('throttle:writes');
    Route::get('/my-purchases',                        [CardController::class, 'myPurchases']) ->name('my-purchases');
    Route::get('/my-purchases/{purchase}',             [CardController::class, 'receipt'])     ->name('receipt');
    Route::get('/my-purchases/{purchase}/download',    [CardController::class, 'download'])    ->name('download');
});

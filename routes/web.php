<?php

use App\Http\Controllers\BotChallengeController;
use App\Http\Controllers\DisputeController;
use App\Http\Controllers\ForumCommentController;
use App\Http\Controllers\ForumPostController;
use App\Http\Controllers\ForumReportController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MarketKeysController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\SupportTicketController;
use App\Http\Controllers\VendorController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

// Bot Challenge Routes (no auth required)
Route::get('/bot-challenge', [BotChallengeController::class, 'show'])->name('bot-challenge');
Route::post('/bot-challenge/verify', [BotChallengeController::class, 'verify'])->name('bot-challenge.verify');
Route::get('/bot-challenge/image', [BotChallengeController::class, 'image'])->name('bot-challenge.image');
Route::get('/bot-challenge/locked', [BotChallengeController::class, 'locked'])->name('bot-challenge.locked');

// Auth routes
Route::prefix('auth')->group(function () {
    // Login routes
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login');
    // PGP Login MFA Challenge
    Route::get('/login/pgp-challenge', [AuthController::class, 'showPgpLoginChallenge'])->name('login.pgp-challenge');
    Route::post('/login/pgp-challenge', [AuthController::class, 'verifyPgpLoginChallenge'])->name('login.pgp-challenge.verify');
    // Register routes
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register');
});

// Account Recovery routes (public - no auth required)
Route::prefix('recovery')->name('recovery.')->group(function () {
    Route::get('/', [\App\Http\Controllers\AccountRecoveryController::class, 'showRecoveryForm'])->name('show');
    Route::post('/verify', [\App\Http\Controllers\AccountRecoveryController::class, 'verifyPassphrases'])->name('verify');
    Route::get('/reset-password', [\App\Http\Controllers\AccountRecoveryController::class, 'showResetForm'])->name('reset-password');
    Route::post('/reset-password', [\App\Http\Controllers\AccountRecoveryController::class, 'resetPassword'])->name('reset-password.submit');
});

// Market Keys - Public staff PGP directory (no auth required)
Route::get('/market-keys', [MarketKeysController::class, 'index'])->name('market-keys');

// Warrant Canary - Public transparency page (no auth required)
Route::get('/canary', [\App\Http\Controllers\CanaryController::class, 'index'])->name('canary');

// Site Rules - Public rules and guidelines (no auth required)
Route::get('/rules', [\App\Http\Controllers\RulesController::class, 'index'])->name('rules');

// Harm Reduction - Public safety information (no auth required)
Route::get('/harm-reduction', [\App\Http\Controllers\HarmReductionController::class, 'index'])->name('harm-reduction');

Route::middleware('auth')->group(function () {
    // Logout route
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Base profile
    Route::get('/profile', [ProfileController::class, 'show'])
        ->name('profile.show');

    // Profile completion
    Route::get('/profile/complete', [ProfileController::class, 'complete'])
        ->name('profile.complete');

    // Password management
    Route::get('/profile/password', [ProfileController::class, 'showPasswordForm'])
        ->name('profile.password.show');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])
        ->name('profile.password.update');

    // PIN management
    Route::get('/profile/pin', [ProfileController::class, 'showPinForm'])
        ->name('profile.pin.show');
    Route::put('/profile/pin', [ProfileController::class, 'updatePin'])
        ->name('profile.pin.update');

    // Recovery passphrases management
    Route::get('/profile/passphrases', [ProfileController::class, 'showPassphrasesForm'])
        ->name('profile.passphrases.show');
    Route::put('/profile/passphrases', [ProfileController::class, 'updatePassphrases'])
        ->name('profile.passphrases.update');

    // PGP Key Verification Flow
    Route::get('/profile/pgp', [ProfileController::class, 'showPgpForm'])
        ->name('profile.pgp');
    Route::post('/profile/pgp/initiate', [ProfileController::class, 'initiatePgpVerification'])
        ->name('profile.pgp.initiate');
    Route::get('/profile/pgp/verify/{verification}', [ProfileController::class, 'showPgpVerificationChallenge'])
        ->name('profile.pgp.verify');
    Route::post('/profile/pgp/verify/{verification}', [ProfileController::class, 'verifyPgpCode'])
        ->name('profile.pgp.verify.submit');

    // Account Deletion (requires PGP + password verification)
    Route::get('/profile/delete-account', [ProfileController::class, 'showDeleteAccountForm'])
        ->name('profile.delete-account.show');
    Route::post('/profile/delete-account', [ProfileController::class, 'deleteAccount'])
        ->name('profile.delete-account.process');
    Route::post('/profile/delete-account/confirm', [ProfileController::class, 'confirmDeleteAccount'])
        ->name('profile.delete-account.confirm');

    // General profile management
    Route::put('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');
    Route::post('/profile/security', [ProfileController::class, 'updateSecurity'])
        ->name('profile.security.update');

    Route::get('/profile/{username}', [ProfileController::class, 'showPublicView'])
        ->name('profile.show_public_view');

    // wallet route
    Route::get('/wallet', [\App\Http\Controllers\WalletController::class, 'index'])->name('wallet.index');

    Route::get('/', [HomeController::class, 'home'])->name('home');
    Route::get('/listings', [HomeController::class, 'home'])->name('listings.index');
    Route::get('/listings/{listing}', [\App\Http\Controllers\ListingController::class, 'show'])->name('listings.show');

    // create an order for a listing using get
    Route::get('/listings/{listing}/create', [\App\Http\Controllers\OrderController::class, 'create'])->name('orders.create');
    Route::post('/listings/{listing}/orders', [\App\Http\Controllers\OrderController::class, 'store'])
        ->name('orders.store');

    Route::get('/messages', [\App\Http\Controllers\MessageController::class, 'index'])
        ->name('messages.index');
    Route::get('/messages/{thread}', [\App\Http\Controllers\MessageController::class, 'show'])
        ->name('messages.show');
    Route::post('/messages/{thread}', [\App\Http\Controllers\MessageController::class, 'store'])
        ->name('messages.store');
    /**
     * Order routes
     */
    Route::get('/orders', [\App\Http\Controllers\OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [\App\Http\Controllers\OrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/{order}/complete', [\App\Http\Controllers\OrderController::class, 'complete'])->name('orders.complete');
    // Route::post('/orders/{order}/ship', [\App\Http\Controllers\OrderController::class, 'ship'])->name('orders.ship');
    Route::post('/orders/{order}/message', [\App\Http\Controllers\OrderController::class, 'sendMessage'])->name('orders.message');
    Route::post('/orders/{order}/review', [ReviewController::class, 'store'])->name('reviews.store');

    // vendor routes
    Route::get('/seller/convert', [VendorController::class, 'showConvertForm'])->name('vendor.convert');
    Route::post('/seller/convert', [VendorController::class, 'convert'])->name('vendor.convert.store');
    Route::get('/seller/{user}', [VendorController::class, 'show'])->name('vendor.show');

    Route::prefix('support')->name('support.')->group(function () {

        // User ticket management
        Route::get('/', [SupportTicketController::class, 'index'])->name('index');
        Route::get('/create', [SupportTicketController::class, 'create'])->name('create');
        Route::post('/', [SupportTicketController::class, 'store'])->name('store');
        Route::get('/{supportTicket}', [SupportTicketController::class, 'show'])->name('show');
        Route::post('/{supportTicket}/message', [SupportTicketController::class, 'addMessage'])->name('add-message');
        Route::post('/{supportTicket}/close', [SupportTicketController::class, 'closeTicket'])->name('close');
        Route::post('/{supportTicket}/attachment', [SupportTicketController::class, 'uploadAttachment'])->name('upload-attachment');
        Route::get('/{supportTicket}/attachment/{attachment}/download', [SupportTicketController::class, 'downloadAttachment'])->name('download-attachment');
        Route::post('/{supportTicket}/mark-read', [SupportTicketController::class, 'markMessagesRead'])->name('mark-read');

    });

    // Bitcoin wallet routes
    Route::prefix('bitcoin')->name('bitcoin.')->group(function () {
        Route::get('/', [\App\Http\Controllers\BitcoinController::class, 'index'])->name('index');
        Route::get('/topup', [\App\Http\Controllers\BitcoinController::class, 'topup'])->name('topup');
        Route::post('/withdraw', [\App\Http\Controllers\BitcoinController::class, 'withdraw'])->name('withdraw');
    });

    // Monero wallet routes
    Route::prefix('monero')->name('monero.')->group(function () {
        Route::get('/', [\App\Http\Controllers\MoneroController::class, 'index'])->name('index');
        Route::get('/topup', [\App\Http\Controllers\MoneroController::class, 'topup'])->name('topup');
        Route::post('/withdraw', [\App\Http\Controllers\MoneroController::class, 'withdraw'])->name('withdraw');
        Route::get('/transaction/{transaction}', [\App\Http\Controllers\MoneroController::class, 'transaction'])->name('transaction');
    });
});

// User Disputes Routes
Route::middleware('auth')->prefix('disputes')->name('disputes.')->group(function () {

    // List user's disputes
    Route::get('/', [DisputeController::class, 'index'])->name('index');

    // Create dispute form
    Route::get('/create/{order}', [DisputeController::class, 'create'])->name('create');
    Route::post('/create/{order}', [DisputeController::class, 'store'])->name('store');

    // View specific dispute
    Route::get('/{dispute}', [DisputeController::class, 'show'])->name('show');

    // Add message to dispute
    Route::post('/{dispute}/messages', [DisputeController::class, 'addMessage'])->name('messages.store');

    // Upload evidence
    Route::post('/{dispute}/evidence', [DisputeController::class, 'uploadEvidence'])->name('evidence.store');

    // Download evidence (with access control)
    Route::get('/{dispute}/evidence/{evidence}/download', [DisputeController::class, 'downloadEvidence'])
        ->name('evidence.download');

    // Mark messages as read
    Route::post('/{dispute}/mark-read', [DisputeController::class, 'markMessagesRead'])->name('mark-read');


});

Route::middleware('auth')->prefix('forum')->name('forum.')->group(function () {
    // Posts
    Route::get('/', [ForumPostController::class, 'index'])->name('index');
    Route::get('/create', [ForumPostController::class, 'create'])->name('posts.create');
    Route::post('/posts', [ForumPostController::class, 'store'])->name('posts.store');
    Route::get('/posts/{post}', [ForumPostController::class, 'show'])->name('posts.show');
    Route::get('/posts/{post}/edit', [ForumPostController::class, 'edit'])->name('posts.edit');
    Route::put('/posts/{post}', [ForumPostController::class, 'update'])->name('posts.update');
    Route::delete('/posts/{post}', [ForumPostController::class, 'destroy'])->name('posts.destroy');

    // Comments
    Route::post('/posts/{post}/comments', [ForumCommentController::class, 'store'])->name('comments.store');
    Route::post('/comments/{comment}/reply', [ForumCommentController::class, 'reply'])->name('comments.reply');
    Route::delete('/comments/{comment}', [ForumCommentController::class, 'destroy'])->name('comments.destroy');

    // Reports
    Route::post('/posts/{post}/report', [ForumReportController::class, 'reportPost'])->name('posts.report');
    Route::post('/comments/{comment}/report', [ForumReportController::class, 'reportComment'])->name('comments.report');
});

Route::get('/landing', function () {
    return view('landing');
});

<?php
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AdminDisputeController;
use App\Http\Controllers\Admin\AdminListingsController;
use App\Http\Controllers\Admin\AdminSupportTicketController;
use App\Http\Controllers\Admin\AdminUsersController;
use App\Http\Controllers\Admin\AdminOrdersController;
use App\Http\Controllers\Admin\AdminFinalizationWindowController;
use App\Http\Controllers\Admin\AdminProductCategoryController;
use App\Http\Controllers\Admin\AdminForumController;
use Illuminate\Support\Facades\Route;

// Admin routes - protected by admin middleware
Route::middleware(['auth', 'admin'])->name('admin.')->group(function () {

    // Dashboard
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');

    // Users Management
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [AdminUsersController::class, 'index'])->name('index');
        Route::get('/export', [AdminUsersController::class, 'export'])->name('export');
        Route::get('/{user}', [AdminUsersController::class, 'show'])->name('show');
        Route::get('/{user}/edit', [AdminUsersController::class, 'edit'])->name('edit');
        Route::put('/{user}', [AdminUsersController::class, 'update'])->name('update');
        Route::post('/{user}/ban', [AdminUsersController::class, 'ban'])->name('ban');
        Route::post('/{user}/unban', [AdminUsersController::class, 'unban'])->name('unban');
        Route::post('/{user}/reset-password', [AdminUsersController::class, 'resetPassword'])->name('reset-password');
        Route::get('/{user}/wallet-transactions', [AdminUsersController::class, 'walletTransactions'])->name('wallet-transactions');
        Route::post('/{user}/adjust-balance', [AdminUsersController::class, 'adjustBalance'])->name('adjust-balance');
        Route::post('/{user}/promote-to-vendor', [AdminUsersController::class, 'promoteToVendor'])->name('promote-to-vendor');
        Route::post('/{user}/toggle-early-finalization', [AdminUsersController::class, 'toggleEarlyFinalizationAccess'])->name('toggle-early-finalization');
    });

    // Orders Management
    Route::prefix('orders')->name('orders.')->group(function () {
        Route::get('/', [AdminOrdersController::class, 'index'])->name('index');
        Route::get('/export', [AdminOrdersController::class, 'export'])->name('export');
        Route::get('/{order}', [AdminOrdersController::class, 'show'])->name('show');
        Route::post('/{order}/complete', [AdminOrdersController::class, 'complete'])->name('complete');
        Route::post('/{order}/cancel', [AdminOrdersController::class, 'cancel'])->name('cancel');
    });

    // Listings Management
    Route::prefix('listings')->name('listings.')->group(function () {
        Route::get('/', [AdminListingsController::class, 'index'])->name('index');
        Route::get('/export', [AdminListingsController::class, 'export'])->name('export');
        Route::post('/bulk', [AdminListingsController::class, 'bulkAction'])->name('bulk');
        Route::get('/{listing}', [AdminListingsController::class, 'show'])->name('show');
        Route::post('/{listing}/feature', [AdminListingsController::class, 'feature'])->name('feature');
        Route::post('/{listing}/unfeature', [AdminListingsController::class, 'unfeature'])->name('unfeature');
        Route::post('/{listing}/disable', [AdminListingsController::class, 'disable'])->name('disable');
        Route::post('/{listing}/enable', [AdminListingsController::class, 'enable'])->name('enable');
    });

    // Dispute Management Routes
    Route::prefix('disputes')->name('disputes.')->group(function () {
        Route::get('/', [AdminDisputeController::class, 'index'])->name('index');
        Route::get('/{dispute}', [AdminDisputeController::class, 'show'])->name('show');
        Route::post('/{dispute}/assign', [AdminDisputeController::class, 'assign'])->name('assign');
        Route::post('/{dispute}/reassign-moderator', [AdminDisputeController::class, 'reassignModerator'])->name('reassign-moderator');
        Route::post('/{dispute}/escalate', [AdminDisputeController::class, 'escalate'])->name('escalate');
        Route::post('/{dispute}/resolve', [AdminDisputeController::class, 'resolve'])->name('resolve');
        Route::post('/{dispute}/close', [AdminDisputeController::class, 'close'])->name('close');
        Route::post('/{dispute}/add-admin-message', [AdminDisputeController::class, 'addAdminMessage'])->name('add-admin-message');
        Route::post('/{dispute}/update-priority', [AdminDisputeController::class, 'updatePriority'])->name('update-priority');
        Route::get('/export', [AdminDisputeController::class, 'export'])->name('export');
        Route::get('/{dispute}/evidence/{evidence}/download', [AdminDisputeController::class, 'downloadEvidence'])->name('download-evidence');
        Route::post('/{dispute}/evidence/{evidence}/verify', [AdminDisputeController::class, 'verifyEvidence'])->name('verify-evidence');
    });

    Route::prefix('support')->name('support.')->group(function () {

        // Admin ticket management
        Route::get('/', [AdminSupportTicketController::class, 'index'])->name('index');
        Route::get('/{supportTicket}', [AdminSupportTicketController::class, 'show'])->name('show');
        Route::post('/{supportTicket}/assign', [AdminSupportTicketController::class, 'assign'])->name('assign');
        Route::post('/{supportTicket}/reassign-staff', [AdminSupportTicketController::class, 'reassignStaff'])->name('reassign-staff');
        Route::post('/{supportTicket}/update-status', [AdminSupportTicketController::class, 'updateStatus'])->name('update-status');
        Route::post('/{supportTicket}/update-priority', [AdminSupportTicketController::class, 'update-priority'])->name('update-priority');
        Route::post('/{supportTicket}/add-message', [AdminSupportTicketController::class, 'addMessage'])->name('add-message');
        Route::post('/{supportTicket}/resolve', [AdminSupportTicketController::class, 'resolve'])->name('resolve');
        Route::post('/{supportTicket}/close', [AdminSupportTicketController::class, 'close'])->name('close');
        Route::get('/export', [AdminSupportTicketController::class, 'export'])->name('export');
        Route::get('/{supportTicket}/attachment/{attachment}/download', [AdminSupportTicketController::class, 'downloadAttachment'])->name('download-attachment');

        Route::get('/export', [AdminSupportTicketController::class, 'export'])->name('export');
        Route::post('/auto-assign', [AdminSupportTicketController::class, 'autoAssign'])->name('auto-assign');
        Route::post('/bulk-action', [AdminSupportTicketController::class, 'bulkAction'])->name('bulk-action');
        Route::get('/stats', [AdminSupportTicketController::class, 'getStats'])->name('stats');
    });

    // Forum Management Routes
    Route::prefix('forum')->name('forum.')->group(function () {
        Route::get('/', [AdminForumController::class, 'index'])->name('index');
        Route::post('/{post}/reassign-moderator', [AdminForumController::class, 'reassignModerator'])->name('reassign-moderator');
        Route::post('/{post}/approve', [AdminForumController::class, 'approve'])->name('approve');
        Route::post('/{post}/reject', [AdminForumController::class, 'reject'])->name('reject');
        Route::post('/{post}/pin', [AdminForumController::class, 'pin'])->name('pin');
        Route::post('/{post}/unpin', [AdminForumController::class, 'unpin'])->name('unpin');
        Route::post('/{post}/lock', [AdminForumController::class, 'lock'])->name('lock');
        Route::post('/{post}/unlock', [AdminForumController::class, 'unlock'])->name('unlock');
        Route::delete('/{post}', [AdminForumController::class, 'destroy'])->name('destroy');
    });

    // Reports
    Route::get('/reports', [AdminController::class, 'reports'])->name('reports');
    Route::get('/reports/financial', [AdminController::class, 'financialReport'])->name('reports.financial');
    Route::get('/reports/users', [AdminController::class, 'usersReport'])->name('reports.users');
    Route::get('/reports/export/{type}', [AdminController::class, 'exportReport'])->name('reports.export');


    // Settings
    Route::get('/settings', [AdminController::class, 'settings'])->name('settings');
    Route::post('/settings', [AdminController::class, 'updateSettings'])->name('settings.update');

    // Cache Management
    Route::post('/cache/clear', [AdminController::class, 'clearCache'])->name('cache.clear');
    Route::post('/cache/optimize', [AdminController::class, 'optimizeCache'])->name('cache.optimize');
    Route::post('/queue/restart', [AdminController::class, 'restartQueue'])->name('queue.restart');

    // Database Management
    Route::post('/db/cleanup', [AdminController::class, 'cleanupDatabase'])->name('db.cleanup');
    Route::post('/db/backup', [AdminController::class, 'backupDatabase'])->name('db.backup');

    // Maintenance
    Route::post('/maintenance/enable', [AdminController::class, 'enableMaintenance'])->name('maintenance.enable');
    Route::post('/data/purge', [AdminController::class, 'purgeOldData'])->name('data.purge');

    // Finalization Windows Management
    Route::prefix('finalization-windows')->name('finalization-windows.')->group(function () {
        Route::get('/', [AdminFinalizationWindowController::class, 'index'])->name('index');
        Route::get('/create', [AdminFinalizationWindowController::class, 'create'])->name('create');
        Route::post('/', [AdminFinalizationWindowController::class, 'store'])->name('store');
        Route::get('/{finalizationWindow}/edit', [AdminFinalizationWindowController::class, 'edit'])->name('edit');
        Route::put('/{finalizationWindow}', [AdminFinalizationWindowController::class, 'update'])->name('update');
        Route::delete('/{finalizationWindow}', [AdminFinalizationWindowController::class, 'destroy'])->name('destroy');
        Route::post('/{finalizationWindow}/toggle-status', [AdminFinalizationWindowController::class, 'toggleStatus'])->name('toggle-status');
    });

    // Product Categories Management
    Route::prefix('product-categories')->name('product-categories.')->group(function () {
        Route::get('/', [AdminProductCategoryController::class, 'index'])->name('index');
        Route::get('/create', [AdminProductCategoryController::class, 'create'])->name('create');
        Route::post('/', [AdminProductCategoryController::class, 'store'])->name('store');
        Route::get('/{productCategory}/edit', [AdminProductCategoryController::class, 'edit'])->name('edit');
        Route::put('/{productCategory}', [AdminProductCategoryController::class, 'update'])->name('update');
        Route::delete('/{productCategory}', [AdminProductCategoryController::class, 'destroy'])->name('destroy');
        Route::post('/{productCategory}/toggle-status', [AdminProductCategoryController::class, 'toggleStatus'])->name('toggle-status');
        Route::post('/{productCategory}/toggle-early-finalization', [AdminProductCategoryController::class, 'toggleEarlyFinalization'])->name('toggle-early-finalization');
    });

    // Products Management
    Route::prefix('products')->name('products.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\AdminProductController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\Admin\AdminProductController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\Admin\AdminProductController::class, 'store'])->name('store');
        Route::get('/{product}/edit', [\App\Http\Controllers\Admin\AdminProductController::class, 'edit'])->name('edit');
        Route::put('/{product}', [\App\Http\Controllers\Admin\AdminProductController::class, 'update'])->name('update');
        Route::delete('/{product}', [\App\Http\Controllers\Admin\AdminProductController::class, 'destroy'])->name('destroy');
        Route::post('/{product}/toggle-status', [\App\Http\Controllers\Admin\AdminProductController::class, 'toggleStatus'])->name('toggle-status');
    });

});

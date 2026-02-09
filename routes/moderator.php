<?php

use App\Http\Controllers\Staff\ForumModerationController;
use App\Http\Controllers\Staff\ModeratorAuditController;
use App\Http\Controllers\Staff\ModeratorContentController;
use App\Http\Controllers\Staff\ModeratorController;
use App\Http\Controllers\Staff\ModeratorDisputeController;
use App\Http\Controllers\Staff\ModeratorSettingsController;
use App\Http\Controllers\Staff\ModeratorTicketController;
use App\Http\Controllers\Staff\ModeratorUserController;
use Illuminate\Support\Facades\Route;


Route::middleware(['auth', 'moderator'])->name('moderator.')->group(function () {
    // Dashboard
    Route::get('/', [ModeratorController::class, 'dashboard'])->name('dashboard');

    // User Management
    Route::get('/users', [ModeratorUserController::class, 'index'])->name('users.index');

    // Content Management
    Route::get('/content', [ModeratorContentController::class, 'index'])->name('content.index');

    // Audit Logs
    Route::get('/audit', [ModeratorAuditController::class, 'index'])->name('audit.index');

    // Settings (Admin only)
    Route::get('/settings', [ModeratorSettingsController::class, 'index'])->name('settings');

    // Ticket Management Routes (merged from staff.php - support role doesn't exist)
    Route::prefix('tickets')->name('tickets.')->group(function () {
        Route::get('/', [ModeratorTicketController::class, 'index'])->name('index');
        Route::get('/{supportTicket}', [ModeratorTicketController::class, 'show'])->name('show');

        // Assignment Actions
        Route::post('/{supportTicket}/assign', [ModeratorTicketController::class, 'assign'])->name('assign');
        Route::post('/{supportTicket}/assign-me', [ModeratorTicketController::class, 'assignMe'])->name('assign-me');
        Route::post('/{supportTicket}/unassign', [ModeratorTicketController::class, 'unassign'])->name('unassign');
        Route::post('/{supportTicket}/reassign-staff', [ModeratorTicketController::class, 'reassignStaff'])->name('reassign-staff');
        Route::post('/auto-assign', [ModeratorTicketController::class, 'autoAssign'])->name('auto-assign');

        // Status & Priority Management
        Route::post('/{supportTicket}/update-status', [ModeratorTicketController::class, 'updateStatus'])->name('update-status');
        Route::post('/{supportTicket}/update-priority', [ModeratorTicketController::class, 'updatePriority'])->name('update-priority');

        // Response Actions
        Route::post('/{supportTicket}/add-response', [ModeratorTicketController::class, 'addResponse'])->name('add-response');
        Route::post('/{supportTicket}/add-message', [ModeratorTicketController::class, 'addMessage'])->name('add-message');
        Route::post('/{supportTicket}/escalate', [ModeratorTicketController::class, 'escalate'])->name('escalate');
        Route::post('/{supportTicket}/resolve', [ModeratorTicketController::class, 'resolve'])->name('resolve');

        // Attachments
        Route::get('/{supportTicket}/attachment/{attachment}/download', [ModeratorTicketController::class, 'downloadAttachment'])->name('download-attachment');
    });

    // Dispute Management Routes
    Route::prefix('disputes')->name('disputes.')->group(function () {
        Route::get('/', [ModeratorDisputeController::class, 'index'])->name('index');
        Route::get('/{dispute}', [ModeratorDisputeController::class, 'show'])->name('show');

        // Assignment Actions
        Route::post('/{dispute}/assign', [ModeratorDisputeController::class, 'assign'])->name('assign');
        Route::post('/{dispute}/unassign', [ModeratorDisputeController::class, 'unassign'])->name('unassign');
        Route::post('/{dispute}/reassign-moderator', [ModeratorDisputeController::class, 'reassignModerator'])->name('reassign-moderator');
        Route::post('/auto-assign', [ModeratorDisputeController::class, 'autoAssign'])->name('auto-assign');

        // Moderation Actions
        Route::post('/{dispute}/add-note', [ModeratorDisputeController::class, 'addNote'])->name('add-note');
        Route::post('/{dispute}/add-message', [ModeratorDisputeController::class, 'addMessage'])->name('add-message');
        Route::post('/{dispute}/request-info', [ModeratorDisputeController::class, 'requestInfo'])->name('request-info');
        Route::post('/{dispute}/resolve', [ModeratorDisputeController::class, 'resolve'])->name('resolve');
        Route::post('/{dispute}/escalate', [ModeratorDisputeController::class, 'escalate'])->name('escalate');
        Route::post('/{dispute}/upload-evidence', [ModeratorDisputeController::class, 'uploadEvidence'])->name('upload-evidence');
        Route::get('/{dispute}/evidence/{evidence}/download', [ModeratorDisputeController::class, 'downloadEvidence'])->name('download-evidence');
        Route::post('/{dispute}/mark-read', [ModeratorDisputeController::class, 'markMessagesRead'])->name('mark-read');
    });

    // Forum Moderation Routes
    Route::prefix('forum')->name('forum.')->group(function () {
        Route::prefix('moderate')->name('moderate.')->group(function () {
            // Pending Posts Management
            Route::get('/pending-posts', [ForumModerationController::class, 'pendingPosts'])->name('pending-posts');
            Route::get('/all-posts', [ForumModerationController::class, 'allPosts'])->name('all-posts');
            Route::post('/posts/{post}/approve', [ForumModerationController::class, 'approvePost'])->name('posts.approve');
            Route::post('/posts/{post}/reject', [ForumModerationController::class, 'rejectPost'])->name('posts.reject');

            // Reports Management
            Route::get('/reports', [ForumModerationController::class, 'reports'])->name('reports');
            Route::post('/reports/{report}/review', [ForumModerationController::class, 'reviewReport'])->name('reports.review');

            // User Ban/Unban Actions
            Route::post('/users/{user}/ban', [ForumModerationController::class, 'banUser'])->name('users.ban');
            Route::post('/users/{user}/unban', [ForumModerationController::class, 'unbanUser'])->name('users.unban');
        });
    });

    // Content Management Actions
    Route::post('/content/{type}/{id}/hide', [ModeratorContentController::class, 'hide'])->name('content.hide');
    Route::post('/content/{type}/{id}/show', [ModeratorContentController::class, 'show'])->name('content.show');
    Route::delete('/content/{type}/{id}', [ModeratorContentController::class, 'delete'])->name('content.delete');

    // Audit Log Export
    Route::get('/audit/export', [ModeratorAuditController::class, 'export'])->name('audit.export');

});

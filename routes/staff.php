<?php

use Illuminate\Support\Facades\Route;

Route::prefix('staff')->name('staff.')->middleware(['auth', 'role:support'])->group(function () {
    Route::prefix('support')->name('support.')->group(function () {

        // Staff ticket management
        Route::get('/', [StaffSupportTicketController::class, 'index'])->name('index');
        Route::get('/{supportTicket}', [StaffSupportTicketController::class, 'show'])->name('show');
        Route::post('/{supportTicket}/assign-me', [StaffSupportTicketController::class, 'assignMe'])->name('assign-me');
        Route::post('/{supportTicket}/reassign-staff', [StaffSupportTicketController::class, 'reassignStaff'])->name('reassign-staff');
        Route::post('/{supportTicket}/update-status', [StaffSupportTicketController::class, 'updateStatus'])->name('update-status');
        Route::post('/{supportTicket}/update-priority', [StaffSupportTicketController::class, 'updatePriority'])->name('update-priority');
        Route::post('/{supportTicket}/add-message', [StaffSupportTicketController::class, 'addMessage'])->name('add-message');
        Route::post('/{supportTicket}/resolve', [StaffSupportTicketController::class, 'resolve'])->name('resolve');
        Route::get('/{supportTicket}/attachment/{attachment}/download', [StaffSupportTicketController::class, 'downloadAttachment'])->name('download-attachment');

    });
});

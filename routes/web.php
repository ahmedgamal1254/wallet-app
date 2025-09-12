<?php

use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\{
    TopupController,ReferralCodeController,
    WithdrawalController,DashboardController
};
use Illuminate\Support\Facades\Route;

// Admin Routes
Route::prefix('admin')->name('admin.')->group(function () {
    // Authentication routes (guest only)
    Route::middleware('guest:admin')->group(function () {
        Route::get('login', [AdminAuthController::class, 'showLoginForm'])->name('login');
        Route::post('login', [AdminAuthController::class, 'login']);
    });

    // Authenticated admin routes
    Route::middleware('admin')->group(function () {
        Route::post('logout', [AdminAuthController::class, 'logout'])->name('logout');

        // Dashboard
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::post('notifications/{id}/read', [DashboardController::class, 'markNotificationAsRead'])->name('notifications.read');

        // Withdrawals
        Route::resource('withdrawals', WithdrawalController::class)->except(['edit', 'update', 'destroy']);
        Route::post('withdrawals/{withdrawal}/approve', [WithdrawalController::class, 'approve'])->name('withdrawals.approve');
        Route::post('withdrawals/{withdrawal}/reject', [WithdrawalController::class, 'reject'])->name('withdrawals.reject');

        // Top-ups
        Route::resource('topups', TopupController::class)->only(['index', 'show']);
        Route::post('topups/{topup}/approve', [TopupController::class, 'approve'])->name('topups.approve');
        Route::post('topups/{topup}/reject', [TopupController::class, 'reject'])->name('topups.reject');

        Route::resource('referral_codes', ReferralCodeController::class);
    });
});

Route::get('/', function () {
    return view('welcome');
});

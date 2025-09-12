<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\WalletController;
use App\Http\Controllers\Api\ReferralController;
use Illuminate\Support\Facades\Route;

// Public API routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected API routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // Wallet routes
    Route::prefix('wallet')->group(function () {
        Route::get('/balance', [WalletController::class, 'balance']);
        Route::get('/transactions', [WalletController::class, 'transactions']);
        Route::post('/topup-request', [WalletController::class, 'requestTopup']);
        Route::get('/topup-requests', [WalletController::class, 'topupRequests']);
    });

    // Referral routes
    Route::prefix('referral')->group(function () {
        Route::get('/codes', [ReferralController::class, 'myCodes']);
        Route::post('/generate', [ReferralController::class, 'generate']);
        Route::get('/validate/{code}', [ReferralController::class, 'validateCode']);
    });
});

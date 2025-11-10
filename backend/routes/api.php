<?php

use App\Http\Controllers\Api\LogoutController;
use App\Http\Controllers\Api\SendOtpController;
use App\Http\Controllers\Api\VerifyOtpController;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Health check endpoint
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toISOString(),
        'version' => '1.0.0',
    ]);
});

// Authentication routes
Route::prefix('auth')->group(function () {
    Route::post('/send-otp', SendOtpController::class);
    Route::post('/verify-otp', VerifyOtpController::class);
});

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return UserResource::make($request->user());
    });

    Route::post('/logout', LogoutController::class);
});

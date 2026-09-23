<?php

use App\Domains\Catalog\Presentation\Http\Controllers\CategoryController;
use App\Domains\Catalog\Presentation\Http\Controllers\ProductController;
use App\Domains\Identity\Presentation\Http\Controllers\AuthController;
use App\Domains\Inventory\Presentation\Http\Controllers\InventoryController;
use App\Domains\Inventory\Presentation\Http\Controllers\StockMovementController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    // ==========================================
    // 1. Public Authentication Routes (Throttled)
    // ==========================================
    Route::prefix('auth')->middleware('throttle:5,1')->group(function () {
        Route::post('register', [AuthController::class, 'register'])->name('auth.register');
        Route::post('login', [AuthController::class, 'login'])->name('auth.login');
        Route::post('forgot-password', [AuthController::class, 'forgotPassword'])->name('password.email');
        Route::post('reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
    });

    // Email Verification Public Route (Signed URL)
    Route::get('email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])
        ->middleware(['signed'])
        ->name('verification.verify');

    // ==========================================
    // 2. Public Catalog Routes
    // ==========================================
    Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::get('/categories/{category}', [CategoryController::class, 'show'])->name('categories.show');
    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');

    // ==========================================
    // 3. Authenticated Routes (Sanctum Only)
    // Allows unverified users to check profile/status, log out, refresh, or resend verification
    // ==========================================
    Route::prefix('auth')->middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout'])->name('auth.logout');
        Route::get('me', [AuthController::class, 'me'])->name('auth.me');
        Route::post('refresh', [AuthController::class, 'refresh'])->name('auth.refresh'); 
        
        Route::post('email/verification-notification', [AuthController::class, 'sendVerificationEmail'])
            ->middleware(['throttle:6,1'])
            ->name('verification.send');
    });

    // ==========================================
    // 4. Authenticated & Verified Routes (Sanctum + Verified)
    // Restricts sensitive mutations until email verification is complete
    // ==========================================
    Route::prefix('auth')->middleware(['auth:sanctum', 'verified'])->group(function () {
        Route::patch('profile', [AuthController::class, 'updateProfile'])->name('auth.profile.update');
        Route::patch('password', [AuthController::class, 'updatePassword'])->name('auth.password.update');
        Route::delete('profile', [AuthController::class, 'destroy'])->name('auth.profile.destroy');
    });

    // ==========================================
    // 5. Admin Management Routes (Sanctum + Admin)
    // ==========================================
    Route::prefix('admin')->middleware(['auth:sanctum', 'admin'])->group(function () {

        Route::post('categories', [CategoryController::class, 'store'])->name('admin.categories.store');
        Route::patch('categories/{category}', [CategoryController::class, 'update'])->name('admin.categories.update');
        Route::delete('categories/{category}', [CategoryController::class, 'destroy'])->name('admin.categories.destroy');

        Route::post('products', [ProductController::class, 'store'])->name('admin.products.store');
        Route::patch('products/{product}', [ProductController::class, 'update'])->name('admin.products.update');
        Route::delete('products/{product}', [ProductController::class, 'destroy'])->name('admin.products.destroy');

        Route::get('/inventories', [InventoryController::class, 'index'])->name('inventories.index');
        Route::get('/inventories/{inventory}', [InventoryController::class, 'show'])->name('inventories.show');
        Route::patch('inventories/{inventory}', [InventoryController::class, 'update'])->name('admin.inventories.update');
        Route::post('inventories/{inventory}/adjust', [InventoryController::class, 'adjust'])
        ->name('admin.inventories.adjust');
        
        Route::get('inventories/{inventory}/movements', [StockMovementController::class, 'index'])
        ->name('admin.inventories.movements.index');
        
    });

});
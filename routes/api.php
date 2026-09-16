<?php

use App\Domains\Catalog\Presentation\Http\Controllers\CategoryController;
use App\Domains\Identity\Presentation\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;


Route::prefix('v1')->group(function () {

    //public routes
    Route::prefix('auth')->middleware('throttle:5,1')->group(function () {
        Route::post('register', [AuthController::class, 'register'])->name('auth.register');
        Route::post('login', [AuthController::class, 'login'])->name('auth.login');
    });

    //public routes
    Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::get('/categories/{category}', [CategoryController::class, 'show'])->name('categories.show');

    //auth - Sanctum
    Route::prefix('auth')->middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout'])->name('auth.logout');
        Route::get('me', [AuthController::class, 'me'])->name('auth.me');
        Route::patch('profile', [AuthController::class, 'updateProfile'])->name('auth.profile.update');
        Route::put('password', [AuthController::class, 'updatePassword'])->name('auth.password.update');
        Route::delete('profile', [AuthController::class, 'destroy'])->name('auth.profile.destroy');
        Route::post('refresh', [AuthController::class, 'refresh'])->name('auth.refresh'); 
    });

    //admin routes
    Route::prefix('admin')->middleware(['auth:sanctum', 'admin'])->group(function () {
        Route::post('categories', [CategoryController::class, 'store'])->name('admin.categories.store');
        Route::patch('categories/{category}', [CategoryController::class, 'update'])->name('admin.categories.update');
        Route::delete('categories/{category}', [CategoryController::class, 'destroy'])->name('admin.categories.destroy');
    });
});




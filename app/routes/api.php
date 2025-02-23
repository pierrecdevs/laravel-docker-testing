<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use Illuminate\Support\Facades\Route;

Route::apiResource('products', ProductController::class);


// Unprotected Auth Routes
Route::post('/login', [AuthController::class, 'login'])->name('auth.login');
Route::post('/register', [AuthController::class, 'register'])->name('auth.register');
Route::get('/', [AuthController::class, 'index'])->name('auth.root');

// Protected Auth Routes
Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::delete('/logout', [AuthController::class, 'logout'])
        ->name('auth.logout');

    Route::get('/user', [AuthController::class, 'user'])
        ->name('auth.user');
});

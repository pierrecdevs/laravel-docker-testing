<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/about', function () {
    return 'About';
});

Route::get('/login', function () {
    return response()->json(['status' => 200, 'message' => 'OK']);
})->name('login');

Route::get('/help', function () {});

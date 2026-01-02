<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// This file is for true API routes (token-based authentication)
// Most of your SPA routes should be in web.php with 'web' middleware for CSRF protection

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});

<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TravelRequestController;
use App\Http\Controllers\UserController;

// Public read endpoints
Route::get('/travel-requests', [TravelRequestController::class, 'index']);
Route::get('/travel-requests/{id}', [TravelRequestController::class, 'show']);

// Protected endpoints: require authenticated user (and admin where applicable)
Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::post('/travel-requests', [TravelRequestController::class, 'store']);
    Route::put('/travel-requests/{id}/status', [TravelRequestController::class, 'updateStatus']);
});

Route::group(['prefix' => '/user'], function () {
    Route::post('/login', [UserController::class, 'login']);
    Route::post('/create', [UserController::class, 'store']);
});

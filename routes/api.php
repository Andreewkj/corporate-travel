<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TravelRequestController;

Route::post('/travel-requests', [TravelRequestController::class, 'store']);
Route::get('/travel-requests', [TravelRequestController::class, 'index']);
Route::get('/travel-requests/{id}', [TravelRequestController::class, 'show']);

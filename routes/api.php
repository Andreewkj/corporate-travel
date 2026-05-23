<?php
declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TravelRequestController;

Route::post('/travel-requests', [TravelRequestController::class, 'store']);
Route::get('/travel-requests', [TravelRequestController::class, 'index']);
Route::get('/travel-requests/{id}', [TravelRequestController::class, 'show']);
Route::put('/travel-requests/{id}/status', [TravelRequestController::class, 'updateStatus']);
Route::post('/travel-requests/{id}/cancel', [TravelRequestController::class, 'cancelRequest']);

use App\Http\Controllers\UserController;

Route::group(['prefix' => '/user'], function () {
	Route::post('/login', [UserController::class, 'login']);
	Route::post('/create', [UserController::class, 'store']);
});

Route::group(['middleware' => ['auth:sanctum']], function () {
	// future protected routes
});

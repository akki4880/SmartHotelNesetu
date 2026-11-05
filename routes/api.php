<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\HousekeepingController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/housekeeping', [HousekeepingController::class, 'index']);

    Route::patch('/housekeeping/room-status', [
        HousekeepingController::class,
        'updateRoomStatus',
    ]);
});

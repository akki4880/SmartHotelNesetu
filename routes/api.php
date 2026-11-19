<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\HousekeepingController;
use App\Http\Controllers\Api\GuestController;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\PersonController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/change-password', [AuthController::class, 'changePassword']);
    Route::post('/update-profile', [AuthController::class, 'updateProfile']);
    Route::delete('/delete-account', [AuthController::class, 'deleteAccount']);
    Route::post('/logout', [AuthController::class, 'logout']);


    Route::get('/guest', [GuestController::class, 'index']);

    Route::apiResource('/persons', PersonController::class);
    Route::get('/person/search', [PersonController::class, 'search']);

    Route::get('/housekeeping', [HousekeepingController::class, 'index']);
    Route::patch('/housekeeping/room-status', [
        HousekeepingController::class,
        'updateRoomStatus',
    ]);
});

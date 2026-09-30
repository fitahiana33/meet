<?php

use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Meeting\MeetingController;
use App\Http\Controllers\User\UserController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');

Route::middleware('auth:sanctum')->group(function (): void {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/meetings', [MeetingController::class, 'index']);
    Route::post('/meetings', [MeetingController::class, 'store']);
    Route::get('/meetings/{meeting}', [MeetingController::class, 'show']);
    Route::post('/meetings/{meeting}/join-requests', [MeetingController::class, 'requestJoin']);
    Route::get('/meetings/{meeting}/join-requests', [MeetingController::class, 'requests']);
    Route::patch('/meetings/{meeting}/join-requests/{joinRequest}', [MeetingController::class, 'updateRequest']);
    Route::post('/meetings/{meeting}/start', [MeetingController::class, 'start']);
    Route::post('/meetings/{meeting}/access-token', [MeetingController::class, 'accessToken']);
    Route::post('/meetings/{meeting}/end', [MeetingController::class, 'end']);

    Route::middleware('admin')->group(function (): void {
        Route::apiResource('users', UserController::class);
        Route::apiResource('roles', RoleController::class);
    });
});

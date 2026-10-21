<?php

use App\Http\Controllers\Admin\DeclarantController;
use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\EntityController;
use App\Http\Controllers\Admin\GrievanceController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SectorController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Meeting\MeetingController;
use App\Http\Controllers\User\UserController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink'])->middleware('throttle:5,1');
Route::post('/reset-password', [PasswordResetController::class, 'reset'])->middleware('throttle:10,1');

Route::middleware('auth:sanctum')->group(function (): void {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::apiResource('departments', DepartmentController::class);
    Route::apiResource('sectors', SectorController::class);
    Route::get('/entities', [EntityController::class, 'index']);
    Route::post('/entities', [EntityController::class, 'store']);
    Route::get('/entities/{entity}', [EntityController::class, 'show']);
    Route::put('/entities/{entity}', [EntityController::class, 'update']);
    Route::patch('/entities/{entity}', [EntityController::class, 'update']);
    Route::delete('/entities/{entity}', [EntityController::class, 'destroy']);
    Route::get('/declarants', [DeclarantController::class, 'index']);
    Route::get('/declarants/{declarant}/photo', [DeclarantController::class, 'photo']);
    Route::get('/grievances/dashboard', [GrievanceController::class, 'dashboard']);
    Route::apiResource('grievances', GrievanceController::class);
    Route::apiResource('roles', RoleController::class);
    Route::delete('grievances/{grievance}/documents/{document}', [GrievanceController::class, 'destroyDocument']);

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
    });
});

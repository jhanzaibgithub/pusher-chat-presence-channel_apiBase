<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ChatController;

Route::post('/register/user', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:api')->post('/logout', [AuthController::class, 'logout']);

Route::middleware('auth:api')->group(function () {
    Route::get('/users', [ChatController::class, 'users']);
    Route::get('/messages/{user}', [ChatController::class, 'getMessages']);
    Route::post('/messages/send', [ChatController::class, 'send']);
});

<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\GroupController;
use App\Http\Controllers\Api\GroupChatController;
use App\Http\Controllers\Api\GroupMessageSent;
use App\Http\Controllers\Api\ChatController;

Route::post('/register/user', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:api')->post('/logout', [AuthController::class, 'logout']);

Route::middleware('auth:api')->group(function () {
    Route::post('/groups', [GroupController::class, 'create']);
    Route::get('/groups', [GroupController::class, 'myGroups']);
    Route::post('/groups/{group}/add-users', [GroupController::class, 'addUsers']);
    Route::get('/groups/{group}/messages', [GroupController::class, 'getGroupMessages']);
    
    Route::get('/users', [ChatController::class, 'users']);
    Route::get('/messages/{user}', [ChatController::class, 'getMessages']);
    Route::post('/messages/send', [ChatController::class, 'send']);
});

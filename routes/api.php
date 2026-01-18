<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Broadcast;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\PhotoController;
use App\Http\Controllers\Api\MatchController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\InterestController;
use App\Http\Controllers\Api\FeedbackController;

Broadcast::routes(['middleware' => ['auth:sanctum']]);

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);


Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::put('/profile', [UserController::class, 'updateProfile']);
    Route::get('/users/{id}', [UserController::class, 'show']);

    Route::prefix('photos')->group(function () {
        Route::get('/', [PhotoController::class, 'index']);
        Route::post('/upload', [PhotoController::class, 'upload']);
        Route::delete('/{id}', [PhotoController::class, 'destroy']);
        Route::put('/{id}/set-primary', [PhotoController::class, 'setPrimary']);
    });

    Route::get('/discover', [MatchController::class, 'discover']);
    Route::post('/swipe', [MatchController::class, 'swipe']);
    Route::get('/matches', [MatchController::class, 'matches']);
    Route::delete('/matches/{matchId}', [MatchController::class, 'unmatch']);

    Route::get('/conversations', [MessageController::class, 'conversations']);
    Route::get('/matches/{matchId}/messages', [MessageController::class, 'index']);
    Route::post('/matches/{matchId}/messages', [MessageController::class, 'store']);
    Route::post('/matches/{matchId}/messages/read', [MessageController::class, 'markAsRead']);

    Route::get('/interests', [InterestController::class, 'index']);
    Route::get('/interests/my', [InterestController::class, 'myInterests']);
    Route::post('/interests/attach', [InterestController::class, 'attach']);

    // Ajoutez ces lignes dans le groupe auth:sanctum
Route::post('/feedback', [FeedbackController::class, 'store']);


 Route::middleware('admin')->group(function () {
        Route::get('/feedback', [FeedbackController::class, 'index']);
        Route::put('/feedback/{id}/status', [FeedbackController::class, 'updateStatus']);
    });
});

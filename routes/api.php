<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\ProfileController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    Route::middleware('role:admin')->group(function () {
        Route::post('/profiles', [ProfileController::class, 'store']);
        Route::post('/profiles/{profile}/comments', [CommentController::class, 'store']);
        Route::put('/profiles/{profile}', [ProfileController::class, 'update']);
        Route::delete('/profiles/{profile}', [ProfileController::class, 'destroy']);
    });
});

Route::get('/profiles', [ProfileController::class, 'index']);
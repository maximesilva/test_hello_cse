<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;

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

// Routes publiques
Route::post('/login', [AuthController::class, 'login']);

// Routes protégées par authentification
Route::middleware('auth:sanctum')->group(function () {
    // Routes accessibles à tous les utilisateurs authentifiés
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // Routes accessibles uniquement aux administrateurs
    Route::middleware('role:admin')->group(function () {
        // Ajoutez ici toutes vos routes qui nécessitent le rôle admin
        Route::get('/admin/dashboard', function () {
            return response()->json(['message' => 'Bienvenue dans le dashboard admin']);
        });
    });
}); 
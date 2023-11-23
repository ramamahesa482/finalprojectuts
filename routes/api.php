<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PatientsController;

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
Route::post('/register', [AuthController::class, 'register']);

Route::middleware(['jwt'])->group(function() {
    Route::get('/patients', [PatientsController::class, 'index']);
    Route::post('/patients', [PatientsController::class, 'store']);
    Route::get('/patients/{id}', [PatientsController::class, 'show']);
    Route::put('/patients/{id}', [PatientsController::class, 'update']);
    Route::delete('/patients/{id}', [PatientsController::class, 'destroy']);
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

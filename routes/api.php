<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
//API/Auth Controllers
use App\Http\Controllers\API\Auth\LoginController;
use App\Http\Controllers\API\Auth\RegisterController;
use App\Http\Controllers\API\Auth\LogoutController;
use App\Http\Controllers\API\Auth\MeController;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');



//API/Auth Routes
Route::prefix('auth')->group(function () {

    Route::post('/register', RegisterController::class);
    Route::post('/login', LoginController::class);

    Route::middleware('auth:sanctum')->group(function () {

        Route::get('/me', MeController::class);

        Route::post('/logout', LogoutController::class);

    });

});
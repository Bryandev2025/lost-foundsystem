<?php

/* |-------------------------------------------------------------------------- | API Routes |-------------------------------------------------------------------------- | | Here is where you can register API routes for your application. These | routes are loaded by the RouteServiceProvider within a group which | is assigned the "api" middleware group. Enjoy building your API! | */

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


//API/Auth Controllers
use App\Http\Controllers\API\Auth\LoginController;
use App\Http\Controllers\API\Auth\RegisterController;
use App\Http\Controllers\API\Auth\LogoutController;
use App\Http\Controllers\API\Auth\MeController;


//API/Controllers
use App\Http\Controllers\API\ItemsController;
use App\Http\Controllers\API\ClaimsController;
use App\Http\Controllers\API\MatchesController;


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

        }
        );
    });





//API/Items Routes
Route::middleware('auth:sanctum')->group(function () {
    /**
     * ITEMS (Lost/Found reports)
     * user/staff/admin can create reports and view lists
     */
    Route::get('/items', [ItemsController::class , 'index']);
    Route::post('/items', [ItemsController::class , 'store']);
    Route::get('/items/{item}', [ItemsController::class , 'show']);
    Route::put('/items/{item}', [ItemsController::class , 'update']);
    Route::delete('/items/{item}', [ItemsController::class , 'destroy']);
    Route::get('/my/items', [ItemsController::class , 'myItems']);

    /**
     * CLAIMS
     * user: submit + view own
     * staff/admin: review/approve/deny/release + list all
     */
    Route::post('/claims', [ClaimsController::class , 'store']);
    Route::get('/my/claims', [ClaimsController::class , 'myClaims']);
    Route::get('/claims/{claim}', [ClaimsController::class , 'show']);

    Route::middleware('role:staff,admin')->group(function () {
            Route::get('/claims', [ClaimsController::class , 'index']);
            Route::put('/claims/{claim}/approve', [ClaimsController::class , 'approve']);
            Route::put('/claims/{claim}/deny', [ClaimsController::class , 'deny']);
            Route::put('/claims/{claim}/release', [ClaimsController::class , 'release']);

            /**
     * MATCHES (Staff/Admin)
     */
            Route::get('/matches', [MatchesController::class , 'index']);
            Route::post('/matches', [MatchesController::class , 'store']);
        }
        );
    });
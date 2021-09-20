<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DeskController;
use App\Http\Controllers\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

//public routs
Route::get('/desks/search/{position}',[DeskController::class, 'search']);
Route::get('/desks/{id}',[DeskController::class, 'show']);
Route::get('/desks',[DeskController::class, 'index']);
Route::get("/desksavailable",[DeskController::class, 'available']);

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);



//Protected routes
Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::post('/desks',[DeskController::class, 'store']);
    Route::put('/desks/{id}',[DeskController::class, 'update']);
    Route::delete('/desks/{id}',[DeskController::class, 'destroy']);
    Route::post('/logout', [AuthController::class, 'logout']);
});
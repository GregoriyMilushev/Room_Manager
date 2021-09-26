<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DeskController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoomController;

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
Route::get('/desks/{id}',[DeskController::class, 'show'])->middleware('admin');
Route::get('/desks',[DeskController::class, 'index']);

Route::get("/desksavailable",[UserController::class, 'available']);

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::resource('rooms', RoomController::class);

//Protected routes
Route::group(['middleware' => ['auth:sanctum']], function () {

    Route::post('/logout', [AuthController::class, 'logout']);

    Route::group(['prefix' => 'admin', 'as' => 'admin.', 'middleware' => 'admin'],function () {
        Route::post('/desks',[DeskController::class, 'store']);
        Route::put('/desks/{id}',[DeskController::class, 'update']);
        Route::delete('/desks/{id}',[DeskController::class, 'destroy']);
        
    });
    
    Route::put("/user/rent/{desk_id}",[UserController::class, 'rent']);
    Route::get("/user/price",[UserController::class, 'price']);
});

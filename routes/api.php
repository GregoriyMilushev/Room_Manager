<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
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
// Auth::routes();
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout']);

Route::resource('users', UserController::class);
Route::patch("/users/rent/{desk_id}",[UserController::class, 'rent']);
Route::get("/users/price",[UserController::class, 'price']);

Route::resource('rooms', RoomController::class);

Route::resource('desks', DeskController::class);
Route::get('/desks/search/{position}',[DeskController::class, 'search']);
//TODO: POST:'/desks/available', but cant access with GET:'/desks/available' ???
Route::post('/desks/available',[DeskController::class, 'available'])->name('available');


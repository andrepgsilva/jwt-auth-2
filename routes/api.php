<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PostCategoryController;
use App\Http\Controllers\PostController;
use Illuminate\Support\Facades\Route;

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

Route::group(['prefix' => '/auth'], function() {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
});

Route::get('/post-categories', [PostCategoryController::class, 'index'])->middleware('auth.jwt');

Route::get('/post', [PostController::class, 'index'])->middleware('auth.jwt');
Route::post('/post', [PostController::class, 'store'])->middleware('auth.jwt');

<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProjectController;
use App\Models\Orientation;
use App\Models\Tag;
use App\Models\User;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/users', function () {
    return User::all();
});

Route::prefix('/project')->group(function () {
    Route::get('/all', [ProjectController::class, 'index']);
    Route::post('/submit', [ProjectController::class, 'store']);
});

Route::get('/orientation/all', function () {
    return Orientation::all();
});

Route::get('/tag/all', function () {
    return Tag::all();
});

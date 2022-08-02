<?php

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

Route::prefix('/project')->group(function () {
    Route::get('/all', [ProjectController::class, 'getAll']);
    Route::get('/preffered', [ProjectController::class, 'getPreffered']);
    Route::post('/submit', [ProjectController::class, 'store']);
    Route::post('/add-preference', [ProjectController::class, 'addPreference']);
    Route::post('/remove-preference', [ProjectController::class, 'removePreference']);
    Route::post('/add-attribution', [ProjectController::class, 'addAttribution']);
    Route::post('/remove-attribution', [ProjectController::class, 'removeAttribution']);
});

Route::get('/orientation/all', function () {
    return Orientation::all();
});

Route::get('/tag/all', function () {
    return Tag::all();
});

Route::get('/user/all', function () {
    return User::orderBy('lastname')->get();
});

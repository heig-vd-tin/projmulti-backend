<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\UserController;
use App\Models\Orientation;
use App\Models\Project;
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

Route::middleware('auth:api')->group(function () {
    Route::prefix('/project')->controller(ProjectController::class)->group(function () {
        Route::get('/all', 'getAll')->can('viewAll', Project::class);
        Route::get('/preffered', 'getPreffered');
        Route::get('/assigned', 'getAssigned');

        Route::post('/submit', 'store');
        Route::post('/add-preference', 'addPreference');
        Route::post('/remove-preference', 'removePreference');
        Route::post('/add-attribution', 'addAttribution');
        Route::post('/remove-attribution', 'removeAttribution');
    });

    Route::prefix('/user')->controller(UserController::class)->group(function () {
        Route::get('', 'getMyself');
        Route::get('/all', 'getAll');
        Route::get('/unassigned', 'getUnassigned');
    });

    Route::get('/orientation/all', function () {
        return Orientation::all();
    });

    Route::get('/tag/all', function () {
        return Tag::all();
    });
});

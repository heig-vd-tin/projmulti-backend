<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AssignmentController;
use App\Models\Orientation;
use App\Models\Domain;
use App\Models\Tag;

use Illuminate\Contracts\Auth\UserProvider;
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
        Route::get('/all', 'getAll');
        Route::get('/owned', 'getOwned');
        Route::get('/preferred', 'getPreferred');
        Route::get('/assigned', 'getAssigned');
        
        Route::post('/select', 'selectProject');

        Route::post('/create', 'createProject');
        Route::post('/edit', 'editProject');
        Route::post('/add-preference', 'addPreference');
        Route::post('/remove-preference', 'removePreference');
        Route::post('/add-assignment', 'addAssignment');
        Route::post('/remove-assignment', 'removeAssignment');
    });

    Route::prefix('/user')->controller(UserController::class)->group(function () {
        Route::get('/me', 'getMyself');
        Route::get('/all', 'getAll');
        Route::get('/all-students', 'getAllStudents');
        Route::get('/unassigned', 'getUnassigned');
    });

    Route::prefix('/assignment')->controller(AssignmentController::class)->group(function () {
        Route::get('/auto-select', 'selectProject');
        Route::get('/auto-affect', 'autoAffect');
        Route::get('/rand', 'rand');
        Route::get('/{id}', 'get');
    });

    Route::get('/orientation/all', function () {
        return Orientation::orderBy('faculty_acronym')->orderBy('acronym')->get();
    });

    Route::get('/domain/all', function () {
        return Domain::all();
    });

    Route::get('/tag/all', function () {
        return Tag::orderBy('name')->get();
    });

    // tmz : login debug
    Route::get('/logid/{id}', function ($id) {
        $u = User::find($id);
        Session::put('user_id', $id);
        Auth::setUser($u);
    });

    Route::get('/loguser/{username}', function ($username) {
        $u = User::where('username', $username)->first();
        if( $u != null ) {
            Auth::setUser($u);
        }
    });
});

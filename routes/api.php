<?php

use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\UserController;
use App\Models\Domain;
use App\Models\Orientation;
use App\Models\Tag;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->group(function () {
    // Toujours accessibles, pour que le front puisse afficher « plateforme fermée ».
    Route::get('/user/me', [UserController::class, 'getMyself']);
    Route::get('/settings', [SettingController::class, 'show']);

    Route::middleware('phase')->group(function () {
        Route::prefix('/project')->controller(ProjectController::class)->group(function () {
            Route::get('/all', 'getAll');
            Route::get('/alld', 'getAllWithoutDescription');
            Route::get('/getdesc/{id}', 'getProject');
            Route::get('/owned', 'getOwned');
            Route::get('/preferred', 'getPreferred');
            Route::get('/assigned', 'getAssigned');
            Route::get('/{id}/pdf', 'exportPdf')->whereNumber('id');

            Route::post('/create', 'createProject');
            Route::post('/edit', 'editProject');
            Route::post('/{id}/duplicate', 'duplicateProject')->whereNumber('id');
            Route::post('/{id}/propose', 'proposeProject')->whereNumber('id');
            Route::post('/add-preference', 'addPreference');
            Route::post('/remove-preference', 'removePreference');

            Route::middleware('role:admin')->group(function () {
                Route::post('/select', 'selectProject');
                Route::post('/lock', 'lockProject');
                Route::post('/add-assignment', 'addAssignment');
                Route::post('/remove-assignment', 'removeAssignment');
            });
        });

        Route::get('/orientation/all', fn () => Orientation::orderBy('faculty_acronym')->orderBy('acronym')->get());
        Route::get('/domain/all', fn () => Domain::all());
        Route::get('/tag/all', fn () => Tag::orderBy('name')->get());

        Route::middleware('role:admin')->group(function () {
            Route::prefix('/user')->controller(UserController::class)->group(function () {
                Route::get('/all', 'getAll');
                Route::get('/all-students', 'getAllStudents');
                Route::get('/unassigned', 'getUnassigned');
                Route::post('/{id}/flags', 'updateFlags')->whereNumber('id');
            });

            Route::prefix('/assignment')->controller(AssignmentController::class)->group(function () {
                Route::get('/calcul-match', 'calculMatch');
                Route::get('/auto-select', 'selectProject');
                Route::get('/auto-affect', 'autoAffect');
                Route::get('/save-affect/{source}', 'copyAssignments')->whereNumber('source');
                Route::get('/get-all', 'getAll');
                Route::post('/load-data', 'loadData');
                Route::post('/solve', 'solve');
                Route::get('/get-export', 'getExportData');
                Route::get('/{id}', 'get')->whereNumber('id');
            });

            Route::put('/settings', [SettingController::class, 'update']);
            Route::post('/settings/new-year', [SettingController::class, 'newYear']);
            Route::get('/settings/domain-mapping', [SettingController::class, 'domainMapping']);
            Route::put('/settings/domain-mapping', [SettingController::class, 'updateDomainMapping']);
        });
    });
});

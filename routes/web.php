<?php

use Illuminate\Support\Facades\Route;

// Application Vue (mode historique) : toute URL hors API renvoie le index.html compilé.
Route::get('/{any?}', function () {
    $index = public_path('index.html');
    abort_unless(is_file($index), 404, 'Front non compilé');
    return response()->file($index, ['Cache-Control' => 'no-cache, no-store, must-revalidate']);
})->where('any', '^(?!api/).*$');

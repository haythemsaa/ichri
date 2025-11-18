<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| This is an API-only application. Web routes are minimal.
|
*/

Route::get('/', function () {
    return response()->json([
        'success' => true,
        'message' => 'ichri.tn API v2.0',
        'documentation' => '/api/documentation',
    ]);
});

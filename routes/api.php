<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GetcourseController;
use App\Http\Controllers\BizonController;


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

Route::controller(GetcourseController::class)->group(function () {

    Route::prefix('getcourse')->group(function () {

        Route::post('/forms', 'forms');
        Route::get('/orders', 'orders');

        Route::get('/webinars/hook', 'hook');
        Route::post('/webinars/send', 'send');
    });
});

Route::controller(BizonController::class)->group(function () {

    Route::post('/bizon/send', 'send');
    Route::post('/bizon/hook', 'hook');
});

<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\SubCategoryController;
use Illuminate\Http\Request;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::controller(CategoryController::class)->group(function () {
    Route::get('/index-category', 'index');
    Route::post('/create-category', 'create');
    Route::get('/show-category/{id}', 'show');
    Route::post('/update-category', 'update');
    Route::get('/destroy-category/{id}', 'destroy');
});
Route::controller(SubCategoryController::class)->group(function () {
    Route::get('/index-sub-category', 'index');
    Route::post('/create-sub-category', 'create');
    Route::get('/show-sub-category/{id}', 'show');
    Route::post('/update-sub-category', 'update');
    Route::get('/destroy-sub-category/{id}', 'destroy');
});

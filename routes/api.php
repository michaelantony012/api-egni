<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\PurchasingController;
use App\Http\Controllers\SubCategoryController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\DocumentFlowController;
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
Route::controller(SupplierController::class)->group(function () {
    Route::get('/index-supplier', 'index');
    Route::post('/create-supplier', 'create');
    Route::get('/show-supplier/{id}', 'show');
    Route::post('/update-supplier', 'update');
    Route::get('/destroy-supplier/{id}', 'destroy');
});
Route::controller(LocationController::class)->group(function () {
    Route::get('/index-location', 'index');
    Route::post('/create-location', 'create');
    Route::get('/show-location/{id}', 'show');
    Route::post('/update-location', 'update');
    Route::get('/destroy-location/{id}', 'destroy');
});
Route::controller(PurchasingController::class)->group(function () {
    Route::get('/index-purchasing', 'index');
    Route::post('/create-supplier', 'create');
    Route::get('/show-supplier/{id}', 'show');
    Route::post('/update-supplier', 'update');
    Route::get('/destroy-supplier/{id}', 'destroy');
});
Route::controller(DocumentFlowController::class)->group(function () {
    Route::get('/index-documentflow', 'index');
    Route::post('/create-documentflow', 'create');
    // Route::get('/show-supplier/{id}', 'show');
    Route::post('/update-documentflow', 'update');
    // Route::get('/destroy-supplier/{id}', 'destroy');
});

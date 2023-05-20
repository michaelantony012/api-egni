<?php

use App\Http\Controllers\AdjustmentController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DocFlowController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PurchasingController;
use App\Http\Controllers\SubCategoryController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\DocumentFlowController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\OutgoingController;
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
Route::controller(ProductController::class)->group(function () {
    Route::get('/index-product', 'index');
    Route::get('/index-opt-product', 'optionIndex');
    Route::post('/search-product', 'search');
    Route::post('/create-product', 'create');
    Route::get('/show-product/{id}', 'show');
    Route::post('/update-product', 'update');
    Route::get('/destroy-product/{id}', 'destroy');
});
Route::controller(OutgoingController::class)->group(function () {
    Route::get('/index-outgoing', 'index');
    Route::post('/create-outgoing', 'create');
    Route::get('/show-outgoing/{id}', 'show');
    Route::post('/update-outgoing', 'update');
    Route::get('/destroy-outgoing/{id}', 'destroy');
    Route::post('/docflow-outgoing', 'docflow');
});
Route::controller(AdjustmentController::class)->group(function () {
    Route::get('/index-adjustment', 'index');
    Route::post('/create-adjustment', 'create');
    Route::get('/show-adjustment/{id}', 'show');
    Route::post('/update-adjustment', 'update');
    Route::get('/destroy-adjustment/{id}', 'destroy');
    Route::post('/docflow-adjustment', 'docflow');
});
Route::controller(PurchasingController::class)->group(function () {
    Route::get('/index-purchasing', 'index');
    Route::post('/create-purchasing', 'create');
    Route::get('/show-purchasing/{id}', 'show');
    Route::post('/update-purchasing', 'update');
    Route::get('/destroy-purchasing/{id}', 'destroy');
    Route::post('/docflow-purchasing', 'docflow');
});
Route::controller(DocFlowController::class)->group(function () {
    Route::get('/index-document', 'index');
    Route::post('/create-document', 'create');
    Route::get('/show-document/{id}', 'show');
    Route::post('/update-document', 'update');
    Route::get('/destroy-document/{id}', 'destroy');
    Route::get('/oeditor-document/{id}/{type}', 'openEditor');
    Route::get('/geditor-document', 'getDataEditor')->name('data.editor');
    Route::post('/ueditor-document', 'updateEditor')->name('update.editor');
    Route::get('/update-flow/{id}', 'updateFlow');
});
Route::controller(UserController::class)->group(function () {
    Route::get('/index-user', 'index');
    Route::post('/create-user', 'create');
    Route::get('/show-user/{id}', 'show');
    Route::post('/update-user', 'update');
    Route::get('/destroy-user/{id}', 'destroy');
});
Route::controller(CustomerController::class)->group(function () {
    Route::get('/index-customer', 'index');
    Route::post('/create-customer', 'create');
    Route::get('/show-customer/{id}', 'show');
    Route::post('/update-customer', 'update');
    Route::get('/destroy-customer/{id}', 'destroy');
});

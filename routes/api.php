<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\APIController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\OtherController;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\DocFlowController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\OutgoingController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\AdjustmentController;
use App\Http\Controllers\PaymentLogController;
use App\Http\Controllers\PurchasingController;
use App\Http\Controllers\SubCategoryController;
use App\Http\Controllers\DocumentFlowController;
use App\Http\Controllers\CashierPayoutController;
use App\Http\Controllers\PaymentMethodController;
use App\Http\Controllers\BeginningStockController;

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

Route::post('/sanctum/token', 'App\Http\Controllers\APIController@create_token');
Route::get('/index-location', [LocationController::class, 'index']);
Route::middleware('auth:sanctum')->group(function () {
    // return $request->user();
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

        Route::post('/create-location', 'create');
        Route::get('/show-location/{id}', 'show');
        Route::post('/update-location', 'update');
        Route::get('/destroy-location/{id}', 'destroy');
    });
    Route::controller(ProductController::class)->group(function () {
        Route::post('/index-product', 'index');
        Route::get('/index-opt-product', 'optionIndex');
        Route::post('/search-product', 'search');
        Route::post('/create-product', 'create');
        Route::get('/show-product/{id}', 'show');
        Route::post('/update-product', 'update');
        Route::get('/destroy-product/{id}', 'destroy');
        Route::post('/masscreate-product', 'massCreate');
        Route::post('/get-stockcard', 'stockCard');
        Route::post('/get-stockcardall', 'stockCardAll');
        Route::post('/get-stockmutationdetail', 'stockMutationDetail');
        Route::post('/update-statproduct','updateEnabledDisabled');
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
    Route::controller(SalesController::class)->group(function () {
        Route::post('/index-sales', 'index');
        Route::post('/create-sales', 'create');
        Route::get('/show-sales/{id}', 'show');
        Route::post('/update-sales', 'update');
        Route::get('/destroy-sales/{id}', 'destroy');
        Route::post('/docflow-sales', 'docflow');
        Route::post('/create-return-item', 'create_return_item');
        Route::post('/posting-log-transaction', 'postingLogTransaksi');
        Route::post('/crud-return', 'crud_return');
        Route::post('/report-aset-penjualan', 'report_aset_penjualan');

    });

    Route::controller(UserController::class)->group(function () {
        Route::get('/index-user', 'index');
        // Route::post('/create-user', 'create');
        Route::get('/show-user/{id}', 'show');
        Route::post('/update-user', 'update');
        Route::get('/destroy-user/{id}', 'destroy');
    });
    Route::controller(CustomerController::class)->group(function () {
        Route::get('/index-customer', 'index');
        Route::post('/create-customer', 'create');
        Route::get('/show-customer/{id}', 'show');
        Route::post('/update-customer', 'update');
        Route::post('/search-customer', 'search');
        Route::get('/destroy-customer/{id}', 'destroy');
    });
    Route::controller(BeginningStockController::class)->group(function () {
        Route::get('/index-beginningstock', 'index');
        Route::post('/create-beginningstock', 'create');
        Route::get('/show-beginningstock/{id}', 'show');
        Route::post('/update-beginningstock', 'update');
        Route::get('/destroy-beginningstock/{id}', 'destroy');
        Route::post('/docflow-beginningstock', 'docflow');
    });
    Route::controller(CashierPayoutController::class)->group(function () {
        Route::get('/index-cashierpayout', 'index');
        Route::post('/create-cashierpayout', 'create');
        Route::get('/show-cashierpayout/{id_user}', 'show');
        Route::post('/update-cashierpayout', 'update');
        Route::get('/destroy-cashierpayout/{id}', 'destroy');
        Route::post('/report-saldokasir', 'LapSaldoKasir');
    });
    Route::controller(PaymentMethodController::class)->group(function () {
        Route::get('/index-paymentmethod', 'index');
        Route::post('/create-paymentmethod', 'create');
        Route::get('/show-paymentmethod/{id}', 'show');
        Route::post('/update-paymentmethod', 'update');
        Route::get('/destroy-paymentmethod/{id}', 'destroy');
    });
    Route::controller(PaymentLogController::class)->group(function () {
        Route::get('/index-paymentlog', 'index');
        Route::post('/create-paymentlog', 'create');
        Route::get('/show-paymentlog/{id}', 'show');
        Route::post('/update-paymentlog', 'update');
        Route::get('/destroy-paymentlog/{id}', 'destroy');
    });
    Route::controller(OtherController::class)->group(function () {
        Route::get('/get-province', 'provinces');
        Route::get('/get-regency/{province_id}', 'regencies');
        Route::get('/get-district/{regency_id}', 'districts');
    });
});


Route::controller(UserController::class)->group(function () {
    Route::post('/create-user', 'create');
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
    Route::post('/logic-document', 'getFlowLogic');
    Route::post('/update-flow', 'updateFlow');
});

Route::controller(ReportController::class)->group(function () {
    // Route::post('/stock-keluar', 'stockKeluar');
    // Route::post('/omset', 'omset');
    Route::post('/reportdashboard', 'reportdashboard');
    Route::post('/reportdashboard2', 'reportdashboard2');
});
<?php

use App\Http\Controllers\AktivitasController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\InspectionController;
use App\Http\Controllers\LokasiController;
use App\Http\Controllers\ProductController;
use App\Http\Middleware\CheckTokenValid;

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::get('gender', [AuthController::class, 'gender']);
Route::post('email_verify', [AuthController::class, 'emailVerifed']);
Route::post('generate_code', [AuthController::class, 'generateCodeVerify']);
Route::post('forget_password', [AuthController::class, 'forgetPassword']);
Route::post('change_password', [AuthController::class, 'changePassword']);
Route::middleware([CheckTokenValid::class])->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::prefix('customer')->group(function () {
        // Route::get('/', [CustomerController::class, 'index']);        // GET /customer
        Route::post('add_customer', [CustomerController::class, 'store']);
        Route::post('update', [CustomerController::class, 'update']);
        Route::get('list_user', [CustomerController::class, 'listUser']);
        Route::get('detail_user', [CustomerController::class, 'detailUser']);
        Route::delete('delete_user', [CustomerController::class, 'destroy']); 
        Route::get('count_user', [CustomerController::class, 'countUser']); 
    });
     Route::prefix('location')->group(function () {
        Route::post('/create', [LokasiController::class, 'store']);
        Route::post('/update', [LokasiController::class, 'update']);
        Route::get('/list_lokasi', [LokasiController::class, 'listLokasi']);
        Route::post('/delete', [LokasiController::class, 'destroy']);
    });
    Route::prefix('aktivitas')->group(function() {
        Route::get('show_aktivitas', [AktivitasController::class, 'show']); 
    });
    Route::prefix('product')->group(function() {
        Route::post('add_product', [ProductController::class, 'store']); 
        Route::get('count_apar', [ProductController::class, 'count_apar']); 
    });
     Route::prefix('inspection')->group(function() {
        Route::post('add_inspection', [InspectionController::class, 'store']); 
        Route::post('update_inspection_schedule', [InspectionController::class, 'update']); 
        Route::post('delete_inspection_schedule', [InspectionController::class, 'destroy']); 
        Route::post('chage_status_inspection', [InspectionController::class, 'changeStatusInspection']); 
        Route::get('list_inspection', [InspectionController::class, 'question']); 
        Route::post('do_inspection', [InspectionController::class, 'inspectionApar']); 
        Route::get('list_inspection', [InspectionController::class, 'listInspection']); 
        Route::get('detail_inspection', [InspectionController::class, 'detailInspection']);
        Route::get('list_apar_not_inspected', [InspectionController::class, 'aparNotInspected']); 
        Route::get('list_apar_inspected', [InspectionController::class, 'aparInspected']); 
        // Route::get('count_apar', [ProductController::class, 'count_apar']); 
    });
});

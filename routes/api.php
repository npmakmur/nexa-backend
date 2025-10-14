<?php

use App\Http\Controllers\AktivitasController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\InspectionController;
use App\Http\Controllers\kopSuratController;
use App\Http\Controllers\LokasiController;
use App\Http\Controllers\PenawaranController;
use App\Http\Controllers\ProductController;
use App\Http\Middleware\CheckTokenValid;

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::get('gender', [AuthController::class, 'gender']);
Route::post('email_verify', [AuthController::class, 'emailVerifed']);
Route::post('generate_code', [AuthController::class, 'generateCodeVerify']);
Route::post('forget_password', [AuthController::class, 'forgetPassword']);
Route::post('change_password', [AuthController::class, 'passVerify']);
Route::post('set_password', [AuthController::class, 'setPassword']);
Route::get('inspection/download/{file}', [InspectionController::class, 'downloadAparReport']); 
Route::get('penawaran/download/{file}', [PenawaranController::class, 'download']);
Route::get('product/download/{file}', [ProductController::class, 'download']);
Route::get('product/detail_apar', [ProductController::class, 'detail_apar']); 

Route::middleware([CheckTokenValid::class])->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::prefix('customer')->group(function () {
        // Route::get('/', [CustomerController::class, 'index']);        // GET /customer
        Route::post('add_customer', [CustomerController::class, 'store']);
        Route::post('update', [CustomerController::class, 'update']);
        Route::post('update_foto_profile', [CustomerController::class, 'updateFotoProfile']);
        Route::post('update_pass_customer', [CustomerController::class, 'updatePassCustomer']);
        Route::get('list_user', [CustomerController::class, 'listUser']);
        Route::get('list_user_all', [CustomerController::class, 'listAllCustomer']);
        Route::get('detail_user', [CustomerController::class, 'detailUser']);
        Route::delete('delete_user', [CustomerController::class, 'destroy']); 
        Route::get('count_user', [CustomerController::class, 'countUser']); 
        Route::get('list_level', [CustomerController::class, 'listLevelUser']); 
        Route::get('kode_customer_list', [CustomerController::class, 'listKodeCustomer']); 
    });
     Route::prefix('location')->group(function () {
        Route::post('/create', [LokasiController::class, 'storeGedung']);
        Route::post('/update_building', [LokasiController::class, 'updateGedung']);
        Route::post('/create_location_point', [LokasiController::class, 'locationPoint']);
        Route::get('/list_lokasi', [LokasiController::class, 'listLokasi']);
        Route::post('/update_location_point', [LokasiController::class, 'updateTitik']);
        Route::post('/building/delete', [LokasiController::class, 'destroyGedung']);
        Route::post('/location_point/delete', [LokasiController::class, 'destroyTitik']);
        Route::get('/buildings/list', [LokasiController::class, 'apiListGedung']);
        Route::get('/list_location_point', [LokasiController::class, 'apiListLocationPoint']);
    });
    Route::prefix('aktivitas')->group(function() {
        Route::get('show_aktivitas', [AktivitasController::class, 'show']); 
    });
    Route::prefix('product')->group(function() {
        Route::post('add_product', [ProductController::class, 'store']); 
        Route::delete('delete_apar', [ProductController::class, 'deleteApar']); 
        Route::post('add_product_super_admin', [ProductController::class, 'storeSuperAdmin']); 
        Route::post('update_produk_super_admin', [ProductController::class, 'updateCustomerCodeByBatch']); 
        Route::get('list_apar_super_admin', [ProductController::class, 'getAparSuperAdmin']); 
        Route::get('count_apar', [ProductController::class, 'count_apar']); 
        Route::get('list_apar', [ProductController::class, 'list_apar']); 
        Route::get('apar_done_permount', [ProductController::class, 'apar_done_permount']); 
        Route::post('update_apar', [ProductController::class, 'update']); 
        Route::get('list_qr_apar', [ProductController::class, 'list_qr']); 
        Route::get('list_qr_apar_super_admin', [ProductController::class, 'listQrSuperAdmin']); 
        Route::get('count_apar_broken', [ProductController::class, 'countAparBroken']); 
        Route::get('count_apar_inspection', [ProductController::class, 'countApatInspection']); 
        Route::get('count_apar_inspection_done', [ProductController::class, 'presentaseInspectionDone']); 
        Route::get('apar_pdf', [ProductController::class, 'list_apar_pdf']); 
        Route::get('list_apar_broken', [ProductController::class, 'listAparBroken']); 
        Route::get('/download_file', [ProductController::class, 'downloadFile']);
    });
    Route::prefix('inspection')->group(function() {
        Route::post('add_inspection', [InspectionController::class, 'store']); 
        Route::post('update_inspection_schedule', [InspectionController::class, 'update']); 
        Route::post('delete_inspection_schedule', [InspectionController::class, 'destroy']); 
        Route::post('chage_status_inspection', [InspectionController::class, 'changeStatusInspection']); 
        Route::get('question', [InspectionController::class, 'question']); 
        Route::post('do_inspection', [InspectionController::class, 'inspectionApar']); 
        Route::get('list_inspection', [InspectionController::class, 'listInspection']); 
        Route::get('detail_inspection', [InspectionController::class, 'detailInspection']);
        Route::get('detail_inspection_apar', [InspectionController::class, 'detailInspectionApar']);
        Route::get('list_apar_not_inspected', [InspectionController::class, 'aparNotInspected']); 
        Route::get('list_apar_inspected', [InspectionController::class, 'aparInspected']); 
        Route::post('download_report', [InspectionController::class, 'generateAparReport']); 
        Route::get('part_broken_list', [InspectionController::class, 'precetagePartBroken']);
        Route::get('proggress', [InspectionController::class, 'proggress']); 
        Route::get('last_inspection_user', [InspectionController::class, 'lastInspection']); 
        Route::post('delete_inspection', [InspectionController::class, 'deleteAparInspection']); 
        // Route::get('count_apar', [ProductController::class, 'count_apar']); 
    });
    Route::prefix('penawaran')->group(function() {
        Route::get('/', [PenawaranController::class, 'index']);
        Route::get('/download_penawaran', [PenawaranController::class, 'ReportPenawaran']);
    });
    Route::prefix('kop_surat')->group(function() {
        Route::post('/insert_kop_surat', [kopSuratController::class, 'insertKopSurat']);
        Route::post('/update_kop_surat', [kopSuratController::class, 'updateKopSurat']);
        Route::get('/list_kop_surat', [kopSuratController::class, 'listKopSurat']);
    });

});

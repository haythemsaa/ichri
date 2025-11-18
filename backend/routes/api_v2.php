<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\KarnyController;
use App\Http\Controllers\Api\DigitalServiceController;
use App\Http\Controllers\Api\PromotionController;

/*
|--------------------------------------------------------------------------
| API Routes v2 - Nouvelles Fonctionnalités
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {

    // Karny - Carnet de Crédit Client
    Route::prefix('karny')->middleware('auth:api')->group(function () {
        Route::get('/customers', [KarnyController::class, 'customers']);
        Route::post('/customers', [KarnyController::class, 'createCustomer']);
        Route::get('/customers/{id}', [KarnyController::class, 'showCustomer']);
        Route::post('/customers/{id}/credit', [KarnyController::class, 'addCredit']);
        Route::post('/customers/{id}/payment', [KarnyController::class, 'addPayment']);
        Route::get('/statistics', [KarnyController::class, 'statistics']);
        Route::get('/qr/{qrCode}', [KarnyController::class, 'searchByQR']);
    });

    // Digital Services - Top-up & Factures
    Route::prefix('digital-services')->middleware('auth:api')->group(function () {
        Route::get('/services', [DigitalServiceController::class, 'services']);
        Route::post('/process', [DigitalServiceController::class, 'process']);
        Route::get('/history', [DigitalServiceController::class, 'history']);
        Route::get('/statistics', [DigitalServiceController::class, 'statistics']);
    });

    // Promotions - Moteur de Promotions Avancé
    Route::prefix('promotions')->group(function () {
        Route::get('/', [PromotionController::class, 'index']);
        Route::get('/featured', [PromotionController::class, 'featured']);
        Route::post('/validate', [PromotionController::class, 'validateCode'])->middleware('auth:api');
        Route::post('/apply', [PromotionController::class, 'apply'])->middleware('auth:api');
    });
});

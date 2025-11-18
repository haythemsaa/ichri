<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CatalogController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\UserController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {

    // Health check
    Route::get('/health', function () {
        return response()->json([
            'status' => 'ok',
            'timestamp' => now()->toIso8601String(),
            'version' => config('app.version', '1.0.0'),
        ]);
    });

    // Authentication routes
    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/send-otp', [AuthController::class, 'sendOTP']);
        Route::post('/verify-otp', [AuthController::class, 'verifyOTP']);
        Route::post('/reset-password', [AuthController::class, 'resetPassword']);

        // Protected routes
        Route::middleware('auth:api')->group(function () {
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::post('/refresh', [AuthController::class, 'refresh']);
            Route::get('/me', [AuthController::class, 'me']);
        });
    });

    // Catalog routes (public)
    Route::prefix('catalog')->group(function () {
        Route::get('/categories', [CatalogController::class, 'categories']);
        Route::get('/brands', [CatalogController::class, 'brands']);
        Route::get('/products', [CatalogController::class, 'products']);
        Route::get('/products/featured', [CatalogController::class, 'featured']);
        Route::get('/products/on-sale', [CatalogController::class, 'onSale']);
        Route::get('/products/bestsellers', [CatalogController::class, 'bestsellers']);
        Route::get('/products/search', [CatalogController::class, 'search']);
        Route::get('/products/{identifier}', [CatalogController::class, 'show']);
    });

    // Protected routes
    Route::middleware('auth:api')->group(function () {

        // User profile
        Route::prefix('user')->group(function () {
            Route::get('/profile', [UserController::class, 'profile']);
            Route::put('/profile', [UserController::class, 'updateProfile']);
            Route::post('/documents', [UserController::class, 'uploadDocument']);
            Route::get('/credit', [UserController::class, 'creditInfo']);
            Route::get('/statistics', [UserController::class, 'statistics']);
        });

        // Cart
        Route::prefix('cart')->group(function () {
            Route::get('/', [CartController::class, 'index']);
            Route::post('/add', [CartController::class, 'add']);
            Route::put('/update/{id}', [CartController::class, 'update']);
            Route::delete('/remove/{id}', [CartController::class, 'remove']);
            Route::delete('/clear', [CartController::class, 'clear']);
        });

        // Orders
        Route::prefix('orders')->group(function () {
            Route::get('/', [OrderController::class, 'index']);
            Route::post('/', [OrderController::class, 'store']);
            Route::get('/{id}', [OrderController::class, 'show']);
            Route::post('/{id}/cancel', [OrderController::class, 'cancel']);
            Route::post('/{id}/reorder', [OrderController::class, 'reorder']);
        });

        // Favorites
        Route::prefix('favorites')->group(function () {
            Route::get('/', [UserController::class, 'favorites']);
            Route::post('/toggle/{productId}', [UserController::class, 'toggleFavorite']);
        });
    });
});

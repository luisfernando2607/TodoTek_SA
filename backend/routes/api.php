<?php
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\StockController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me',      [AuthController::class, 'me']);
    });
    Route::apiResource('products', ProductController::class);
    Route::delete('products/{product}/images/{image}',     [ProductController::class, 'destroyImage']);
    Route::patch('products/{product}/images/{image}/main', [ProductController::class, 'setMainImage']);
    Route::get('products/{product}/stock',  [StockController::class, 'history']);
    Route::post('products/{product}/stock', [StockController::class, 'move']);
    Route::apiResource('clients', ClientController::class);
    Route::apiResource('invoices', InvoiceController::class)->only(['index', 'store', 'show']);
    Route::patch('invoices/{invoice}/cancel', [InvoiceController::class, 'cancel']);
});

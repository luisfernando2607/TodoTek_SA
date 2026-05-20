<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\StockController;
use Illuminate\Support\Facades\Route;

// ── Auth (público) ──────────────────────────────────────────────
Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
});

// ── Rutas protegidas ────────────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    Route::prefix('auth')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me',      [AuthController::class, 'me']);
    });

    // Productos
    Route::apiResource('products', ProductController::class);
    Route::delete('products/{product}/images/{image}',       [ProductController::class, 'destroyImage']);
    Route::patch('products/{product}/images/{image}/main',   [ProductController::class, 'setMainImage']);

    // Stock
    Route::get('products/{product}/stock',  [StockController::class, 'history']);
    Route::post('products/{product}/stock', [StockController::class, 'move']);

    // Clientes
    Route::apiResource('clients', ClientController::class);

    // Facturas
    Route::apiResource('invoices', InvoiceController::class)->only(['index', 'store', 'show']);
    Route::patch('invoices/{invoice}/cancel', [InvoiceController::class, 'cancel']);
});

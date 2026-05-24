<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DevDashController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/health', [DevDashController::class, 'health']);

Route::prefix('dev')->group(function () {
    Route::post('/run-tests',          [DevDashController::class, 'runTests']);
    Route::post('/regenerate-swagger', [DevDashController::class, 'regenerateSwagger']);
    Route::get('/db-stats',            [DevDashController::class, 'dbStats']);
});

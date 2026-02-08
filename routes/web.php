<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HolidayController;
use App\Http\Controllers\PrayerController;
use App\Http\Controllers\WeatherController;
use App\Http\Controllers\BackgroundController;
use App\Http\Controllers\UserController;

// Public routes
Route::get('/', function () {
    return view('dashboard');
})->name('dashboard');

// Protected admin routes
Route::middleware('auth')->group(function () {
    Route::get('/admin', function () {
        return redirect('/admin/prayer');
    });

    Route::get('/admin/holidays', function () {
        return view('admin.holidays');
    });

    Route::get('/admin/prayer', function () {
        return view('admin.prayer');
    });

    Route::get('/admin/weather', function () {
        return view('admin.weather');
    });

    Route::get('/admin/backgrounds', function () {
        return view('admin.backgrounds');
    });

    Route::get('/admin/users', function () {
        return view('admin.users');
    });
});

// Public API endpoints (read-only for dashboard)
Route::prefix('api/holidays')->group(function () {
    Route::get('/', [HolidayController::class, 'index']);
});

Route::prefix('api/prayer')->group(function () {
    Route::get('/times', [PrayerController::class, 'times']);
});

Route::prefix('api/weather')->group(function () {
    Route::get('/current', [WeatherController::class, 'current']);
});

Route::prefix('api/backgrounds')->group(function () {
    Route::get('/', [BackgroundController::class, 'index']);
});

// Protected API endpoints (create, update, delete)
Route::middleware('auth')->group(function () {
    // Holiday API
    Route::prefix('api/holidays')->group(function () {
        Route::get('/all', [HolidayController::class, 'all']);
        Route::post('/', [HolidayController::class, 'store']);
        Route::post('/{holiday}/toggle', [HolidayController::class, 'toggleVisibility']);
        Route::delete('/{holiday}', [HolidayController::class, 'destroy']);
        Route::post('/sync', [HolidayController::class, 'sync']);
        Route::post('/clear-cache', [HolidayController::class, 'clearCache']);
    });

    // Prayer API
    Route::prefix('api/prayer')->group(function () {
        Route::get('/settings', [PrayerController::class, 'settings']);
        Route::post('/settings', [PrayerController::class, 'updateSettings']);
        Route::post('/refresh', [PrayerController::class, 'refresh']);
    });

    // Weather API
    Route::prefix('api/weather')->group(function () {
        Route::get('/settings', [WeatherController::class, 'settings']);
        Route::post('/settings', [WeatherController::class, 'updateSettings']);
        Route::post('/refresh', [WeatherController::class, 'refresh']);
    });

    // Background API
    Route::prefix('api/backgrounds')->group(function () {
        Route::get('/all', [BackgroundController::class, 'all']);
        Route::get('/settings', [BackgroundController::class, 'settings']);
        Route::post('/settings', [BackgroundController::class, 'updateSettings']);
        Route::post('/upload', [BackgroundController::class, 'upload']);
        Route::post('/{background}/toggle', [BackgroundController::class, 'toggleVisibility']);
        Route::post('/order', [BackgroundController::class, 'updateOrder']);
        Route::delete('/{background}', [BackgroundController::class, 'destroy']);
        Route::post('/seed', [BackgroundController::class, 'seedFromFilesystem']);
    });

    // User management API
    Route::prefix('api/users')->group(function () {
        Route::get('/', [UserController::class, 'index']);
        Route::post('/', [UserController::class, 'store']);
        Route::put('/{user}', [UserController::class, 'update']);
        Route::delete('/{user}', [UserController::class, 'destroy']);
    });
});

require __DIR__.'/auth.php';

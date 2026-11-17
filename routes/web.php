<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SleepController;
use App\Http\Controllers\ActivityController;
use App\Http\Controllers\RecommendationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Authentication Routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Registration Routes
Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register']);

// Password Reset Routes
Route::get('/password/reset', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('/password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('/password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('/password/reset', [ResetPasswordController::class, 'reset'])->name('password.update');

// Protected Routes (require authentication)
Route::middleware(['auth'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/chart-data', [DashboardController::class, 'getChartData'])->name('chart.data');

    // Sleep Routes
    Route::prefix('sleep')->group(function () {
        Route::get('/', [SleepController::class, 'index'])->name('sleep.index');
        Route::get('/create', [SleepController::class, 'create'])->name('sleep.create');
        Route::post('/', [SleepController::class, 'store'])->name('sleep.store');
        Route::get('/{sleep}', [SleepController::class, 'show'])->name('sleep.show');
        Route::get('/{sleep}/edit', [SleepController::class, 'edit'])->name('sleep.edit');
        Route::put('/{sleep}', [SleepController::class, 'update'])->name('sleep.update');
        Route::delete('/{sleep}', [SleepController::class, 'destroy'])->name('sleep.destroy');
    });

    // Activity Routes
    Route::prefix('activity')->group(function () {
        Route::get('/', [ActivityController::class, 'index'])->name('activity.index');
        Route::get('/create', [ActivityController::class, 'create'])->name('activity.create');
        Route::post('/', [ActivityController::class, 'store'])->name('activity.store');
        Route::get('/{activity}', [ActivityController::class, 'show'])->name('activity.show');
        Route::get('/{activity}/edit', [ActivityController::class, 'edit'])->name('activity.edit');
        Route::put('/{activity}', [ActivityController::class, 'update'])->name('activity.update');
        Route::delete('/{activity}', [ActivityController::class, 'destroy'])->name('activity.destroy');
        Route::get('/weekly/summary', [ActivityController::class, 'getWeeklySummary'])->name('activity.weekly.summary');
    });

    // Recommendations Routes
    Route::prefix('recommendations')->group(function () {
        Route::get('/', [RecommendationController::class, 'index'])->name('recommendations');
        Route::get('/{category}', [RecommendationController::class, 'show'])->name('recommendations.category');
        Route::get('/tip/daily', [RecommendationController::class, 'getDailyTip'])->name('recommendations.daily.tip');
        Route::post('/{recommendation}/read', [RecommendationController::class, 'markAsRead'])->name('recommendations.mark.read');
    });

    // Profile Routes
    Route::prefix('profile')->group(function () {
        Route::get('/', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/', [ProfileController::class, 'update'])->name('profile.update');
        Route::get('/export', [ProfileController::class, 'exportData'])->name('profile.export');
        Route::get('/recommendations', [ProfileController::class, 'showRecommendations'])->name('profile.recommendations');
    });

    // API Routes for AJAX requests
    Route::prefix('api')->group(function () {
        Route::get('/sleep-stats', [SleepController::class, 'getSleepStatistics'])->name('api.sleep.stats');
        Route::get('/activity-stats', [ActivityController::class, 'getActivityStatistics'])->name('api.activity.stats');
        Route::get('/profile/stats', [ProfileController::class, 'getHealthStats'])->name('api.profile.stats');
    });
});

// Fallback route for 404 errors
Route::fallback(function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});




// Fitness Devices Routes
Route::prefix('fitness')->name('fitness.')->group(function () {
    // Страница управления устройствами
    Route::get('/devices', [FitnessDeviceController::class, 'index'])->name('devices');

    // Google Fit routes
    Route::get('/google-fit/connect', [FitnessDeviceController::class, 'connectGoogleFit'])->name('google-fit.connect');
    Route::get('/google-fit/callback', [FitnessDeviceController::class, 'googleFitCallback'])->name('google-fit.callback');
    Route::post('/google-fit/{device}/sync', [FitnessDeviceController::class, 'syncGoogleFit'])->name('google-fit.sync');
    Route::post('/google-fit/{device}/refresh-token', [FitnessDeviceController::class, 'refreshGoogleFitToken'])->name('google-fit.refresh-token');
    Route::delete('/google-fit/{device}/disconnect', [FitnessDeviceController::class, 'disconnectGoogleFit'])->name('google-fit.disconnect');

    // Settings
    Route::put('/devices/{device}/settings', [FitnessDeviceController::class, 'updateSyncSettings'])->name('devices.settings');
});

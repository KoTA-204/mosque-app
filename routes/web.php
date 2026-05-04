<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\UserController;

// Landing Page
Route::get('/', [LandingController::class, 'index'])->name('landing');
Route::view('/organisasi', 'landing.organisasi')->name('organisasi');
Route::view('/tentang-kami', 'landing.tentang-kami')->name('tentang-kami');


// dashboard pages
Route::get('/dashboard', function () {
    return view('pages.dashboard.ecommerce', ['title' => 'E-commerce Dashboard']);
})->name('dashboard');

// Authentication 
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'index'])->name('auth.login');
    Route::post('/login', [LoginController::class, 'store'])->name('auth.login.post');
    Route::get('/forgot-password', [ForgotPasswordController::class, 'index'])->name('auth.forgot-password');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLink'])->name('auth.forgot-password.post');
    Route::get('/forgot-password/check-email', [ForgotPasswordController::class, 'checkEmail'])->name('auth.check-email');
    Route::post('/forgot-password/resend', [ForgotPasswordController::class, 'resendEmail'])->name('auth.forgot-password.resend');
    Route::get('/reset-password', [ForgotPasswordController::class, 'resetPasswordForm'])->name('auth.reset-password');
    Route::get('/reset-password/success', [ForgotPasswordController::class, 'resetSuccess'])->name('auth.reset-success');
    Route::get('/reset-password/{token}', function (string $token) {
        return view('pages.auth.reset-password', [
            'token' => $token,
            'email' => request('email'),
        ]);
    })->middleware('guest')->name('password.reset');
    Route::post('/reset-password', [ForgotPasswordController::class, 'resetPassword'])->name('password.update');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {return view('pages.dashboard.ecommerce');})->name('dashboard');
    Route::post('/logout', [LoginController::class, 'destroy'])->name('auth.logout');

});

Route::middleware(['auth'])->prefix('dashboard')->name('dashboard.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('index');
    Route::resource('users', UserController::class);
    Route::middleware('permission:VIEW_ROLES')->group(function () {
        Route::resource('roles', RoleController::class);
    });

    Route::middleware('permission:VIEW_PERMISSIONS')->group(function () {
        Route::resource('permissions', PermissionController::class);
    });

    Route::middleware('permission:VIEW_MENUS')->group(function () {
        Route::resource('menus', MenuController::class);
    });
});
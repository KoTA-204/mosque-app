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
use App\Http\Controllers\TransaksiKegiatanController;
use App\Http\Controllers\ApprovalController;

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

    Route::middleware('permission:VIEW_KEGIATAN')->group(function () {
        Route::get('/kegiatan', [KegiatanController::class, 'index'])->name('kegiatan.index');

        Route::get('/kegiatan/{kegiatan}', [KegiatanController::class, 'show'])
            ->name('kegiatan.show')
            ->whereNumber('kegiatan');

        Route::get('/kegiatan/{kegiatan}/transaksi/{transaksi}', [KegiatanController::class, 'showTransaksi'])
            ->name('kegiatan.transaksi.show')
            ->whereNumber('kegiatan')
            ->whereNumber('transaksi');
    });

    Route::middleware('permission:CREATE_KEGIATAN')->group(function () {
        Route::get('/kegiatan/{kegiatan}/transaksi/create', [KegiatanController::class, 'createTransaksi'])->name('kegiatan.transaksi.create');
        Route::post('/kegiatan/{kegiatan}/transaksi', [KegiatanController::class, 'storeTransaksi'])->name('kegiatan.transaksi.store');
        Route::delete('/kegiatan/{kegiatan}/transaksi/{transaksi}', [KegiatanController::class, 'destroyTransaksi'])->name('kegiatan.transaksi.destroy');
        Route::get('/kegiatan/{kegiatan}/transaksi/{transaksi}/edit', [KegiatanController::class, 'editTransaksi'])
            ->name('kegiatan.transaksi.edit');
        Route::put('/kegiatan/{kegiatan}/transaksi/{transaksi}', [KegiatanController::class, 'updateTransaksi'])
            ->name('kegiatan.transaksi.update');
    });Route::middleware('permission:VIEW_KEGIATAN')->group(function () {
        Route::get('/kegiatan-panitia', [TransaksiKegiatanController::class, 'index'])->name('kegiatan-panitia.index');
 
        Route::get('/kegiatan-panitia/{kegiatan}', [TransaksiKegiatanController::class, 'show'])
            ->name('kegiatan-panitia.show')
            ->whereNumber('kegiatan');
 
        Route::get('/kegiatan-panitia/{kegiatan}/transaksi/{transaksi}', [TransaksiKegiatanController::class, 'showTransaksi'])
            ->name('kegiatan-panitia.transaksi.show')
            ->whereNumber('kegiatan')
            ->whereNumber('transaksi');
    });
 
    Route::middleware('permission:CREATE_KEGIATAN')->group(function () {
        Route::get('/kegiatan-panitia/{kegiatan}/transaksi/create', [TransaksiKegiatanController::class, 'createTransaksi'])->name('kegiatan-panitia.transaksi.create');
        Route::post('/kegiatan-panitia/{kegiatan}/transaksi', [TransaksiKegiatanController::class, 'storeTransaksi'])->name('kegiatan-panitia.transaksi.store');
        Route::delete('/kegiatan-panitia/{kegiatan}/transaksi/{transaksi}', [TransaksiKegiatanController::class, 'destroyTransaksi'])->name('kegiatan-panitia.transaksi.destroy');
        Route::get('/kegiatan-panitia/{kegiatan}/transaksi/{transaksi}/edit', [TransaksiKegiatanController::class, 'editTransaksi'])
            ->name('kegiatan-panitia.transaksi.edit');
        Route::put('/kegiatan-panitia/{kegiatan}/transaksi/{transaksi}', [TransaksiKegiatanController::class, 'updateTransaksi'])
            ->name('kegiatan-panitia.transaksi.update');
    });

    Route::middleware('permission:VIEW_APPROVAL')->group(function () {
        Route::get('/approval/transaksi', [KegiatanController::class, 'approvalIndex'])
            ->name('approval.index');
        Route::post('/approval/transaksi/bulk-approve', [KegiatanController::class, 'bulkApprove'])
            ->name('approval.bulk-approve');
        Route::post('/approval/transaksi/bulk-reject', [KegiatanController::class, 'bulkReject'])
            ->name('approval.bulk-reject');
        Route::get('/approval/transaksi/{transaksi}', [KegiatanController::class, 'approvalShow'])
            ->name('approval.show');
        Route::post('/approval/transaksi/{transaksi}/approve', [KegiatanController::class, 'approve'])
            ->name('approval.approve');
        Route::post('/approval/transaksi/{transaksi}/reject', [KegiatanController::class, 'reject'])
            ->name('approval.reject');
        Route::post('/approval/transaksi/{transaksi}/revision', [KegiatanController::class, 'revision'])
            ->name('approval.revision');
    });
    
    Route::middleware('permission:VIEW_KEGIATAN')->group(function () {
        Route::get('/kegiatan', [TransaksiKegiatanController::class, 'index'])->name('kegiatan.index');
 
        Route::get('/kegiatan/{kegiatan}', [TransaksiKegiatanController::class, 'show'])
            ->name('kegiatan.show')
            ->whereNumber('kegiatan');
 
        Route::get('/kegiatan/{kegiatan}/transaksi/{transaksi}', [TransaksiKegiatanController::class, 'showTransaksi'])
            ->name('kegiatan.transaksi.show')
            ->whereNumber('kegiatan')
            ->whereNumber('transaksi');
    });
 
    Route::middleware('permission:CREATE_KEGIATAN')->group(function () {
        Route::get('/kegiatan/{kegiatan}/transaksi/create', [TransaksiKegiatanController::class, 'createTransaksi'])->name('kegiatan.transaksi.create');
        Route::post('/kegiatan/{kegiatan}/transaksi', [TransaksiKegiatanController::class, 'storeTransaksi'])->name('kegiatan.transaksi.store');
        Route::delete('/kegiatan/{kegiatan}/transaksi/{transaksi}', [TransaksiKegiatanController::class, 'destroyTransaksi'])->name('kegiatan.transaksi.destroy');
        Route::get('/kegiatan/{kegiatan}/transaksi/{transaksi}/edit', [TransaksiKegiatanController::class, 'editTransaksi'])
            ->name('kegiatan.transaksi.edit');
        Route::put('/kegiatan/{kegiatan}/transaksi/{transaksi}', [TransaksiKegiatanController::class, 'updateTransaksi'])
            ->name('kegiatan.transaksi.update');
    });
 
    Route::middleware('permission:VIEW_APPROVAL')->group(function () {
        Route::get('/approval/transaksi', [ApprovalController::class, 'approvalIndex'])
            ->name('approval.index');
        Route::post('/approval/transaksi/bulk-approve', [ApprovalController::class, 'bulkApprove'])
            ->name('approval.bulk-approve');
        Route::post('/approval/transaksi/bulk-reject', [ApprovalController::class, 'bulkReject'])
            ->name('approval.bulk-reject');
        Route::get('/approval/transaksi/{transaksi}', [ApprovalController::class, 'approvalShow'])
            ->name('approval.show');
        Route::post('/approval/transaksi/{transaksi}/approve', [ApprovalController::class, 'approve'])
            ->name('approval.approve');
        Route::post('/approval/transaksi/{transaksi}/reject', [ApprovalController::class, 'reject'])
            ->name('approval.reject');
        Route::post('/approval/transaksi/{transaksi}/revision', [ApprovalController::class, 'revision'])
            ->name('approval.revision');
    });
});
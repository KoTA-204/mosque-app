<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LandingController;
use App\Http\Controller\DashboardController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Dashboard\ChartOfAccountController;

// Landing Page
Route::get('/', [LandingController::class, 'index'])->name('landing');
Route::view('/organisasi', 'landing.organisasi')->name('organisasi');
Route::view('/tentang-kami', 'landing.tentang-kami')->name('tentang-kami');
Route::prefix('donasi')->name('donasi.')->group(function () {
    Route::get('/', [DonasiController::class, 'index'])->name('index');
    Route::get('/{slug}', [DonasiController::class, 'show'])->name('show');
});
Route::prefix('kegiatan')->name('kegiatan.')->group(function () {
    Route::get('/', [KegiatanController::class, 'index'])->name('index');
    Route::get('/{slug}', [KegiatanController::class, 'show'])->name('show');
});
Route::get('/laporan', [LaporanController::class, 'publicIndex'])->name('laporan.public');

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
    Route::get('/dashboard', function () {return view('pages.dashboard.index');})->name('dashboard');
    Route::post('/logout', [LoginController::class, 'destroy'])->name('auth.logout');

});

Route::middleware(['auth'])->prefix('dashboard')->name('dashboard.')->group(function () {
    Route::get('/', [\App\Http\Controllers\DashboardController::class, 'index'])->name('index');
    Route::resource('banner', \App\Http\Controllers\Dashboard\BannerController::class);
    Route::resource('keuangan', \App\Http\Controllers\KeuanganController::class);
    Route::resource('transaksi', \App\Http\Controllers\TransaksiController::class);
    Route::resource('program', \App\Http\Controllers\ProgramController::class);
    Route::resource('kegiatan', \App\Http\Controllers\KegiatanDashboardController::class);
    Route::resource('donatur', \App\Http\Controllers\DonaturController::class);
    Route::prefix('laporan')->name('laporan.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Dashboard\LaporanController::class, 'index'])->name('index');
        Route::get('/cetak', [\App\Http\Controllers\Dashboard\LaporanController::class, 'cetak'])->name('cetak');
        Route::get('/export', [\App\Http\Controllers\Dashboard\LaporanController::class, 'export'])->name('export');
    });
    Route::prefix('pengaturan')->name('pengaturan.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Dashboard\PengaturanController::class, 'index'])->name('index');
        Route::put('/', [\App\Http\Controllers\Dashboard\PengaturanController::class, 'update'])->name('update');
    });

    Route::prefix('chart-of-account')->name('coa.')->group(function () {
        Route::get('/', [ChartOfAccountController::class, 'index'])->name('index');
        Route::get('/kategori/create', [ChartOfAccountController::class, 'createKategori'])->name('kategori.create');
        Route::post('/kategori', [ChartOfAccountController::class, 'storeKategori'])->name('kategori.store');
        Route::get('/kategori/{kategori}/edit', [ChartOfAccountController::class, 'editKategori'])->name('kategori.edit');
        Route::put('/kategori/{kategori}', [ChartOfAccountController::class, 'updateKategori'])->name('kategori.update');
        Route::delete('/kategori/{kategori}', [ChartOfAccountController::class, 'destroyKategori'])->name('kategori.destroy');
        Route::get('/sub-kategori/create', [ChartOfAccountController::class, 'createSubKategori'])->name('sub-kategori.create');
        Route::post('/sub-kategori', [ChartOfAccountController::class, 'storeSubKategori'])->name('sub-kategori.store');
        Route::get('/sub-kategori/{subKategori}/edit', [ChartOfAccountController::class, 'editSubKategori'])->name('sub-kategori.edit');
        Route::put('/sub-kategori/{subKategori}', [ChartOfAccountController::class, 'updateSubKategori'])->name('sub-kategori.update');
        Route::delete('/sub-kategori/{subKategori}', [ChartOfAccountController::class, 'destroySubKategori'])->name('sub-kategori.destroy');
        Route::get('/akun/create', [ChartOfAccountController::class, 'createAkun'])->name('akun.create');
        Route::post('/akun', [ChartOfAccountController::class, 'storeAkun'])->name('akun.store');
        Route::get('/akun/{akun}/edit', [ChartOfAccountController::class, 'editAkun'])->name('akun.edit');
        Route::put('/akun/{akun}', [ChartOfAccountController::class, 'updateAkun'])->name('akun.update');
        Route::delete('/akun/{akun}', [ChartOfAccountController::class, 'destroyAkun'])->name('akun.destroy');
    });
});
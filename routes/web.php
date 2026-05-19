<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LandingController;
use App\Http\Controller\DashboardController;
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

// Dashboard Pages
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
    Route::get('/dashboard', function () {return view('pages.dashboard.index');})->name('dashboard');
    Route::post('/logout', [LoginController::class, 'destroy'])->name('auth.logout');

});

Route::middleware(['auth'])->prefix('dashboard')->name('dashboard.')->group(function () {
    Route::get('/', [\App\Http\Controllers\DashboardController::class, 'index'])->name('index');
    Route::resource('users', UserController::class);
    Route::resource('roles', RoleController::class);
    Route::resource('permissions', PermissionController::class);
    Route::resource('menus', MenuController::class);

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
 
    Route::middleware('permission:VIEW_KEGIATAN')->group(function () {
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
 
    Route::middleware('permission:VIEW_KENCLENG')->group(function () {
        Route::get('/kencleng', [KenclengController::class, 'index'])
            ->name('kencleng.index');
    });

    Route::middleware('permission:CREATE_KENCLENG')->group(function () {
        Route::get('/kencleng/create', [KenclengController::class, 'create'])
            ->name('kencleng.create');

        Route::post('/kencleng', [KenclengController::class, 'store'])
            ->name('kencleng.store');

        Route::get('/kencleng/{kencleng}/edit', [KenclengController::class, 'edit'])
            ->name('kencleng.edit');

        Route::put('/kencleng/{kencleng}', [KenclengController::class, 'update'])
            ->name('kencleng.update');

        Route::delete('/kencleng/{kencleng}', [KenclengController::class, 'destroy'])
            ->name('kencleng.destroy');
    });

    Route::middleware('permission:VIEW_KENCLENG')->group(function () {
        Route::get('/kencleng/{kencleng}', [KenclengController::class, 'show'])
            ->name('kencleng.show');
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
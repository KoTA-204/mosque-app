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
use App\Http\Controllers\KegiatanController;
use App\Http\Controllers\ChartOfAccountController;
use App\Http\Controllers\KenclengController;
use App\Http\Controllers\KategoriTransaksiController;
use App\Http\Controllers\AsetController;
use App\Http\Controllers\JurnalUmumController;
use App\Http\Controllers\BukuBesarController;

// ── Landing Page ───────────────────────────────────────────────────────────
Route::get('/', [LandingController::class, 'index'])->name('landing');
Route::view('/organisasi', 'landing.organisasi')->name('organisasi');
Route::view('/tentang-kami', 'landing.tentang-kami')->name('tentang-kami');

// ── Authentication ─────────────────────────────────────────────────────────
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
    })->name('password.reset');
    Route::post('/reset-password', [ForgotPasswordController::class, 'resetPassword'])->name('password.update');
});

// ── Authenticated ──────────────────────────────────────────────────────────
Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('auth.logout');
});

// ── Dashboard ──────────────────────────────────────────────────────────────
Route::middleware(['auth'])->prefix('dashboard')->name('dashboard.')->group(function () {

    Route::get('/', [DashboardController::class, 'index'])->name('index');

    // ── Manajemen User ─────────────────────────────────────────────────────
    Route::middleware('permission:VIEW_USERS')->group(function () {
        Route::resource('users', UserController::class);
    });

    Route::middleware('permission:VIEW_ROLES')->group(function () {
        Route::resource('roles', RoleController::class);
    });

    Route::middleware('permission:VIEW_PERMISSIONS')->group(function () {
        Route::resource('permissions', PermissionController::class);
        Route::resource('menus', MenuController::class);
    });

    // ── Pencatatan - Kencleng ──────────────────────────────────────────────

    Route::middleware('permission:VIEW_KENCLENG')->group(function () {
        Route::get('/kencleng', [KenclengController::class, 'index'])->name('kencleng.index');
    });

    Route::middleware('permission:CREATE_KENCLENG')->group(function () {
        Route::get('/kencleng/create', [KenclengController::class, 'create'])->name('kencleng.create');
        Route::post('/kencleng', [KenclengController::class, 'store'])->name('kencleng.store');
    });

    Route::middleware('permission:EDIT_KENCLENG')->group(function () {
        Route::get('/kencleng/{kencleng}/edit', [KenclengController::class, 'edit'])->name('kencleng.edit');
        Route::put('/kencleng/{kencleng}', [KenclengController::class, 'update'])->name('kencleng.update');
    });

    Route::middleware('permission:VIEW_KENCLENG')->group(function () {
        Route::get('/kencleng/{kencleng}', [KenclengController::class, 'show'])
            ->name('kencleng.show')
            ->whereNumber('kencleng');
    });

    Route::middleware('permission:DELETE_KENCLENG')->group(function () {
        Route::delete('/kencleng/{kencleng}', [KenclengController::class, 'destroy'])->name('kencleng.destroy');
    });

    // ── Kegiatan Khusus - Data Kegiatan ────────────────────────────────────
    Route::middleware('permission:VIEW_KEGIATAN')->group(function () {
        Route::get('/kegiatan', [KegiatanController::class, 'index'])->name('kegiatan.index');
        Route::get('/kegiatan/{kegiatan}', [KegiatanController::class, 'show'])
            ->name('kegiatan.show')
            ->whereNumber('kegiatan');
    });

    Route::middleware('permission:CREATE_KEGIATAN')->group(function () {
        Route::get('/kegiatan/create', [KegiatanController::class, 'create'])->name('kegiatan.create');
        Route::post('/kegiatan', [KegiatanController::class, 'store'])->name('kegiatan.store');
    });

    Route::middleware('permission:EDIT_KEGIATAN')->group(function () {
        Route::get('/kegiatan/{kegiatan}/edit', [KegiatanController::class, 'edit'])->name('kegiatan.edit');
        Route::put('/kegiatan/{kegiatan}', [KegiatanController::class, 'update'])->name('kegiatan.update');
    });

    Route::middleware('permission:DELETE_KEGIATAN')->group(function () {
        Route::delete('/kegiatan/{kegiatan}', [KegiatanController::class, 'destroy'])->name('kegiatan.destroy');
    });

    // ── Kegiatan Khusus - Transaksi Kegiatan ───────────────────────────────
    Route::middleware('permission:VIEW_TRANSAKSI_KEGIATAN')->group(function () {
        Route::get('/kegiatan-panitia', [TransaksiKegiatanController::class, 'index'])->name('kegiatan-panitia.index');
        Route::get('/kegiatan-panitia/{kegiatan}', [TransaksiKegiatanController::class, 'show'])
            ->name('kegiatan-panitia.show')
            ->whereNumber('kegiatan');
        Route::get('/kegiatan-panitia/{kegiatan}/transaksi/{transaksi}', [TransaksiKegiatanController::class, 'showTransaksi'])
            ->whereNumber('kegiatan')
            ->whereNumber('transaksi')
            ->name('kegiatan-panitia.transaksi.show');
    });

    Route::middleware('permission:CREATE_TRANSAKSI_KEGIATAN')->group(function () {
        Route::get('/kegiatan-panitia/{kegiatan}/transaksi/create', [TransaksiKegiatanController::class, 'createTransaksi'])
            ->name('kegiatan-panitia.transaksi.create');
        Route::post('/kegiatan-panitia/{kegiatan}/transaksi', [TransaksiKegiatanController::class, 'storeTransaksi'])
            ->name('kegiatan-panitia.transaksi.store');
    });

    Route::middleware('permission:EDIT_TRANSAKSI_KEGIATAN')->group(function () {
        Route::get('/kegiatan-panitia/{kegiatan}/transaksi/{transaksi}/edit', [TransaksiKegiatanController::class, 'editTransaksi'])
            ->whereNumber('kegiatan')
            ->whereNumber('transaksi')
            ->name('kegiatan-panitia.transaksi.edit');

        Route::put('/kegiatan-panitia/{kegiatan}/transaksi/{transaksi}', [TransaksiKegiatanController::class, 'updateTransaksi'])
            ->whereNumber('kegiatan')
            ->whereNumber('transaksi')
            ->name('kegiatan-panitia.transaksi.update');
    });

    Route::middleware('permission:DELETE_TRANSAKSI_KEGIATAN')->group(function () {
        Route::delete('/kegiatan-panitia/{kegiatan}/transaksi/{transaksi}', [TransaksiKegiatanController::class, 'destroyTransaksi'])
            ->name('kegiatan-panitia.transaksi.destroy');
    });

    // ── Approval ───────────────────────────────────────────────────────────
    Route::middleware('permission:VIEW_APPROVAL')->group(function () {
        Route::get('/approval/transaksi', [ApprovalController::class, 'approvalIndex'])->name('approval.index');
        Route::get('/approval/transaksi/{transaksi}', [ApprovalController::class, 'approvalShow'])->name('approval.show');
        Route::post('/approval/transaksi/bulk-approve', [ApprovalController::class, 'bulkApprove'])->name('approval.bulk-approve');
        Route::post('/approval/transaksi/bulk-reject', [ApprovalController::class, 'bulkReject'])->name('approval.bulk-reject');
        Route::post('/approval/transaksi/{transaksi}/approve', [ApprovalController::class, 'approve'])->name('approval.approve');
        Route::post('/approval/transaksi/{transaksi}/reject', [ApprovalController::class, 'reject'])->name('approval.reject');
        Route::post('/approval/transaksi/{transaksi}/revision', [ApprovalController::class, 'revision'])->name('approval.revision');
    });

    // ── Manajemen Aset ─────────────────────────────────────────────────────────
    Route::middleware('permission:VIEW_ASET')->group(function () {
        Route::get('/aset', [AsetController::class, 'index'])->name('aset.index');
        Route::get('/aset/{aset}', [AsetController::class, 'show'])
            ->name('aset.show')
            ->whereNumber('aset');
    });
    
    Route::middleware('permission:CREATE_ASET')->group(function () {
        Route::get('/aset/create', [AsetController::class, 'create'])->name('aset.create');
        Route::post('/aset', [AsetController::class, 'store'])->name('aset.store');
    });
    
    Route::middleware('permission:EDIT_ASET')->group(function () {
        Route::get('/aset/{aset}/edit', [AsetController::class, 'edit'])
            ->name('aset.edit')
            ->whereNumber('aset');
        Route::put('/aset/{aset}', [AsetController::class, 'update'])->name('aset.update');
        Route::patch('/aset/{aset}/toggle-status', [AsetController::class, 'toggleStatus'])
            ->name('aset.toggle-status');
    });
    
    Route::middleware('permission:DELETE_ASET')->group(function () {
        Route::delete('/aset/{aset}', [AsetController::class, 'destroy'])->name('aset.destroy');
    });

    // ── Akuntansi - Jurnal Umum ─────────────────────────────────
    Route::middleware('permission:VIEW_JURNAL')->group(function () {
        Route::get('/jurnal-umum', [JurnalUmumController::class, 'index'])->name('jurnal-umum.index');
        Route::get('/jurnal-umum/{jurnalUmum}', [JurnalUmumController::class, 'show'])
            ->whereNumber('jurnalUmum')
            ->name('jurnal-umum.show');

        Route::middleware('permission:CREATE_JURNAL')->group(function () {
            Route::get('/jurnal-umum/create', [JurnalUmumController::class, 'create'])->name('jurnal-umum.create');
            Route::post('/jurnal-umum', [JurnalUmumController::class, 'store'])->name('jurnal-umum.store');
        });

        Route::middleware('permission:EDIT_JURNAL')->group(function () {
            Route::get('/jurnal-umum/{jurnalUmum}/edit', [JurnalUmumController::class, 'edit'])->name('jurnal-umum.edit');
            Route::put('/jurnal-umum/{jurnalUmum}', [JurnalUmumController::class, 'update'])->name('jurnal-umum.update');
        });

        Route::middleware('permission:DELETE_JURNAL')->group(function () {
            Route::delete('/jurnal-umum/{jurnalUmum}', [JurnalUmumController::class, 'destroy'])->name('jurnal-umum.destroy');
        });
    });

    // ── Akuntansi - Buku Besar ─────────────────────────────────
    Route::middleware('permission:VIEW_BUKU_BESAR')->group(function () {
        Route::get('/buku-besar', [BukuBesarController::class, 'index'])->name('buku-besar.index');
    });

    // ── Master Data - Chart of Accounts ────────────────────────────────────
    Route::middleware('permission:VIEW_COA')->prefix('chart-of-account')->name('coa.')->group(function () {
        Route::get('/', [ChartOfAccountController::class, 'index'])->name('index');

        Route::middleware('permission:CREATE_COA')->group(function () {
            Route::get('/kategori/create', [ChartOfAccountController::class, 'createKategori'])->name('kategori.create');
            Route::post('/kategori', [ChartOfAccountController::class, 'storeKategori'])->name('kategori.store');
            Route::get('/sub-kategori/create', [ChartOfAccountController::class, 'createSubKategori'])->name('sub-kategori.create');
            Route::post('/sub-kategori', [ChartOfAccountController::class, 'storeSubKategori'])->name('sub-kategori.store');
            Route::get('/akun/create', [ChartOfAccountController::class, 'createAkun'])->name('akun.create');
            Route::post('/akun', [ChartOfAccountController::class, 'storeAkun'])->name('akun.store');
        });

        Route::middleware('permission:EDIT_COA')->group(function () {
            Route::get('/kategori/{kategori}/edit', [ChartOfAccountController::class, 'editKategori'])->name('kategori.edit');
            Route::put('/kategori/{kategori}', [ChartOfAccountController::class, 'updateKategori'])->name('kategori.update');
            Route::get('/sub-kategori/{subKategori}/edit', [ChartOfAccountController::class, 'editSubKategori'])->name('sub-kategori.edit');
            Route::put('/sub-kategori/{subKategori}', [ChartOfAccountController::class, 'updateSubKategori'])->name('sub-kategori.update');
            Route::get('/akun/{akun}/edit', [ChartOfAccountController::class, 'editAkun'])->name('akun.edit');
            Route::put('/akun/{akun}', [ChartOfAccountController::class, 'updateAkun'])->name('akun.update');
        });

        Route::middleware('permission:DELETE_COA')->group(function () {
            Route::delete('/kategori/{kategori}', [ChartOfAccountController::class, 'destroyKategori'])->name('kategori.destroy');
            Route::delete('/sub-kategori/{subKategori}', [ChartOfAccountController::class, 'destroySubKategori'])->name('sub-kategori.destroy');
            Route::delete('/akun/{akun}', [ChartOfAccountController::class, 'destroyAkun'])->name('akun.destroy');
        });
    });

    // ── Master Data - Kategori Transaksi ───────────────────────────────────
    Route::middleware('permission:VIEW_KATEGORI')->group(function () {
        Route::get('/kategori-transaksi', [KategoriTransaksiController::class, 'index'])->name('kategori-transaksi.index');

        Route::middleware('permission:CREATE_KATEGORI')->group(function () {
            Route::get('/kategori-transaksi/create', [KategoriTransaksiController::class, 'create'])->name('kategori-transaksi.create');
            Route::post('/kategori-transaksi', [KategoriTransaksiController::class, 'store'])->name('kategori-transaksi.store');
        });

        Route::middleware('permission:EDIT_KATEGORI')->group(function () {
            Route::get('/kategori-transaksi/{kategoriTransaksi}/edit', [KategoriTransaksiController::class, 'edit'])->name('kategori-transaksi.edit');
            Route::put('/kategori-transaksi/{kategoriTransaksi}', [KategoriTransaksiController::class, 'update'])->name('kategori-transaksi.update');
        });

        Route::middleware('permission:DELETE_KATEGORI')->group(function () {
            Route::delete('/kategori-transaksi/{kategoriTransaksi}', [KategoriTransaksiController::class, 'destroy'])->name('kategori-transaksi.destroy');
        });
    });
});
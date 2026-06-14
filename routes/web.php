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
use App\Http\Controllers\TransaksiController;
use App\Http\Controllers\TransaksiKegiatanController;
use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\KegiatanController;
use App\Http\Controllers\ChartOfAccountController;
use App\Http\Controllers\KenclengController;
use App\Http\Controllers\KategoriTransaksiController;
use App\Http\Controllers\AsetController;
use App\Http\Controllers\JurnalPembukaController;
use App\Http\Controllers\JurnalUmumController;
use App\Http\Controllers\JurnalPenyesuaianController;
use App\Http\Controllers\JurnalKoreksiController;
use App\Http\Controllers\JurnalPenutupController;
use App\Http\Controllers\BukuBesarController;
use App\Http\Controllers\NeracaSaldoController;
use App\Http\Controllers\LaporanKeuanganController;

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
    Route::get('/forgot-password/check-status', [ForgotPasswordController::class, 'checkResetStatus'])->name('auth.check-reset-status');
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

// ── Dashboard Public ───────────────────────────────────────────────────────
Route::get('/laporan-keuangan', [DashboardController::class, 'laporanKeuangan'])
    ->name('laporan-keuangan.index');

// ── Dashboard ──────────────────────────────────────────────────────────────
Route::middleware(['auth', 'active'])->prefix('dashboard')->name('dashboard.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('index');

    // ── Manajemen User ─────────────────────────────────────────────────────
        Route::middleware('permission:VIEW_USERS')->group(function () {
            Route::get('/users',                        [UserController::class, 'index'])->name('users.index');
            Route::get('/users/create',                 [UserController::class, 'create'])->name('users.create');
            Route::post('/users',                       [UserController::class, 'store'])->name('users.store');
            Route::get('/users/{user}/edit',            [UserController::class, 'edit'])->name('users.edit');
            Route::put('/users/{user}',                 [UserController::class, 'update'])->name('users.update');
            Route::get('/users/{user}/delete',          [UserController::class, 'confirmDelete'])->name('users.confirmDelete');
            Route::delete('/users/{user}',              [UserController::class, 'destroy'])->name('users.destroy');
        });

        Route::middleware('permission:VIEW_ROLES')->group(function () {
            Route::resource('roles', RoleController::class);
        });

        Route::middleware('permission:VIEW_PERMISSIONS')->group(function () {
            Route::resource('permissions', PermissionController::class);
            Route::resource('menus', MenuController::class);
        });

    // ── Pencatatan - Transaksi ─────────────────────────────────────────────
 
    Route::middleware('permission:VIEW_TRANSAKSI')->group(function () {
        Route::get('/transaksi', [TransaksiController::class, 'index'])->name('transaksi.index');
 
        Route::get('/transaksi/{transaksi}', [TransaksiController::class, 'show'])
            ->name('transaksi.show')
            ->whereNumber('transaksi');
 
        Route::get('/transaksi/import/review', [TransaksiController::class, 'importReview'])
            ->name('transaksi.import.review');
    });
 
    Route::middleware('permission:CREATE_TRANSAKSI')->group(function () {
        Route::post('/transaksi', [TransaksiController::class, 'store'])->name('transaksi.store');
 
        // Import — upload file 
        Route::post('/transaksi/import', [TransaksiController::class, 'import'])
            ->name('transaksi.import');
 
        // Import — simpan hasil klasifikasi 
        Route::post('/transaksi/import/simpan', [TransaksiController::class, 'importSimpan'])
            ->name('transaksi.import.simpan');
    });
 
    Route::middleware('permission:EDIT_TRANSAKSI')->group(function () {
        Route::put('/transaksi/{transaksi}', [TransaksiController::class, 'update'])
            ->name('transaksi.update')
            ->whereNumber('transaksi');
    });
 
    Route::middleware('permission:DELETE_TRANSAKSI')->group(function () {
        Route::delete('/transaksi/{transaksi}', [TransaksiController::class, 'destroy'])
            ->name('transaksi.destroy')
            ->whereNumber('transaksi');
 
        Route::delete('/transaksi/bukti/{bukti}', [TransaksiController::class, 'destroyBukti'])
            ->name('transaksi.bukti.destroy')
            ->whereNumber('bukti');
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
        Route::get('/kegiatan',              [KegiatanController::class, 'index'])->name('kegiatan.index');
        Route::get('/kegiatan/{kegiatan}',   [KegiatanController::class, 'show'])->name('kegiatan.show')->whereNumber('kegiatan');
    });

    Route::middleware('permission:CREATE_KEGIATAN')->group(function () {
        Route::get('/kegiatan/create',       [KegiatanController::class, 'create'])->name('kegiatan.create');
        Route::post('/kegiatan',             [KegiatanController::class, 'store'])->name('kegiatan.store');
    });

    Route::middleware('permission:EDIT_KEGIATAN')->group(function () {
        Route::get('/kegiatan/{kegiatan}/edit',   [KegiatanController::class, 'edit'])->name('kegiatan.edit');
        Route::put('/kegiatan/{kegiatan}',        [KegiatanController::class, 'update'])->name('kegiatan.update');
    });

    Route::middleware('permission:DELETE_KEGIATAN')->group(function () {
        Route::get('/kegiatan/{kegiatan}/delete', [KegiatanController::class, 'confirmDelete'])->name('kegiatan.confirmDelete')->whereNumber('kegiatan');
        Route::delete('/kegiatan/{kegiatan}',     [KegiatanController::class, 'destroy'])->name('kegiatan.destroy');
    });

    // ── Kegiatan Khusus - Transaksi Kegiatan ───────────────────────────────
    
        Route::get('/transaksi-kegiatan', [TransaksiKegiatanController::class, 'index'])->name('transaksi-kegiatan.index');
        Route::get('/transaksi-kegiatan/{kegiatan}', [TransaksiKegiatanController::class, 'show'])
            ->name('transaksi-kegiatan.show')
            ->whereNumber('kegiatan');
        Route::get('/transaksi-kegiatan/{kegiatan}/transaksi/{transaksi}', [TransaksiKegiatanController::class, 'showTransaksi'])
            ->whereNumber('kegiatan')
            ->whereNumber('transaksi')
            ->name('transaksi-kegiatan.transaksi.show');
    

  
        Route::get('/transaksi-kegiatan/{kegiatan}/transaksi/create', [TransaksiKegiatanController::class, 'createTransaksi'])
            ->name('transaksi-kegiatan.transaksi.create');
        Route::post('/transaksi-kegiatan/{kegiatan}/transaksi', [TransaksiKegiatanController::class, 'storeTransaksi'])
            ->name('transaksi-kegiatan.transaksi.store');
   

   
        Route::get('/transaksi-kegiatan/{kegiatan}/transaksi/{transaksi}/edit', [TransaksiKegiatanController::class, 'editTransaksi'])
            ->whereNumber('kegiatan')
            ->whereNumber('transaksi')
            ->name('transaksi-kegiatan.transaksi.edit');

        Route::put('/transaksi-kegiatan/{kegiatan}/transaksi/{transaksi}', [TransaksiKegiatanController::class, 'updateTransaksi'])
            ->whereNumber('kegiatan')
            ->whereNumber('transaksi')
            ->name('transaksi-kegiatan.transaksi.update');
   
 
        Route::delete('/transaksi-kegiatan/{kegiatan}/transaksi/{transaksi}', [TransaksiKegiatanController::class, 'destroyTransaksi'])
            ->name('transaksi-kegiatan.transaksi.destroy');
 

    // ── Approval ───────────────────────────────────────────────────────────
    Route::middleware('permission:VIEW_APPROVAL')->group(function () {
        Route::get('/approval/transaksi', [ApprovalController::class, 'approvalIndex'])->name('approval.index');
        Route::get('/approval/transaksi/{transaksi}', [ApprovalController::class, 'approvalShow'])->name('approval.show');
        Route::post('/approval/transaksi/bulk-approve', [ApprovalController::class, 'bulkApprove'])->name('approval.bulk-approve');
        Route::post('/approval/transaksi/bulk-reject', [ApprovalController::class, 'bulkReject'])->name('approval.bulk-reject');
        Route::post('/approval/transaksi/bulk-revisi', [ApprovalController::class, 'bulkRevisi'])->name('approval.bulk-revisi');
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
            Route::post('/jurnal-umum/{jurnalUmum}/post', [JurnalUmumController::class, 'post'])->name('jurnal-umum.post');
            Route::post('/jurnal-umum/bulk-post', [JurnalUmumController::class, 'bulkPost'])->name('jurnal-umum.bulk-post');
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

    // ── Akuntansi - Neraca Saldo ─────────────────────────────────
    Route::middleware('permission:VIEW_NERACA_SALDO')->group(function () {
        Route::get('/neraca-saldo', [NeracaSaldoController::class, 'index'])->name('neraca-saldo.index');
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

    // ── Akuntansi - Jurnal Pembuka ─────────────────────────────────────────
    Route::middleware('permission:VIEW_JURNAL_PEMBUKA')->group(function () {
        Route::get('/jurnal-pembuka', [JurnalPembukaController::class, 'index'])
            ->name('jurnal-pembuka.index');
 
        Route::get('/jurnal-pembuka/{jurnalPembuka}', [JurnalPembukaController::class, 'show'])
            ->name('jurnal-pembuka.show')
            ->whereNumber('jurnalPembuka');

        Route::patch('/jurnal-pembuka/{jurnalPembuka}/posting', [JurnalPembukaController::class, 'posting'])
                ->name('jurnal-pembuka.posting')
                ->whereNumber('jurnalPembuka');

        Route::middleware('permission:CREATE_JURNAL_PEMBUKA')->group(function () {
            Route::get('/jurnal-pembuka/create', [JurnalPembukaController::class, 'create'])
                ->name('jurnal-pembuka.create');
            Route::post('/jurnal-pembuka', [JurnalPembukaController::class, 'store'])
                ->name('jurnal-pembuka.store');
        });

        Route::middleware('permission:EDIT_JURNAL_PEMBUKA')->group(function () {
            Route::get('/jurnal-pembuka/{jurnalPembuka}/edit', [JurnalPembukaController::class, 'edit'])
                ->name('jurnal-pembuka.edit')
                ->whereNumber('jurnalPembuka');
            Route::put('/jurnal-pembuka/{jurnalPembuka}', [JurnalPembukaController::class, 'update'])
                ->name('jurnal-pembuka.update')
                ->whereNumber('jurnalPembuka');
        });

        Route::middleware('permission:DELETE_JURNAL_PEMBUKA')->group(function () {
            Route::delete('/jurnal-pembuka/{jurnalPembuka}', [JurnalPembukaController::class, 'destroy'])
                ->name('jurnal-pembuka.destroy')
                ->whereNumber('jurnalPembuka');
        });
    });

    // ── Akuntansi - Jurnal Penyesuaian ─────────────────────────────────────────
    Route::middleware('permission:VIEW_JURNAL_PENYESUAIAN')->group(function () {
        Route::get('/jurnal-penyesuaian', [JurnalPenyesuaianController::class, 'index'])
            ->name('jurnal-penyesuaian.index');

        Route::get('/jurnal-penyesuaian/{jurnal}', [JurnalPenyesuaianController::class, 'show'])
            ->whereNumber('jurnal')
            ->name('jurnal-penyesuaian.show');

        Route::middleware('permission:CREATE_JURNAL_PENYESUAIAN')->group(function () {
            Route::get('/jurnal-penyesuaian/create', [JurnalPenyesuaianController::class, 'create'])
                ->name('jurnal-penyesuaian.create');

            Route::get('/jurnal-penyesuaian/aset-detail', [JurnalPenyesuaianController::class, 'getAsetDetail'])
                ->name('jurnal-penyesuaian.aset-detail');

            Route::post('/jurnal-penyesuaian', [JurnalPenyesuaianController::class, 'store'])
                ->name('jurnal-penyesuaian.store');

            Route::post('/jurnal-penyesuaian/bulk-post', [JurnalPenyesuaianController::class, 'bulkPost'])
                ->name('jurnal-penyesuaian.bulk-post');

            Route::delete('/jurnal-penyesuaian/{jurnal}', [JurnalPenyesuaianController::class, 'destroy'])
                ->whereNumber('jurnal')
                ->name('jurnal-penyesuaian.destroy');
        });
    });

    // ── Akuntansi - Jurnal Koreksi ─────────────────────────────────────────
    Route::middleware('permission:VIEW_JURNAL_KOREKSI')->group(function () {
        Route::get('/jurnal-koreksi', [JurnalKoreksiController::class, 'index'])
            ->name('jurnal-koreksi.index');

        Route::get('/jurnal-koreksi/{jurnal}', [JurnalKoreksiController::class, 'show'])
            ->whereNumber('jurnal')
            ->name('jurnal-koreksi.show');

        Route::middleware('permission:CREATE_JURNAL_KOREKSI')->group(function () {
            Route::get('/jurnal-koreksi/create', [JurnalKoreksiController::class, 'create'])
                ->name('jurnal-koreksi.create');

            Route::get('/jurnal-koreksi/aset-detail', [JurnalKoreksiController::class, 'getAsetDetail'])
                ->name('jurnal-koreksi.aset-detail');

            Route::post('/jurnal-koreksi', [JurnalKoreksiController::class, 'store'])
                ->name('jurnal-koreksi.store');

            Route::post('/jurnal-koreksi/bulk-post', [JurnalKoreksiController::class, 'bulkPost'])
                ->name('jurnal-koreksi.bulk-post');

            Route::delete('/jurnal-koreksi/{jurnal}', [JurnalKoreksiController::class, 'destroy'])
                ->whereNumber('jurnal')
                ->name('jurnal-koreksi.destroy');
        });
    });

    // ── Akuntansi - Jurnal Penutup ─────────────────────────────────────────
    Route::middleware('permission:VIEW_JURNAL_PENUTUP')->group(function () {
        Route::get('/jurnal-penutup', [JurnalPenutupController::class, 'index'])
            ->name('jurnal-penutup.index');

        Route::get('/jurnal-penutup/{jurnal}', [JurnalPenutupController::class, 'show'])
            ->whereNumber('jurnal')
            ->name('jurnal-penutup.show');

        Route::middleware('permission:CREATE_JURNAL_PENUTUP')->group(function () {
            Route::get('/jurnal-penutup/create', [JurnalPenutupController::class, 'create'])
                ->name('jurnal-penutup.create');

            Route::post('/jurnal-penutup/post-draft', [JurnalPenutupController::class, 'postDraft'])
                ->name('jurnal-penutup.post-draft');

            Route::get('/jurnal-penutup/aset-detail', [JurnalPenutupController::class, 'getAsetDetail'])
                ->name('jurnal-penutup.aset-detail');

            Route::post('/jurnal-penutup', [JurnalPenutupController::class, 'store'])
                ->name('jurnal-penutup.store');
            
            Route::post('/jurnal-penutup/konfirmasi-tahap', [JurnalPenutupController::class, 'konfirmasiTahap'])
                ->name('jurnal-penutup.konfirmasi-tahap');

            Route::post('/jurnal-penutup/bulk-post', [JurnalPenutupController::class, 'bulkPost'])
                ->name('jurnal-penutup.bulk-post');

            Route::delete('/jurnal-penutup/{jurnal}', [JurnalPenutupController::class, 'destroy'])
                ->whereNumber('jurnal')
                ->name('jurnal-penutup.destroy');
        });
    });

    Route::prefix('laporan')->name('laporan.')->group(function () {
        Route::get('/penghasilan-komprehensif', [LaporanKeuanganController::class, 'penghasilanKomprehensif'])
            ->name('penghasilan-komprehensif');
    
        Route::get('/posisi-keuangan', [LaporanKeuanganController::class, 'posisiKeuangan'])
            ->name('posisi-keuangan');
    
        Route::get('/perubahan-aset-neto', [LaporanKeuanganController::class, 'perubahanAsetNeto'])
            ->name('perubahan-aset-neto');

        Route::get('/arus-kas', [LaporanKeuanganController::class, 'arusKas'])
            ->name('arus-kas');

        Route::get('/calk', [LaporanKeuanganController::class, 'calk'])
            ->name('calk');
    });
});
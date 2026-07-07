<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Autentikasi\LoginController;
use App\Http\Controllers\Autentikasi\ForgotPasswordController;
use App\Http\Controllers\ManajemenAkses\RoleController;
use App\Http\Controllers\ManajemenAkses\PermissionController;
use App\Http\Controllers\ManajemenAkses\MenuController;
use App\Http\Controllers\ManajemenAkses\UserController;
use App\Http\Controllers\Operasional\TransaksiController;
use App\Http\Controllers\Operasional\TransaksiKegiatanController;
use App\Http\Controllers\Operasional\ApprovalController;
use App\Http\Controllers\DataInduk\KegiatanController;
use App\Http\Controllers\DataInduk\ChartOfAccountController;
use App\Http\Controllers\Operasional\KenclengController;
use App\Http\Controllers\DataInduk\KategoriTransaksiController;
use App\Http\Controllers\Aset\AsetController;
use App\Http\Controllers\Akuntansi\JurnalPembukaController;
use App\Http\Controllers\Akuntansi\JurnalUmumController;
use App\Http\Controllers\Akuntansi\JurnalPenyesuaianController;
use App\Http\Controllers\Akuntansi\JurnalKoreksiController;
use App\Http\Controllers\Akuntansi\JurnalPenutupController;
use App\Http\Controllers\Akuntansi\BukuBesarController;
use App\Http\Controllers\Akuntansi\NeracaSaldoController;
use App\Http\Controllers\LaporanKeuangan\LaporanKeuanganController;

// ── Landing Page ───────────────────────────────────────────────────────────
Route::get('/', [LandingController::class, 'index'])->name('landing');
Route::view('/organisasi', 'landing.organisasi')->name('organisasi');
Route::view('/tentang-kami', 'landing.tentang-kami')->name('tentang-kami');

// ── Authentication ─────────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'tampilkanFormLogin'])->name('auth.login');
    Route::post('/login', [LoginController::class, 'prosesLogin'])->name('auth.login.post');
    Route::get('/forgot-password', [ForgotPasswordController::class, 'tampilkanFormLupaPassword'])->name('auth.forgot-password');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'kirimTautanResetPassword'])->name('auth.forgot-password.post');
    Route::get('/forgot-password/check-email', [ForgotPasswordController::class, 'tampilkanHalamanCekEmail'])->name('auth.check-email');
    Route::get('/forgot-password/check-status', [ForgotPasswordController::class, 'checkResetStatus'])->name('auth.check-reset-status');
    Route::post('/forgot-password/resend', [ForgotPasswordController::class, 'kirimUlangEmailReset'])->name('auth.forgot-password.resend');
    Route::get('/reset-password', [ForgotPasswordController::class, 'tampilkanFormResetPassword'])->name('auth.reset-password');
    Route::get('/reset-password/success', [ForgotPasswordController::class, 'tampilkanHalamanResetBerhasil'])->name('auth.reset-success');
    Route::get('/reset-password/{token}', function (string $token) {
        return view('pages.autentikasi.reset-password', [
            'token' => $token,
            'email' => request('email'),
        ]);
    })->name('password.reset');
    Route::post('/reset-password', [ForgotPasswordController::class, 'prosesResetPassword'])->name('password.update');
});

// ── Authenticated ──────────────────────────────────────────────────────────
Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'prosesLogout'])->name('auth.logout');
});

// ── Dashboard Public ───────────────────────────────────────────────────────
Route::get('/laporan-keuangan', [DashboardController::class, 'laporanKeuangan'])
    ->name('laporan-keuangan.index');

// ── Export Transaksi Publik ───────────────────────────────────────────────
Route::get('/laporan-keuangan/export/excel', [DashboardController::class, 'exportTransaksiExcel'])->name('laporan-keuangan.export-excel');
Route::get('/laporan-keuangan/export/pdf', [DashboardController::class, 'exportTransaksiPdf'])->name('laporan-keuangan.export-pdf');

// ── Laporan Keuangan Publik ─────────────────────────────────────────────────────────────────
Route::prefix('laporan')->name('laporan.')->group(function () {
    Route::get('/posisi-keuangan', [LaporanKeuanganController::class, 'posisiKeuangan'])->name('posisi-keuangan');
    Route::get('/penghasilan-komprehensif', [LaporanKeuanganController::class, 'penghasilanKomprehensif'])->name('penghasilan-komprehensif');
    Route::get('/perubahan-aset-neto', [LaporanKeuanganController::class, 'perubahanAsetNeto'])->name('perubahan-aset-neto');
    Route::get('/arus-kas', [LaporanKeuanganController::class, 'arusKas'])->name('arus-kas');
    Route::get('/catatan-atas-laporan', [LaporanKeuanganController::class, 'calk'])->name('catatan-atas-laporan');
    Route::get('/{jenis}/unduh-pdf', [LaporanKeuanganController::class, 'downloadPdf'])
        ->where('jenis', 'posisi-keuangan|penghasilan-komprehensif|perubahan-aset-neto|arus-kas|calk')
        ->name('pdf');
});
    
// ── Dashboard ──────────────────────────────────────────────────────────────
Route::middleware(['auth', 'active'])->prefix('dashboard')->name('dashboard.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('index');
        Route::get('/home', [DashboardController::class, 'home'])->name('home');

    // ── Laporan Keuangan Index (redirect ke halaman pertama) ───────────────
    Route::get('/laporan-keuangan', function () {
        return redirect()->route('dashboard.laporan.penghasilan-komprehensif');
    })->name('laporan-keuangan.index');

    // ── Manajemen User ─────────────────────────────────────────────────────
    Route::middleware('permission:VIEW_USERS')->group(function () {
        Route::get('/users', [UserController::class, 'tampilkanDaftarUser'])->name('users.index');
        Route::get('/users/create', [UserController::class, 'tampilkanFormTambahUser'])->name('users.create');
        Route::post('/users', [UserController::class, 'simpanUserBaru'])->name('users.store');
        Route::post('/users/{user}/send-credentials', [UserController::class, 'kirimKredensialUser'])->name('users.send-credentials');
        Route::get('/users/{user}/edit', [UserController::class, 'tampilkanFormEditUser'])->name('users.edit');
        Route::put('/users/{user}', [UserController::class, 'perbaruiUser'])->name('users.update');
        Route::get('/users/{user}/delete', [UserController::class, 'tampilkanKonfirmasiHapusUser'])->name('users.confirmDelete');
        Route::delete('/users/{user}', [UserController::class, 'hapusUser'])->name('users.destroy');
    });

    Route::middleware('permission:VIEW_ROLES')->group(function () {
        Route::get('/roles', [RoleController::class, 'tampilkanDaftarRole'])->name('roles.index');
        Route::get('/roles/create', [RoleController::class, 'tampilkanFormTambahRole'])->name('roles.create');
        Route::post('/roles', [RoleController::class, 'simpanRoleBaru'])->name('roles.store');
        Route::get('/roles/{role}', [RoleController::class, 'tampilkanDetailRole'])->name('roles.show');
        Route::get('/roles/{role}/edit', [RoleController::class, 'tampilkanFormEditRole'])->name('roles.edit');
        Route::put('/roles/{role}', [RoleController::class, 'perbaruiRole'])->name('roles.update');
        Route::delete('/roles/{role}', [RoleController::class, 'hapusRole'])->name('roles.destroy');
    });

    Route::middleware('permission:VIEW_PERMISSIONS')->group(function () {
        Route::get('/permissions', [PermissionController::class, 'tampilkanDaftarPermission'])->name('permissions.index');
        Route::get('/permissions/create', [PermissionController::class, 'tampilkanFormTambahPermission'])->name('permissions.create');
        Route::post('/permissions', [PermissionController::class, 'simpanPermissionBaru'])->name('permissions.store');
        Route::get('/permissions/{permission}', [PermissionController::class, 'tampilkanDetailPermission'])->name('permissions.show');
        Route::get('/permissions/{permission}/edit', [PermissionController::class, 'tampilkanFormEditPermission'])->name('permissions.edit');
        Route::put('/permissions/{permission}', [PermissionController::class, 'perbaruiPermission'])->name('permissions.update');
        Route::delete('/permissions/{permission}', [PermissionController::class, 'hapusPermission'])->name('permissions.destroy');
    });

    // ── Manajemen Menu (CRUD khusus Administrator via VIEW_MENU) ─────────
    Route::middleware('permission:VIEW_MENU')->group(function () {
        Route::get('/menus', [MenuController::class, 'tampilkanDaftarMenu'])->name('menus.index');
        Route::post('/menus', [MenuController::class, 'simpanMenuBaru'])->name('menus.store');
        Route::get('/menus/{menu}', [MenuController::class, 'tampilkanDetailMenu'])->name('menus.show');
        Route::put('/menus/{menu}', [MenuController::class, 'perbaruiMenu'])->name('menus.update');
        Route::delete('/menus/{menu}', [MenuController::class, 'hapusMenu'])->name('menus.destroy');
    });

    // ── Pencatatan - Transaksi ─────────────────────────────────────────────
    Route::middleware('permission:VIEW_TRANSAKSI')->group(function () {
        Route::get('/transaksi', [TransaksiController::class, 'tampilkanDaftarTransaksi'])->name('transaksi.index');

        Route::get('/transaksi/{transaksi}', [TransaksiController::class, 'tampilkanDetailTransaksi'])
            ->name('transaksi.show')
            ->whereNumber('transaksi');

        Route::get('/transaksi/import/review', [TransaksiController::class, 'tampilkanReviewImpor'])
            ->name('transaksi.import.review');
    });

    Route::middleware('permission:CREATE_TRANSAKSI')->group(function () {
        Route::post('/transaksi', [TransaksiController::class, 'simpanTransaksiBaru'])->name('transaksi.store');
        Route::post('/transaksi/import', [TransaksiController::class, 'imporMutasiBank'])->name('transaksi.import');
        Route::post('/transaksi/import/simpan', [TransaksiController::class, 'simpanHasilImpor'])->name('transaksi.import.simpan');
    });

    Route::middleware('permission:EDIT_TRANSAKSI')->group(function () {
        Route::put('/transaksi/{transaksi}', [TransaksiController::class, 'perbaruiTransaksi'])
            ->name('transaksi.update')
            ->whereNumber('transaksi');
    });

    Route::middleware('permission:DELETE_TRANSAKSI')->group(function () {
        Route::delete('/transaksi/{transaksi}', [TransaksiController::class, 'hapusTransaksi'])
            ->name('transaksi.destroy')
            ->whereNumber('transaksi');

        Route::delete('/transaksi/bukti/{bukti}', [TransaksiController::class, 'hapusBuktiTransaksi'])
            ->name('transaksi.bukti.destroy')
            ->whereNumber('bukti');
    });

    // ── Pencatatan - Kencleng ──────────────────────────────────────────────
    Route::middleware('permission:VIEW_KENCLENG')->group(function () {
        Route::get('/kencleng', [KenclengController::class, 'tampilkanDaftarKencleng'])->name('kencleng.index');
        Route::get('/kencleng/{kencleng}', [KenclengController::class, 'tampilkanDetailKencleng'])
            ->name('kencleng.show')
            ->whereNumber('kencleng');
    });

    Route::middleware('permission:CREATE_KENCLENG')->group(function () {
        Route::get('/kencleng/create', [KenclengController::class, 'tampilkanFormTambahKencleng'])->name('kencleng.create');
        Route::post('/kencleng', [KenclengController::class, 'simpanKenclengBaru'])->name('kencleng.store');
    });

    Route::middleware('permission:EDIT_KENCLENG')->group(function () {
        Route::get('/kencleng/{kencleng}/edit', [KenclengController::class, 'tampilkanFormEditKencleng'])->name('kencleng.edit');
        Route::put('/kencleng/{kencleng}', [KenclengController::class, 'perbaruiKencleng'])->name('kencleng.update');
    });

    Route::middleware('permission:DELETE_KENCLENG')->group(function () {
        Route::delete('/kencleng/{kencleng}', [KenclengController::class, 'hapusKencleng'])->name('kencleng.destroy');
    });

    // ── Kegiatan Khusus - Data Kegiatan ────────────────────────────────────
    Route::middleware('permission:VIEW_KEGIATAN')->group(function () {
        Route::get('/kegiatan',            [KegiatanController::class, 'tampilkanDaftarKegiatan'])->name('kegiatan.index');
        Route::get('/kegiatan/{kegiatan}', [KegiatanController::class, 'tampilkanDetailKegiatan'])->name('kegiatan.show')->whereNumber('kegiatan');
    });

    Route::middleware('permission:CREATE_KEGIATAN')->group(function () {
        Route::get('/kegiatan/create',  [KegiatanController::class, 'tampilkanFormTambahKegiatan'])->name('kegiatan.create');
        Route::post('/kegiatan',        [KegiatanController::class, 'simpanKegiatanBaru'])->name('kegiatan.store');
    });

    Route::middleware('permission:EDIT_KEGIATAN')->group(function () {
        Route::get('/kegiatan/{kegiatan}/edit', [KegiatanController::class, 'tampilkanFormUbahKegiatan'])->name('kegiatan.edit');
        Route::put('/kegiatan/{kegiatan}',      [KegiatanController::class, 'perbaruiKegiatan'])->name('kegiatan.update');
    });

    Route::middleware('permission:DELETE_KEGIATAN')->group(function () {
        Route::get('/kegiatan/{kegiatan}/delete', [KegiatanController::class, 'tampilkanKonfirmasiHapusKegiatan'])->name('kegiatan.confirmDelete')->whereNumber('kegiatan');
        Route::delete('/kegiatan/{kegiatan}',     [KegiatanController::class, 'hapusKegiatan'])->name('kegiatan.destroy');
    });

    // ── Kegiatan Khusus - Transaksi Kegiatan ───────────────────────────────
    Route::get('/transaksi-kegiatan', [TransaksiKegiatanController::class, 'tampilkanDaftarKegiatan'])->name('transaksi-kegiatan.index');
    Route::get('/transaksi-kegiatan/{kegiatan}', [TransaksiKegiatanController::class, 'tampilkanTransaksiKegiatan'])
        ->name('transaksi-kegiatan.show')
        ->whereNumber('kegiatan');
    Route::get('/transaksi-kegiatan/{kegiatan}/transaksi/{transaksi}', [TransaksiKegiatanController::class, 'tampilkanDetailTransaksiKegiatan'])
        ->whereNumber('kegiatan')
        ->whereNumber('transaksi')
        ->name('transaksi-kegiatan.transaksi.show');

    Route::get('/transaksi-kegiatan/{kegiatan}/transaksi/create', [TransaksiKegiatanController::class, 'createTransaksi'])
        ->name('transaksi-kegiatan.transaksi.create');
    Route::post('/transaksi-kegiatan/{kegiatan}/transaksi', [TransaksiKegiatanController::class, 'simpanTransaksiKegiatan'])
        ->name('transaksi-kegiatan.transaksi.store');

    Route::get('/transaksi-kegiatan/{kegiatan}/transaksi/{transaksi}/edit', [TransaksiKegiatanController::class, 'editTransaksi'])
        ->whereNumber('kegiatan')
        ->whereNumber('transaksi')
        ->name('transaksi-kegiatan.transaksi.edit');
    Route::put('/transaksi-kegiatan/{kegiatan}/transaksi/{transaksi}', [TransaksiKegiatanController::class, 'perbaruiTransaksiKegiatan'])
        ->whereNumber('kegiatan')
        ->whereNumber('transaksi')
        ->name('transaksi-kegiatan.transaksi.update');
    Route::delete('/transaksi-kegiatan/{kegiatan}/transaksi/{transaksi}', [TransaksiKegiatanController::class, 'hapusTransaksiKegiatan'])
        ->name('transaksi-kegiatan.transaksi.destroy');

    // ── Approval ───────────────────────────────────────────────────────────
    Route::middleware('permission:VIEW_APPROVAL')->group(function () {
        Route::get('/approval/transaksi', [ApprovalController::class, 'tampilkanDaftarApproval'])->name('approval.index');
        Route::get('/approval/transaksi/{transaksi}', [ApprovalController::class, 'tampilkanDetailApproval'])->name('approval.show');
        Route::post('/approval/transaksi/bulk-approve', [ApprovalController::class, 'setujuiTransaksiMassal'])->name('approval.bulk-approve');
        Route::post('/approval/transaksi/bulk-reject', [ApprovalController::class, 'tolakTransaksiMassal'])->name('approval.bulk-reject');
        Route::post('/approval/transaksi/bulk-revisi', [ApprovalController::class, 'revisiTransaksiMassal'])->name('approval.bulk-revisi');
        Route::post('/approval/transaksi/{transaksi}/approve', [ApprovalController::class, 'setujuiTransaksi'])->name('approval.approve');
        Route::post('/approval/transaksi/{transaksi}/reject', [ApprovalController::class, 'tolakTransaksi'])->name('approval.reject');
        Route::post('/approval/transaksi/{transaksi}/revision', [ApprovalController::class, 'revisiTransaksi'])->name('approval.revision');
    });

    // ── Manajemen Aset ─────────────────────────────────────────────────────
    Route::middleware('permission:VIEW_ASET')->group(function () {
        Route::get('/aset', [AsetController::class, 'tampilkanDaftarAset'])->name('aset.index');
        // Menampilkan modal form (create/edit) termasuk hak VIEW: Administrator boleh
        // MELIHAT modal tambah/ubah aset. Alur SIMPAN (store/update) tetap dijaga
        // permission CREATE_ASET / EDIT_ASET di bawah -- sesuai pola "guard di route POST".
        Route::get('/aset/create', [AsetController::class, 'tampilkanFormTambahAset'])->name('aset.create');
        Route::get('/aset/{aset}/edit', [AsetController::class, 'tampilkanFormUbahAset'])
            ->name('aset.edit')
            ->whereNumber('aset');
        Route::get('/aset/{aset}', [AsetController::class, 'tampilkanDetailAset'])
            ->name('aset.show')
            ->whereNumber('aset');
    });

    // Alur SIMPAN aset baru -- hanya role berhak (Sekretaris). Admin: ditolak (403).
    Route::middleware('permission:CREATE_ASET')->group(function () {
        Route::post('/aset', [AsetController::class, 'simpanAsetBaru'])->name('aset.store');
    });

    // Alur UBAH / toggle status aset -- hanya role berhak (Sekretaris). Admin: ditolak (403).
    Route::middleware('permission:EDIT_ASET')->group(function () {
        Route::put('/aset/{aset}', [AsetController::class, 'perbaruiAset'])->name('aset.update');
        Route::patch('/aset/{aset}/toggle-status', [AsetController::class, 'ubahStatusAset'])
            ->name('aset.toggle-status');
    });

    Route::middleware('permission:DELETE_ASET')->group(function () {
        Route::delete('/aset/{aset}', [AsetController::class, 'hapusAset'])->name('aset.destroy');
    });

    // ── Akuntansi - Jurnal Umum ────────────────────────────────────────────
    Route::middleware('permission:VIEW_JURNAL')->group(function () {
        Route::get('/jurnal-umum', [JurnalUmumController::class, 'tampilkanJurnalUmum'])->name('jurnal-umum.index');

        Route::middleware('permission:CREATE_JURNAL')->group(function () {
            Route::post('/jurnal-umum/bulk-post', [JurnalUmumController::class, 'bulkPost'])->name('jurnal-umum.bulk-post');
            Route::post('/jurnal-umum/{jurnalUmum}/post', [JurnalUmumController::class, 'post'])->name('jurnal-umum.post');
        });

        Route::middleware('permission:DELETE_JURNAL')->group(function () {
            Route::delete('/jurnal-umum/{jurnalUmum}', [JurnalUmumController::class, 'hapusJurnalUmum'])->name('jurnal-umum.destroy');
        });

        Route::get('/jurnal-umum/{jurnalUmum}', [JurnalUmumController::class, 'tampilkanDetailJurnalUmum'])
            ->whereNumber('jurnalUmum')
            ->name('jurnal-umum.show');
    });

    // ── Akuntansi - Buku Besar ─────────────────────────────────────────────
    Route::middleware('permission:VIEW_BUKU_BESAR')->group(function () {
        Route::get('/buku-besar', [BukuBesarController::class, 'index'])->name('buku-besar.index');
    });

    // ── Akuntansi - Neraca Saldo ───────────────────────────────────────────
    Route::middleware('permission:VIEW_NERACA_SALDO')->group(function () {
        Route::get('/neraca-saldo', [NeracaSaldoController::class, 'index'])->name('neraca-saldo.index');
    });

    // ── Master Data - Chart of Accounts ────────────────────────────────────
    Route::middleware('permission:VIEW_COA')->prefix('chart-of-account')->name('coa.')->group(function () {
        Route::get('/', [ChartOfAccountController::class, 'tampilkanDaftarCoa'])->name('index');

        Route::middleware('permission:CREATE_COA')->group(function () {
            Route::get('/kategori/create', [ChartOfAccountController::class, 'tampilkanFormTambahKategoriAkun'])->name('kategori.create');
            Route::post('/kategori', [ChartOfAccountController::class, 'simpanKategoriAkunBaru'])->name('kategori.store');
            Route::get('/sub-kategori/create', [ChartOfAccountController::class, 'tampilkanFormTambahSubKategori'])->name('sub-kategori.create');
            Route::post('/sub-kategori', [ChartOfAccountController::class, 'simpanSubKategoriBaru'])->name('sub-kategori.store');
            Route::get('/akun/create', [ChartOfAccountController::class, 'tampilkanFormTambahAkun'])->name('akun.create');
            Route::post('/akun', [ChartOfAccountController::class, 'simpanAkunBaru'])->name('akun.store');
        });

        Route::middleware('permission:EDIT_COA')->group(function () {
            Route::get('/kategori/{kategori}/edit', [ChartOfAccountController::class, 'tampilkanFormUbahKategoriAkun'])->name('kategori.edit');
            Route::put('/kategori/{kategori}', [ChartOfAccountController::class, 'perbaruiKategoriAkun'])->name('kategori.update');
            Route::get('/sub-kategori/{subKategori}/edit', [ChartOfAccountController::class, 'tampilkanFormUbahSubKategori'])->name('sub-kategori.edit');
            Route::put('/sub-kategori/{subKategori}', [ChartOfAccountController::class, 'perbaruiSubKategori'])->name('sub-kategori.update');
            Route::get('/akun/{akun}/edit', [ChartOfAccountController::class, 'tampilkanFormUbahAkun'])->name('akun.edit');
            Route::put('/akun/{akun}', [ChartOfAccountController::class, 'perbaruiAkun'])->name('akun.update');
        });

        Route::middleware('permission:DELETE_COA')->group(function () {
            Route::delete('/kategori/{kategori}', [ChartOfAccountController::class, 'hapusKategoriAkun'])->name('kategori.destroy');
            Route::delete('/sub-kategori/{subKategori}', [ChartOfAccountController::class, 'hapusSubKategori'])->name('sub-kategori.destroy');
            Route::delete('/akun/{akun}', [ChartOfAccountController::class, 'hapusAkun'])->name('akun.destroy');
        });
    });

    // ── Master Data - Kategori Transaksi ───────────────────────────────────
    Route::middleware('permission:VIEW_KATEGORI')->group(function () {
        Route::get('/kategori-transaksi', [KategoriTransaksiController::class, 'tampilkanDaftarKategoriTransaksi'])->name('kategori-transaksi.index');

        Route::middleware('permission:CREATE_KATEGORI')->group(function () {
            Route::get('/kategori-transaksi/create', [KategoriTransaksiController::class, 'tampilkanFormTambahKategoriTransaksi'])->name('kategori-transaksi.create');
            Route::post('/kategori-transaksi', [KategoriTransaksiController::class, 'simpanKategoriTransaksiBaru'])->name('kategori-transaksi.store');
        });

        Route::middleware('permission:EDIT_KATEGORI')->group(function () {
            Route::get('/kategori-transaksi/{kategoriTransaksi}/edit', [KategoriTransaksiController::class, 'tampilkanFormUbahKategoriTransaksi'])->name('kategori-transaksi.edit');
            Route::put('/kategori-transaksi/{kategoriTransaksi}', [KategoriTransaksiController::class, 'perbaruiKategoriTransaksi'])->name('kategori-transaksi.update');
        });

        Route::middleware('permission:DELETE_KATEGORI')->group(function () {
            Route::delete('/kategori-transaksi/{kategoriTransaksi}', [KategoriTransaksiController::class, 'hapusKategoriTransaksi'])->name('kategori-transaksi.destroy');
        });
    });

    // ── Akuntansi - Jurnal Pembuka ─────────────────────────────────────────
    Route::middleware('permission:VIEW_JURNAL_PEMBUKA')->group(function () {
        Route::get('/jurnal-pembuka', [JurnalPembukaController::class, 'index'])->name('jurnal-pembuka.index');

        Route::middleware('permission:CREATE_JURNAL_PEMBUKA')->group(function () {
            Route::get('/jurnal-pembuka/create', [JurnalPembukaController::class, 'create'])->name('jurnal-pembuka.create');
            Route::post('/jurnal-pembuka', [JurnalPembukaController::class, 'store'])->name('jurnal-pembuka.store');
        });

        Route::patch('/jurnal-pembuka/{jurnalPembuka}/posting', [JurnalPembukaController::class, 'posting'])
            ->name('jurnal-pembuka.posting')
            ->whereNumber('jurnalPembuka');

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

        Route::get('/jurnal-pembuka/{jurnalPembuka}', [JurnalPembukaController::class, 'show'])
            ->name('jurnal-pembuka.show')
            ->whereNumber('jurnalPembuka');
    });

    // ── Akuntansi - Jurnal Penyesuaian ────────────────────────────────────
    Route::middleware('permission:VIEW_JURNAL_PENYESUAIAN')->group(function () {
        Route::get('/jurnal-penyesuaian', [JurnalPenyesuaianController::class, 'index'])->name('jurnal-penyesuaian.index');

        Route::middleware('permission:CREATE_JURNAL_PENYESUAIAN')->group(function () {
            Route::get('/jurnal-penyesuaian/create', [JurnalPenyesuaianController::class, 'create'])->name('jurnal-penyesuaian.create');
            Route::get('/jurnal-penyesuaian/aset-detail', [JurnalPenyesuaianController::class, 'getAsetDetail'])->name('jurnal-penyesuaian.aset-detail');
            Route::post('/jurnal-penyesuaian', [JurnalPenyesuaianController::class, 'store'])->name('jurnal-penyesuaian.store');
            Route::post('/jurnal-penyesuaian/bulk-post', [JurnalPenyesuaianController::class, 'bulkPost'])->name('jurnal-penyesuaian.bulk-post');
            Route::delete('/jurnal-penyesuaian/{jurnal}', [JurnalPenyesuaianController::class, 'destroy'])
                ->whereNumber('jurnal')
                ->name('jurnal-penyesuaian.destroy');
        });

        Route::get('/jurnal-penyesuaian/{jurnal}', [JurnalPenyesuaianController::class, 'show'])
            ->whereNumber('jurnal')
            ->name('jurnal-penyesuaian.show');
    });

    // ── Akuntansi - Jurnal Koreksi ─────────────────────────────────────────
    Route::middleware('permission:VIEW_JURNAL_KOREKSI')->group(function () {
        Route::get('/jurnal-koreksi', [JurnalKoreksiController::class, 'index'])->name('jurnal-koreksi.index');

        Route::middleware('permission:CREATE_JURNAL_KOREKSI')->group(function () {
            Route::get('/jurnal-koreksi/create', [JurnalKoreksiController::class, 'create'])->name('jurnal-koreksi.create');
            Route::get('/jurnal-koreksi/aset-detail', [JurnalKoreksiController::class, 'getAsetDetail'])->name('jurnal-koreksi.aset-detail');
            Route::post('/jurnal-koreksi', [JurnalKoreksiController::class, 'store'])->name('jurnal-koreksi.store');
            Route::post('/jurnal-koreksi/bulk-post', [JurnalKoreksiController::class, 'bulkPost'])->name('jurnal-koreksi.bulk-post');
            Route::delete('/jurnal-koreksi/{jurnal}', [JurnalKoreksiController::class, 'destroy'])
                ->whereNumber('jurnal')
                ->name('jurnal-koreksi.destroy');
        });

        Route::get('/jurnal-koreksi/{jurnal}', [JurnalKoreksiController::class, 'show'])
            ->whereNumber('jurnal')
            ->name('jurnal-koreksi.show');
    });

    // ── Akuntansi - Jurnal Penutup ─────────────────────────────────────────
    Route::middleware('permission:VIEW_JURNAL_PENUTUP')->group(function () {
        Route::get('/jurnal-penutup', [JurnalPenutupController::class, 'index'])->name('jurnal-penutup.index');

        Route::middleware('permission:CREATE_JURNAL_PENUTUP')->group(function () {
            Route::get('/jurnal-penutup/create', [JurnalPenutupController::class, 'create'])->name('jurnal-penutup.create');
            Route::get('/jurnal-penutup/aset-detail', [JurnalPenutupController::class, 'getAsetDetail'])->name('jurnal-penutup.aset-detail');
            Route::post('/jurnal-penutup', [JurnalPenutupController::class, 'store'])->name('jurnal-penutup.store');
            Route::post('/jurnal-penutup/post-draft', [JurnalPenutupController::class, 'postDraft'])->name('jurnal-penutup.post-draft');
            Route::post('/jurnal-penutup/konfirmasi-tahap', [JurnalPenutupController::class, 'konfirmasiTahap'])->name('jurnal-penutup.konfirmasi-tahap');
            Route::post('/jurnal-penutup/bulk-post', [JurnalPenutupController::class, 'bulkPost'])->name('jurnal-penutup.bulk-post');
            Route::delete('/jurnal-penutup/{jurnal}', [JurnalPenutupController::class, 'destroy'])
                ->whereNumber('jurnal')
                ->name('jurnal-penutup.destroy');
        });

        Route::get('/jurnal-penutup/{jurnal}', [JurnalPenutupController::class, 'show'])
            ->whereNumber('jurnal')
            ->name('jurnal-penutup.show');
    });

    // ── Laporan Keuangan ───────────────────────────────────────────────────
    Route::prefix('laporan')->name('laporan.')->group(function () {
        Route::get('/penghasilan-komprehensif', [LaporanKeuanganController::class, 'penghasilanKomprehensif'])->name('penghasilan-komprehensif');
        Route::get('/posisi-keuangan', [LaporanKeuanganController::class, 'posisiKeuangan'])->name('posisi-keuangan');
        Route::get('/perubahan-aset-neto', [LaporanKeuanganController::class, 'perubahanAsetNeto'])->name('perubahan-aset-neto');
        Route::get('/arus-kas', [LaporanKeuanganController::class, 'arusKas'])->name('arus-kas');
        Route::get('/calk', [LaporanKeuanganController::class, 'calk'])->name('calk');
        Route::get('/{jenis}/unduh-pdf', [LaporanKeuanganController::class, 'downloadPdf'])
            ->where('jenis', 'posisi-keuangan|penghasilan-komprehensif|perubahan-aset-neto|arus-kas|calk')
            ->name('pdf');
    });
});
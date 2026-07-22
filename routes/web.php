<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Autentikasi\LoginController;
use App\Http\Controllers\Autentikasi\ForgotPasswordController;
use App\Http\Controllers\ManajemenAkses\PeranController;
use App\Http\Controllers\ManajemenAkses\HakAksesController;
use App\Http\Controllers\ManajemenAkses\MenuController;
use App\Http\Controllers\ManajemenAkses\PenggunaController;
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
    Route::get('/penghasilan-komprehensif', [LaporanKeuanganController::class, 'tampilkanLaporan'])
        ->defaults('jenis', 'penghasilan-komprehensif')->name('penghasilan-komprehensif');
    Route::get('/posisi-keuangan', [LaporanKeuanganController::class, 'tampilkanLaporan'])
        ->defaults('jenis', 'posisi-keuangan')->name('posisi-keuangan');
    Route::get('/perubahan-aset-neto', [LaporanKeuanganController::class, 'tampilkanLaporan'])
        ->defaults('jenis', 'perubahan-aset-neto')->name('perubahan-aset-neto');
    Route::get('/arus-kas', [LaporanKeuanganController::class, 'tampilkanLaporan'])
        ->defaults('jenis', 'arus-kas')->name('arus-kas');
    Route::get('/calk', [LaporanKeuanganController::class, 'tampilkanLaporan'])
        ->defaults('jenis', 'calk')->name('calk');
    Route::get('/{jenis}/unduh-pdf', [LaporanKeuanganController::class, 'unduhLaporanPdf'])
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

    // ── Manajemen Pengguna ─────────────────────────────────────────────────────
    Route::middleware('hak_akses:VIEW_PENGGUNA')->group(function () {
        Route::get('/pengguna', [PenggunaController::class, 'tampilkanDaftarPengguna'])->name('pengguna.index');
        Route::get('/pengguna/create', [PenggunaController::class, 'tampilkanFormTambahPengguna'])->name('pengguna.create');
        Route::post('/pengguna', [PenggunaController::class, 'simpanPenggunaBaru'])->name('pengguna.store');
        Route::post('/pengguna/{pengguna}/send-credentials', [PenggunaController::class, 'kirimKredensialPengguna'])->name('pengguna.send-credentials');
        Route::get('/pengguna/{pengguna}/edit', [PenggunaController::class, 'tampilkanFormEditPengguna'])->name('pengguna.edit');
        Route::put('/pengguna/{pengguna}', [PenggunaController::class, 'perbaruiPengguna'])->name('pengguna.update');
        Route::get('/pengguna/{pengguna}/delete', [PenggunaController::class, 'tampilkanKonfirmasiHapusPengguna'])->name('pengguna.confirmDelete');
        Route::delete('/pengguna/{pengguna}', [PenggunaController::class, 'hapusPengguna'])->name('pengguna.destroy');
    });

    Route::middleware('hak_akses:VIEW_PERAN')->group(function () {
        Route::get('/peran', [PeranController::class, 'tampilkanDaftarPeran'])->name('peran.index');
        Route::get('/peran/create', [PeranController::class, 'tampilkanFormTambahPeran'])->name('peran.create');
        Route::get('/peran/{peran}', [PeranController::class, 'tampilkanDetailPeran'])->name('peran.show');
        Route::get('/peran/{peran}/edit', [PeranController::class, 'tampilkanFormEditPeran'])->name('peran.edit');
    });
    Route::middleware('hak_akses:CREATE_PERAN')->group(function () {
        Route::post('/peran', [PeranController::class, 'simpanPeranBaru'])->name('peran.store');
    });
    Route::middleware('hak_akses:EDIT_PERAN')->group(function () {
        Route::put('/peran/{peran}', [PeranController::class, 'perbaruiPeran'])->name('peran.update');
    });
    Route::middleware('hak_akses:DELETE_PERAN')->group(function () {
        Route::delete('/peran/{peran}', [PeranController::class, 'hapusPeran'])->name('peran.destroy');
    });

    // ── Manajemen Akses - HakAkses ─────────────────────────────────────────
    Route::middleware('hak_akses:VIEW_HAK_AKSES')->group(function () {
        Route::get('/hak_akses', [HakAksesController::class, 'tampilkanDaftarHakAkses'])->name('hak-akses.index');
        Route::get('/hak_akses/create', [HakAksesController::class, 'tampilkanFormTambahHakAkses'])->name('hak-akses.create');
        Route::get('/hak_akses/{hak_akses}', [HakAksesController::class, 'tampilkanDetailHakAkses'])->name('hak-akses.show');
        Route::get('/hak_akses/{hak_akses}/edit', [HakAksesController::class, 'tampilkanFormEditHakAkses'])->name('hak-akses.edit');
    });
    Route::middleware('hak_akses:CREATE_HAK_AKSES')->group(function () {
        Route::post('/hak_akses', [HakAksesController::class, 'simpanHakAksesBaru'])->name('hak-akses.store');
    });
    Route::middleware('hak_akses:EDIT_HAK_AKSES')->group(function () {
        Route::put('/hak_akses/{hak_akses}', [HakAksesController::class, 'perbaruiHakAkses'])->name('hak-akses.update');
    });
    Route::middleware('hak_akses:DELETE_HAK_AKSES')->group(function () {
        Route::delete('/hak_akses/{hak_akses}', [HakAksesController::class, 'hapusHakAkses'])->name('hak-akses.destroy');
    });

    // ── Manajemen Akses - Menu ────────────────────────────────────────────────
    Route::middleware('hak_akses:VIEW_MENUS')->group(function () {
        Route::get('/menus', [MenuController::class, 'tampilkanDaftarMenu'])->name('menus.index');
        Route::get('/menus/{menu}', [MenuController::class, 'tampilkanDetailMenu'])->name('menus.show');
    });
    Route::middleware('hak_akses:CREATE_MENUS')->group(function () {
        Route::post('/menus', [MenuController::class, 'simpanMenuBaru'])->name('menus.store');
    });
    Route::middleware('hak_akses:EDIT_MENUS')->group(function () {
        Route::put('/menus/{menu}', [MenuController::class, 'perbaruiMenu'])->name('menus.update');
    });
    Route::middleware('hak_akses:DELETE_MENUS')->group(function () {
        Route::delete('/menus/{menu}', [MenuController::class, 'hapusMenu'])->name('menus.destroy');
    });

    // ── Pencatatan - Transaksi ─────────────────────────────────────────────
    Route::middleware('hak_akses:VIEW_TRANSAKSI')->group(function () {
        Route::get('/transaksi', [TransaksiController::class, 'tampilkanDaftarTransaksi'])->name('transaksi.index');
        Route::get('/transaksi/create', [TransaksiController::class, 'tampilkanFormTambahTransaksi'])->name('transaksi.create');
        Route::get('/transaksi/import', [TransaksiController::class, 'tampilkanFormImporTransaksi'])->name('transaksi.import.create');
        Route::get('/transaksi/import/review', [TransaksiController::class, 'tampilkanReviewImpor'])->name('transaksi.import.review');

        Route::get('/transaksi/{transaksi}', [TransaksiController::class, 'tampilkanDetailTransaksi'])->name('transaksi.show')->whereNumber('transaksi');
        Route::get('/transaksi/{transaksi}/edit', [TransaksiController::class, 'tampilkanFormEditTransaksi'])->name('transaksi.edit')->whereNumber('transaksi');
    });

    Route::middleware('hak_akses:CREATE_TRANSAKSI')->group(function () {
        Route::post('/transaksi', [TransaksiController::class, 'simpanTransaksiBaru'])->name('transaksi.store');
        Route::post('/transaksi/import', [TransaksiController::class, 'imporMutasiBank'])->name('transaksi.import');
        Route::post('/transaksi/import/simpan', [TransaksiController::class, 'simpanHasilImpor'])->name('transaksi.import.simpan');
    });

    Route::middleware('hak_akses:EDIT_TRANSAKSI')->group(function () {
        Route::put('/transaksi/{transaksi}', [TransaksiController::class, 'perbaruiTransaksi'])->name('transaksi.update')->whereNumber('transaksi');
    });

    Route::middleware('hak_akses:DELETE_TRANSAKSI')->group(function () {
        Route::delete('/transaksi/{transaksi}', [TransaksiController::class, 'hapusTransaksi'])->name('transaksi.destroy')->whereNumber('transaksi');
        Route::delete('/transaksi/bukti/{bukti}', [TransaksiController::class, 'hapusBuktiTransaksi'])->name('transaksi.bukti.destroy')->whereNumber('bukti');
    });

    // ── Pencatatan - Kencleng ──────────────────────────────────────────────
    Route::middleware('hak_akses:VIEW_KENCLENG')->group(function () {
        Route::get('/kencleng', [KenclengController::class, 'tampilkanDaftarKencleng'])->name('kencleng.index');
        Route::get('/kencleng/{kencleng}', [KenclengController::class, 'tampilkanDetailKencleng'])
            ->name('kencleng.show')
            ->whereNumber('kencleng');
        Route::get('/kencleng/create', [KenclengController::class, 'tampilkanFormTambahKencleng'])->name('kencleng.create');
        Route::get('/kencleng/{kencleng}/edit', [KenclengController::class, 'tampilkanFormEditKencleng'])->name('kencleng.edit');
    });
 
    Route::middleware('hak_akses:CREATE_KENCLENG')->group(function () {
        Route::post('/kencleng', [KenclengController::class, 'simpanKenclengBaru'])->name('kencleng.store');
    });
 
    Route::middleware('hak_akses:EDIT_KENCLENG')->group(function () {
        Route::put('/kencleng/{kencleng}', [KenclengController::class, 'perbaruiKencleng'])->name('kencleng.update');
    });
 
    Route::middleware('hak_akses:DELETE_KENCLENG')->group(function () {
        Route::delete('/kencleng/{kencleng}', [KenclengController::class, 'hapusKencleng'])->name('kencleng.destroy');
    });

    // ── Kegiatan Khusus - Data Kegiatan ────────────────────────────────────
    Route::middleware('hak_akses:VIEW_KEGIATAN')->group(function () {
        Route::get('/kegiatan',            [KegiatanController::class, 'tampilkanDaftarKegiatan'])->name('kegiatan.index');
        Route::get('/kegiatan/{kegiatan}', [KegiatanController::class, 'tampilkanDetailKegiatan'])->name('kegiatan.show')->whereNumber('kegiatan');
    });

    Route::middleware('hak_akses:CREATE_KEGIATAN')->group(function () {
        Route::get('/kegiatan/create',  [KegiatanController::class, 'tampilkanFormTambahKegiatan'])->name('kegiatan.create');
        Route::post('/kegiatan',        [KegiatanController::class, 'simpanKegiatanBaru'])->name('kegiatan.store');
    });

    Route::middleware('hak_akses:EDIT_KEGIATAN')->group(function () {
        Route::get('/kegiatan/{kegiatan}/edit', [KegiatanController::class, 'tampilkanFormUbahKegiatan'])->name('kegiatan.edit');
        Route::put('/kegiatan/{kegiatan}',      [KegiatanController::class, 'perbaruiKegiatan'])->name('kegiatan.update');
    });

    Route::middleware('hak_akses:DELETE_KEGIATAN')->group(function () {
        Route::get('/kegiatan/{kegiatan}/delete', [KegiatanController::class, 'tampilkanKonfirmasiHapusKegiatan'])->name('kegiatan.confirmDelete')->whereNumber('kegiatan');
        Route::delete('/kegiatan/{kegiatan}',     [KegiatanController::class, 'hapusKegiatan'])->name('kegiatan.destroy');
    });

    // ── Kegiatan Khusus - Transaksi Kegiatan ───────────────────────────────
    Route::middleware('hak_akses:VIEW_TRANSAKSI_KEGIATAN')->group(function () {
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
        Route::get('/transaksi-kegiatan/{kegiatan}/transaksi/{transaksi}/edit', [TransaksiKegiatanController::class, 'editTransaksi'])
            ->whereNumber('kegiatan')
            ->whereNumber('transaksi')
            ->name('transaksi-kegiatan.transaksi.edit');
    });
 
    Route::middleware('hak_akses:CREATE_TRANSAKSI_KEGIATAN')->group(function () {
        Route::post('/transaksi-kegiatan/{kegiatan}/transaksi', [TransaksiKegiatanController::class, 'simpanTransaksiKegiatan'])
            ->name('transaksi-kegiatan.transaksi.store');
    });
 
    Route::middleware('hak_akses:EDIT_TRANSAKSI_KEGIATAN')->group(function () {
        Route::put('/transaksi-kegiatan/{kegiatan}/transaksi/{transaksi}', [TransaksiKegiatanController::class, 'perbaruiTransaksiKegiatan'])
            ->whereNumber('kegiatan')
            ->whereNumber('transaksi')
            ->name('transaksi-kegiatan.transaksi.update');
    });
 
    Route::middleware('hak_akses:DELETE_TRANSAKSI_KEGIATAN')->group(function () {
        Route::delete('/transaksi-kegiatan/{kegiatan}/transaksi/{transaksi}', [TransaksiKegiatanController::class, 'hapusTransaksiKegiatan'])
            ->name('transaksi-kegiatan.transaksi.destroy');
    });

    // ── Approval ───────────────────────────────────────────────────────────
    Route::middleware('hak_akses:VIEW_APPROVAL')->group(function () {
        Route::get('/approval/transaksi', [ApprovalController::class, 'tampilkanDaftarApproval'])->name('approval.index');
        Route::get('/approval/transaksi/{transaksi}', [ApprovalController::class, 'tampilkanDetailApproval'])->name('approval.show');
    });
 
    Route::middleware('hak_akses:EDIT_APPROVAL')->group(function () {
        Route::post('/approval/transaksi/bulk-approve', [ApprovalController::class, 'setujuiTransaksiMassal'])->name('approval.bulk-approve');
        Route::post('/approval/transaksi/bulk-reject', [ApprovalController::class, 'tolakTransaksiMassal'])->name('approval.bulk-reject');
        Route::post('/approval/transaksi/bulk-revisi', [ApprovalController::class, 'revisiTransaksiMassal'])->name('approval.bulk-revisi');
        Route::post('/approval/transaksi/{transaksi}/approve', [ApprovalController::class, 'setujuiTransaksi'])->name('approval.approve');
        Route::post('/approval/transaksi/{transaksi}/reject', [ApprovalController::class, 'tolakTransaksi'])->name('approval.reject');
        Route::post('/approval/transaksi/{transaksi}/revision', [ApprovalController::class, 'revisiTransaksi'])->name('approval.revision');
    });

    // ── Manajemen Aset ─────────────────────────────────────────────────────
    Route::middleware('hak_akses:VIEW_ASET')->group(function () {
        Route::get('/aset', [AsetController::class, 'tampilkanDaftarAset'])->name('aset.index');
        // Menampilkan modal form (create/edit) termasuk hak VIEW: Administrator boleh
        // MELIHAT modal tambah/ubah aset. Alur SIMPAN (store/update) tetap dijaga
        // hak_akses CREATE_ASET / EDIT_ASET di bawah -- sesuai pola "guard di route POST".
        Route::get('/aset/create', [AsetController::class, 'tampilkanFormTambahAset'])->name('aset.create');
        Route::get('/aset/{aset}/edit', [AsetController::class, 'tampilkanFormUbahAset'])
            ->name('aset.edit')
            ->whereNumber('aset');
        Route::get('/aset/{aset}', [AsetController::class, 'tampilkanDetailAset'])
            ->name('aset.show')
            ->whereNumber('aset');
    });

    // Alur SIMPAN aset baru -- hanya peran berhak (Sekretaris). Admin: ditolak (403).
    Route::middleware('hak_akses:CREATE_ASET')->group(function () {
        Route::post('/aset', [AsetController::class, 'simpanAsetBaru'])->name('aset.store');
    });

    // Alur UBAH / toggle status aset -- hanya peran berhak (Sekretaris). Admin: ditolak (403).
    Route::middleware('hak_akses:EDIT_ASET')->group(function () {
        Route::put('/aset/{aset}', [AsetController::class, 'perbaruiAset'])->name('aset.update');
        Route::patch('/aset/{aset}/toggle-status', [AsetController::class, 'ubahStatusAset'])
            ->name('aset.toggle-status');
    });

    Route::middleware('hak_akses:DELETE_ASET')->group(function () {
        Route::delete('/aset/{aset}', [AsetController::class, 'hapusAset'])->name('aset.destroy');
    });

    // ── Akuntansi - Jurnal Umum ────────────────────────────────────────────
    Route::middleware('hak_akses:VIEW_JURNAL')->group(function () {
        Route::get('/jurnal-umum', [JurnalUmumController::class, 'tampilkanJurnalUmum'])->name('jurnal-umum.index');

        Route::middleware('hak_akses:CREATE_JURNAL')->group(function () {
            Route::post('/jurnal-umum/bulk-post', [JurnalUmumController::class, 'bulkPost'])->name('jurnal-umum.bulk-post');
            Route::post('/jurnal-umum/{jurnalUmum}/post', [JurnalUmumController::class, 'post'])->name('jurnal-umum.post');
        });

        Route::middleware('hak_akses:DELETE_JURNAL')->group(function () {
            Route::delete('/jurnal-umum/{jurnalUmum}', [JurnalUmumController::class, 'hapusJurnalUmum'])->name('jurnal-umum.destroy');
        });

        Route::get('/jurnal-umum/{jurnalUmum}', [JurnalUmumController::class, 'tampilkanDetailJurnalUmum'])
            ->whereNumber('jurnalUmum')
            ->name('jurnal-umum.show');
    });

    // ── Akuntansi - Buku Besar ─────────────────────────────────────────────
    Route::middleware('hak_akses:VIEW_BUKU_BESAR')->group(function () {
        Route::get('/buku-besar', [BukuBesarController::class, 'tampilkanBukuBesar'])->name('buku-besar.index');
    });

    // ── Akuntansi - Neraca Saldo ───────────────────────────────────────────
    Route::middleware('hak_akses:VIEW_NERACA_SALDO')->group(function () {
        Route::get('/neraca-saldo', [NeracaSaldoController::class, 'tampilkanNeracaSaldo'])->name('neraca-saldo.index');
    });

    // ── Master Data - Chart of Accounts ────────────────────────────────────
    Route::middleware('hak_akses:VIEW_COA')->prefix('chart-of-account')->name('coa.')->group(function () {
        Route::get('/', [ChartOfAccountController::class, 'tampilkanDaftarCoa'])->name('index');
 
        Route::get('/kategori/create', [ChartOfAccountController::class, 'tampilkanFormTambahKategoriAkun'])->name('kategori.create');
        Route::get('/sub-kategori/create', [ChartOfAccountController::class, 'tampilkanFormTambahSubKategori'])->name('sub-kategori.create');
        Route::get('/akun/create', [ChartOfAccountController::class, 'tampilkanFormTambahAkun'])->name('akun.create');
 
        Route::middleware('hak_akses:CREATE_COA')->group(function () {
            Route::post('/kategori', [ChartOfAccountController::class, 'simpanKategoriAkunBaru'])->name('kategori.store');
            Route::post('/sub-kategori', [ChartOfAccountController::class, 'simpanSubKategoriBaru'])->name('sub-kategori.store');
            Route::post('/akun', [ChartOfAccountController::class, 'simpanAkunBaru'])->name('akun.store');
        });
 
        // GET form ubah termasuk hak VIEW: Administrator boleh MELIHAT form ubah.
        Route::get('/kategori/{kategori}/edit', [ChartOfAccountController::class, 'tampilkanFormUbahKategoriAkun'])->name('kategori.edit');
        Route::get('/sub-kategori/{subKategori}/edit', [ChartOfAccountController::class, 'tampilkanFormUbahSubKategori'])->name('sub-kategori.edit');
        Route::get('/akun/{akun}/edit', [ChartOfAccountController::class, 'tampilkanFormUbahAkun'])->name('akun.edit');
 
        // UBAH data -- hanya peran berhak (mis. Bendahara 1). Admin: ditolak (403).
        Route::middleware('hak_akses:EDIT_COA')->group(function () {
            Route::put('/kategori/{kategori}', [ChartOfAccountController::class, 'perbaruiKategoriAkun'])->name('kategori.update');
            Route::put('/sub-kategori/{subKategori}', [ChartOfAccountController::class, 'perbaruiSubKategori'])->name('sub-kategori.update');
            Route::put('/akun/{akun}', [ChartOfAccountController::class, 'perbaruiAkun'])->name('akun.update');
        });
        
        // HAPUS data -- hanya peran berhak. Admin: ditolak (403).
        Route::middleware('hak_akses:DELETE_COA')->group(function () {
            Route::delete('/kategori/{kategori}', [ChartOfAccountController::class, 'hapusKategoriAkun'])->name('kategori.destroy');
            Route::delete('/sub-kategori/{subKategori}', [ChartOfAccountController::class, 'hapusSubKategori'])->name('sub-kategori.destroy');
            Route::delete('/akun/{akun}', [ChartOfAccountController::class, 'hapusAkun'])->name('akun.destroy');
        });
    });
 
    // ── Master Data - Kategori Transaksi ───────────────────────────────────
    Route::middleware('hak_akses:VIEW_KATEGORI')->group(function () {
        Route::get('/kategori-transaksi', [KategoriTransaksiController::class, 'tampilkanDaftarKategoriTransaksi'])->name('kategori-transaksi.index');
        Route::get('/kategori-transaksi/create', [KategoriTransaksiController::class, 'tampilkanFormTambahKategoriTransaksi'])->name('kategori-transaksi.create');
        Route::get('/kategori-transaksi/{kategoriTransaksi}/edit', [KategoriTransaksiController::class, 'tampilkanFormUbahKategoriTransaksi'])->name('kategori-transaksi.edit');
 
        Route::middleware('hak_akses:CREATE_KATEGORI')->group(function () {
            Route::post('/kategori-transaksi', [KategoriTransaksiController::class, 'simpanKategoriTransaksiBaru'])->name('kategori-transaksi.store');
        });
 
        Route::middleware('hak_akses:EDIT_KATEGORI')->group(function () {
            Route::put('/kategori-transaksi/{kategoriTransaksi}', [KategoriTransaksiController::class, 'perbaruiKategoriTransaksi'])->name('kategori-transaksi.update');
        });
        
        // HAPUS data -- hanya peran berhak. Admin: ditolak (403).
        Route::middleware('hak_akses:DELETE_KATEGORI')->group(function () {
            Route::delete('/kategori-transaksi/{kategoriTransaksi}', [KategoriTransaksiController::class, 'hapusKategoriTransaksi'])->name('kategori-transaksi.destroy');
        });
    });

    // ── Akuntansi - Jurnal Pembuka ─────────────────────────────────────────
    Route::middleware('hak_akses:VIEW_JURNAL_PEMBUKA')->group(function () {
        Route::get('/jurnal-pembuka', [JurnalPembukaController::class, 'tampilkanJurnalPembuka'])->name('jurnal-pembuka.index');
        Route::get('/jurnal-pembuka/create', [JurnalPembukaController::class, 'tambahJurnalPembuka'])->name('jurnal-pembuka.create');
        Route::get('/jurnal-pembuka/{jurnalPembuka}/edit', [JurnalPembukaController::class, 'ubahJurnalPembuka'])->name('jurnal-pembuka.edit')->whereNumber('jurnalPembuka');

        Route::middleware('hak_akses:CREATE_JURNAL_PEMBUKA')->group(function () {
            Route::post('/jurnal-pembuka', [JurnalPembukaController::class, 'simpanJurnalPembuka'])->name('jurnal-pembuka.store');
        });

        Route::middleware('hak_akses:EDIT_JURNAL_PEMBUKA')->group(function () {
            Route::put('/jurnal-pembuka/{jurnalPembuka}', [JurnalPembukaController::class, 'perbaruiJurnalPembuka'])
                ->name('jurnal-pembuka.update')
                ->whereNumber('jurnalPembuka');
        });

        Route::middleware('hak_akses:DELETE_JURNAL_PEMBUKA')->group(function () {
            Route::delete('/jurnal-pembuka/{jurnalPembuka}', [JurnalPembukaController::class, 'hapusJurnalPembuka'])
                ->name('jurnal-pembuka.destroy')
                ->whereNumber('jurnalPembuka');
        });

        Route::get('/jurnal-pembuka/{jurnalPembuka}', [JurnalPembukaController::class, 'tampilkanDetailJurnalPembuka'])
            ->name('jurnal-pembuka.show')
            ->whereNumber('jurnalPembuka');
    });

    // ── Akuntansi - Jurnal Penyesuaian ────────────────────────────────────
    Route::middleware('hak_akses:VIEW_JURNAL_PENYESUAIAN')->group(function () {
        Route::get('/jurnal-penyesuaian', [JurnalPenyesuaianController::class, 'tampilkanJurnalPenyesuaian'])->name('jurnal-penyesuaian.index');
        Route::get('/jurnal-penyesuaian/create', [JurnalPenyesuaianController::class, 'tambahJurnalPenyesuaian'])->name('jurnal-penyesuaian.create');
        Route::get('/jurnal-penyesuaian/aset-detail', [JurnalPenyesuaianController::class, 'getAsetDetail'])->name('jurnal-penyesuaian.aset-detail');
        Route::get('/jurnal-penyesuaian/{jurnal}', [JurnalPenyesuaianController::class, 'tampilkanDetailJurnalPenyesuaian'])
            ->whereNumber('jurnal')->name('jurnal-penyesuaian.show');
    });
 
    Route::middleware('hak_akses:CREATE_JURNAL_PENYESUAIAN')->group(function () {
        Route::post('/jurnal-penyesuaian', [JurnalPenyesuaianController::class, 'simpanJurnalPenyesuaian'])->name('jurnal-penyesuaian.store');
        Route::post('/jurnal-penyesuaian/bulk-post', [JurnalPenyesuaianController::class, 'bulkPost'])->name('jurnal-penyesuaian.bulk-post');
    });
 
    Route::middleware('hak_akses:DELETE_JURNAL_PENYESUAIAN')->group(function () {
        Route::delete('/jurnal-penyesuaian/{jurnal}', [JurnalPenyesuaianController::class, 'hapusJurnalPenyesuaian'])
            ->whereNumber('jurnal')->name('jurnal-penyesuaian.destroy');
    });

    // ── Akuntansi - Jurnal Koreksi ─────────────────────────────────────────
    Route::middleware('hak_akses:VIEW_JURNAL_KOREKSI')->group(function () {
        Route::get('/jurnal-koreksi', [JurnalKoreksiController::class, 'tampilkanJurnalKoreksi'])->name('jurnal-koreksi.index');
        Route::get('/jurnal-koreksi/create', [JurnalKoreksiController::class, 'tambahJurnalKoreksi'])->name('jurnal-koreksi.create');
        Route::get('/jurnal-koreksi/aset-detail', [JurnalKoreksiController::class, 'getAsetDetail'])->name('jurnal-koreksi.aset-detail');
        Route::get('/jurnal-koreksi/{jurnal}', [JurnalKoreksiController::class, 'tampilkanDetailJurnalKoreksi'])
            ->whereNumber('jurnal')->name('jurnal-koreksi.show');
    });
 
    Route::middleware('hak_akses:CREATE_JURNAL_KOREKSI')->group(function () {
        Route::post('/jurnal-koreksi', [JurnalKoreksiController::class, 'simpanJurnalKoreksi'])->name('jurnal-koreksi.store');
        Route::post('/jurnal-koreksi/bulk-post', [JurnalKoreksiController::class, 'bulkPost'])->name('jurnal-koreksi.bulk-post');
    });
 
    Route::middleware('hak_akses:DELETE_JURNAL_KOREKSI')->group(function () {
        Route::delete('/jurnal-koreksi/{jurnal}', [JurnalKoreksiController::class, 'hapusJurnalKoreksi'])
            ->whereNumber('jurnal')->name('jurnal-koreksi.destroy');
    });

    // ── Akuntansi - Jurnal Penutup ─────────────────────────────────────────
    Route::middleware('hak_akses:VIEW_JURNAL_PENUTUP')->group(function () {
        Route::get('/jurnal-penutup', [JurnalPenutupController::class, 'tampilkanJurnalPenutup'])->name('jurnal-penutup.index');
        Route::get('/jurnal-penutup/create', [JurnalPenutupController::class, 'tambahJurnalPenutup'])->name('jurnal-penutup.create');
        Route::get('/jurnal-penutup/aset-detail', [JurnalPenutupController::class, 'getAsetDetail'])->name('jurnal-penutup.aset-detail');
        Route::get('/jurnal-penutup/{jurnal}', [JurnalPenutupController::class, 'tampilkanDetailJurnalPenutup'])
            ->whereNumber('jurnal')->name('jurnal-penutup.show');
    });
 
    Route::middleware('hak_akses:CREATE_JURNAL_PENUTUP')->group(function () {
        Route::post('/jurnal-penutup', [JurnalPenutupController::class, 'simpanJurnalPenutup'])->name('jurnal-penutup.store');
        Route::post('/jurnal-penutup/post-draft', [JurnalPenutupController::class, 'postDraft'])->name('jurnal-penutup.post-draft');
        Route::post('/jurnal-penutup/konfirmasi-tahap', [JurnalPenutupController::class, 'konfirmasiTahap'])->name('jurnal-penutup.konfirmasi-tahap');
        Route::post('/jurnal-penutup/bulk-post', [JurnalPenutupController::class, 'bulkPost'])->name('jurnal-penutup.bulk-post');
    });
 
    Route::middleware('hak_akses:DELETE_JURNAL_PENUTUP')->group(function () {
        Route::delete('/jurnal-penutup/{jurnal}', [JurnalPenutupController::class, 'hapusJurnalPenutup'])
            ->whereNumber('jurnal')->name('jurnal-penutup.destroy');
    });

    // ── Laporan Keuangan ───────────────────────────────────────────────────
    Route::prefix('laporan')->name('laporan.')->group(function () {
        Route::get('/penghasilan-komprehensif', [LaporanKeuanganController::class, 'tampilkanLaporan'])
            ->defaults('jenis', 'penghasilan-komprehensif')->name('penghasilan-komprehensif');
        Route::get('/posisi-keuangan', [LaporanKeuanganController::class, 'tampilkanLaporan'])
            ->defaults('jenis', 'posisi-keuangan')->name('posisi-keuangan');
        Route::get('/perubahan-aset-neto', [LaporanKeuanganController::class, 'tampilkanLaporan'])
            ->defaults('jenis', 'perubahan-aset-neto')->name('perubahan-aset-neto');
        Route::get('/arus-kas', [LaporanKeuanganController::class, 'tampilkanLaporan'])
            ->defaults('jenis', 'arus-kas')->name('arus-kas');
        Route::get('/calk', [LaporanKeuanganController::class, 'tampilkanLaporan'])
            ->defaults('jenis', 'calk')->name('calk');
        Route::get('/{jenis}/unduh-pdf', [LaporanKeuanganController::class, 'unduhLaporanPdf'])
            ->where('jenis', 'posisi-keuangan|penghasilan-komprehensif|perubahan-aset-neto|arus-kas|calk')
            ->name('pdf');
    });
});
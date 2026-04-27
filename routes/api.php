<?php

use App\Http\Controllers\Api\JadwalShalatController;
use Illuminate\Support\Facades\Route;

Route::prefix('jadwal-shalat')->name('api.jadwal-shalat.')->group(function () {

    /**
     * GET /api/jadwal-shalat
     * Mengambil jadwal shalat berdasarkan kota dan tanggal.
     *
     * Query params:
     *   - kota    : ID kota (opsional, default: 1301 = Bandung)
     *   - tanggal : Format YYYY-MM-DD (opsional, default: hari ini)
     *
     * Response:
     * {
     *   "status": "ok",
     *   "data": {
     *     "tanggal": "Sabtu, 25 April 2026",
     *     "imsak": "04:23",
     *     "subuh": "04:33",
     *     ...
     *   }
     * }
     */
    Route::get('/', [JadwalShalatController::class, 'index'])->name('index');

    /**
     * GET /api/jadwal-shalat/kota
     * Mengambil daftar semua kota yang tersedia.
     *
     * Response:
     * {
     *   "status": "ok",
     *   "data": [
     *     { "id": "1301", "nama": "BANDUNG" },
     *     ...
     *   ]
     * }
     */
    Route::get('/kota', [JadwalShalatController::class, 'listKota'])->name('kota');
});

Route::prefix('donasi')->name('api.donasi.')->group(function () {

    /**
     * GET /api/donasi/program
     * Daftar program donasi yang aktif.
     */
    Route::get('/program', [\App\Http\Controllers\Api\DonasiController::class, 'listProgram'])->name('program');

    /**
     * POST /api/donasi
     * Submit donasi baru (integrasi payment gateway).
     */
    Route::post('/', [\App\Http\Controllers\Api\DonasiController::class, 'store'])->name('store');

    /**
     * POST /api/donasi/callback
     * Webhook callback dari payment gateway (Midtrans, dll.)
     */
    Route::post('/callback', [\App\Http\Controllers\Api\DonasiController::class, 'callback'])
        ->name('callback')
        ->withoutMiddleware('auth:sanctum'); // Callback tidak memerlukan auth
});


Route::prefix('laporan')->name('api.laporan.')->group(function () {

    /**
     * GET /api/laporan/ringkasan
     * Ringkasan keuangan untuk ditampilkan di landing page.
     */
    Route::get('/ringkasan', [\App\Http\Controllers\Api\LaporanController::class, 'ringkasan'])->name('ringkasan');
});


Route::middleware('auth:sanctum')->prefix('v1')->name('api.v1.')->group(function () {

    // User info
    Route::get('/user', function (\Illuminate\Http\Request $request) {
        return response()->json(['data' => $request->user()]);
    })->name('user');

    // Transaksi
    Route::apiResource('transaksi', \App\Http\Controllers\Api\TransaksiController::class);

    // Program Infaq (CRUD untuk admin)
    Route::apiResource('program', \App\Http\Controllers\Api\ProgramController::class);

    // Kegiatan (CRUD untuk admin)
    Route::apiResource('kegiatan', \App\Http\Controllers\Api\KegiatanController::class);

    // Donatur
    Route::apiResource('donatur', \App\Http\Controllers\Api\DonaturController::class);

    // Laporan (Protected)
    Route::prefix('laporan')->name('laporan.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\LaporanController::class, 'index'])->name('index');
        Route::get('/export', [\App\Http\Controllers\Api\LaporanController::class, 'export'])->name('export');
    });
});
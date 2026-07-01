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
<?php
// app/Http/Controllers/CalkController.php

namespace App\Http\Controllers;

use App\Models\Akun;
use App\Models\Aset;
use App\Models\DetailJurnal;
use App\Models\Periode;
use Illuminate\Http\Request;

class CalkController extends Controller
{
    public function index(Request $request)
    {
        $periodeId = $request->input('periode_id', '');
        $periode   = $periodeId ? Periode::find($periodeId) : Periode::aktif()->latest('tanggal_awal')->first();

        // Hitung ringkasan keuangan
        $baseQuery = fn() => DetailJurnal::whereHas('jurnal', function ($q) use ($periodeId) {
            $q->where('status', 'POSTED');
            if ($periodeId) $q->where('periode_id', $periodeId);
        });

        // Total Aset = semua akun dengan kode 1-xxx
        $totalAset = ($baseQuery)()->whereHas('akun', fn($a) =>
            $a->whereHas('kategoriAkun', fn($k) => $k->where('kode_kategori', '1'))
        )->where('tipe', 'DEBIT')->sum('nominal');

        // Total Liabilitas = kode 2-xxx
        $totalLiabilitas = ($baseQuery)()->whereHas('akun', fn($a) =>
            $a->whereHas('kategoriAkun', fn($k) => $k->where('kode_kategori', '2'))
        )->where('tipe', 'KREDIT')->sum('nominal');

        // Total Aset Neto = kode 3-xxx
        $totalAsetNeto = ($baseQuery)()->whereHas('akun', fn($a) =>
            $a->whereHas('kategoriAkun', fn($k) => $k->where('kode_kategori', '3'))
        )->where('tipe', 'KREDIT')->sum('nominal');

        // Kas & Bank
        $kasSetaraKas = ($baseQuery)()->whereHas('akun', fn($a) =>
            $a->where('kode_akun', 'like', '1-11%')
        )->where('tipe', 'DEBIT')->sum('nominal');

        // Aset Tetap
        $asets = Aset::all();
        $totalAsetTetap = $asets->sum('nilai_buku_real_time');

        // Pendapatan & Beban
        $totalPendapatan = ($baseQuery)()->whereHas('akun', fn($a) =>
            $a->whereHas('kategoriAkun', fn($k) => $k->where('kode_kategori', '4'))
        )->where('tipe', 'KREDIT')->sum('nominal');

        $totalBeban = ($baseQuery)()->whereHas('akun', fn($a) =>
            $a->whereHas('kategoriAkun', fn($k) => $k->where('kode_kategori', '5'))
        )->where('tipe', 'DEBIT')->sum('nominal');

        // Infak & Sedekah (kategori 4-1xxx)
        $totalInfakSedekah = ($baseQuery)()->whereHas('akun', fn($a) =>
            $a->where('kode_akun', 'like', '4-1%')
        )->where('tipe', 'KREDIT')->sum('nominal');

        $periodes = Periode::orderByDesc('tanggal_awal')->get();
        $jumlahCatatan = 8; // sesuai gambar

        return view('pages.calk.index', compact(
            'periode', 'periodes',
            'totalAset', 'totalLiabilitas', 'totalAsetNeto',
            'kasSetaraKas', 'totalAsetTetap', 'asets',
            'totalPendapatan', 'totalBeban', 'totalInfakSedekah',
            'jumlahCatatan', 'periodeId'
        ));
    }
}
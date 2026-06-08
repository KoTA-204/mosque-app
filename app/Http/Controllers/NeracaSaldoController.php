<?php
// app/Http/Controllers/NeracaSaldoController.php

namespace App\Http\Controllers;

use App\Models\Akun;
use App\Models\DetailJurnal;
use App\Models\Periode;
use Illuminate\Http\Request;

class NeracaSaldoController extends Controller
{
    public function index(Request $request)
    {
        $periodeId  = $request->input('periode_id', '');
        $akunFilter = $request->input('akun_filter', '');
        $sortBy     = $request->input('sort_by', 'kode_akun_asc');
        $perPage    = $request->input('per_page', 10);

        $akunQuery = Akun::with('kategoriAkun')
            ->whereNotNull('parent_id')
            ->when($akunFilter && $akunFilter !== 'semua', function ($q) use ($akunFilter) {
                $q->whereHas('kategoriAkun', fn($k) =>
                    $k->where('kode_kategori', $akunFilter)
                );
            });

        match($sortBy) {
            'kode_akun_desc' => $akunQuery->orderByDesc('kode_akun'),
            'nama_asc'       => $akunQuery->orderBy('nama_akun'),
            default          => $akunQuery->orderBy('kode_akun'),
        };

        // Ambil SEMUA akun yang cocok filter untuk hitung grand total
        $allAkuns = (clone $akunQuery)->get();
        $allAkunIds = $allAkuns->pluck('id')->toArray();

        // Hitung saldo semua akun sekaligus
        $semuaSaldo = DetailJurnal::whereIn('akun_id', $allAkunIds)
            ->whereHas('jurnal', function ($q) use ($periodeId) {
                $q->where('status', 'POSTED');
                if ($periodeId) $q->where('periode_id', $periodeId);
            })
            ->selectRaw('akun_id, tipe, SUM(nominal) as total')
            ->groupBy('akun_id', 'tipe')
            ->get()
            ->groupBy('akun_id');

        // Grand total dari SEMUA data (bukan per halaman)
        $grandTotalDebit  = 0;
        $grandTotalKredit = 0;
        foreach ($allAkuns as $akun) {
            $rows = $semuaSaldo->get($akun->id, collect());
            $grandTotalDebit  += $rows->where('tipe', 'DEBIT')->sum('total');
            $grandTotalKredit += $rows->where('tipe', 'KREDIT')->sum('total');
        }
        $selisih = $grandTotalDebit - $grandTotalKredit;

        // Paginate akun
        $akuns = $akunQuery->paginate($perPage)->withQueryString();

        // Map saldo ke akun yang ditampilkan di halaman ini
        $akuns->getCollection()->transform(function ($akun) use ($semuaSaldo) {
            $rows = $semuaSaldo->get($akun->id, collect());
            $akun->total_debit  = $rows->where('tipe', 'DEBIT')->sum('total');
            $akun->total_kredit = $rows->where('tipe', 'KREDIT')->sum('total');
            return $akun;
        });

        $periodes      = Periode::orderByDesc('tanggal_awal')->get();
        $kategoriAkuns = \App\Models\KategoriAkun::orderBy('kode_kategori')->get();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('pages.neraca-saldo.table', compact(
                    'akuns', 'grandTotalDebit', 'grandTotalKredit', 'selisih'
                ))->render(),
            ]);
        }

        return view('pages.neraca-saldo.index', compact(
            'akuns', 'periodes', 'kategoriAkuns',
            'grandTotalDebit', 'grandTotalKredit', 'selisih',
            'periodeId', 'akunFilter', 'sortBy'
        ));
    }
}
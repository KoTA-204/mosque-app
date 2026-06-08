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
        $periodeId = $request->input('periode_id', '');
        $akunFilter = $request->input('akun_filter', ''); // 'semua', 'aset', 'liabilitas', etc.
        $sortBy    = $request->input('sort_by', 'kode_akun_asc');
        $perPage   = $request->input('per_page', 10);

        // Ambil semua akun child (leaf nodes)
        $akunQuery = Akun::with('kategoriAkun')
            ->whereNotNull('parent_id')
            ->when($akunFilter && $akunFilter !== 'semua', function ($q) use ($akunFilter) {
                $q->whereHas('kategoriAkun', fn($k) =>
                    $k->where('kode_kategori', $akunFilter)
                );
            });

        // Sorting
        match($sortBy) {
            'kode_akun_asc'  => $akunQuery->orderBy('kode_akun'),
            'kode_akun_desc' => $akunQuery->orderByDesc('kode_akun'),
            'nama_asc'       => $akunQuery->orderBy('nama_akun'),
            default          => $akunQuery->orderBy('kode_akun'),
        };

        $akuns = $akunQuery->paginate($perPage)->withQueryString();

        // Hitung debit & kredit per akun dari detail jurnal
        $akunIds = $akuns->pluck('id')->toArray();

        $saldos = DetailJurnal::whereIn('akun_id', $akunIds)
            ->whereHas('jurnal', function ($q) use ($periodeId) {
                $q->where('status', 'POSTED');
                if ($periodeId) $q->where('periode_id', $periodeId);
            })
            ->selectRaw('akun_id, tipe, SUM(nominal) as total')
            ->groupBy('akun_id', 'tipe')
            ->get()
            ->groupBy('akun_id');

        // Map saldo ke tiap akun
        $akuns->getCollection()->transform(function ($akun) use ($saldos) {
            $rows   = $saldos->get($akun->id, collect());
            $akun->total_debit  = $rows->where('tipe', 'DEBIT')->sum('total');
            $akun->total_kredit = $rows->where('tipe', 'KREDIT')->sum('total');
            return $akun;
        });

        // Grand total
        $grandTotalDebit  = $akuns->getCollection()->sum('total_debit');
        $grandTotalKredit = $akuns->getCollection()->sum('total_kredit');
        $selisih          = $grandTotalDebit - $grandTotalKredit;

        $periodes  = Periode::orderByDesc('tanggal_awal')->get();
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
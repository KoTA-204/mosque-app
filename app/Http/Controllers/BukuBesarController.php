<?php
// app/Http/Controllers/BukuBesarController.php

namespace App\Http\Controllers;

use App\Models\Akun;
use App\Models\DetailJurnal;
use App\Models\Periode;
use Illuminate\Http\Request;

class BukuBesarController extends Controller
{
    public function index(Request $request)
    {
        $periodeId = $request->input('periode_id', '');
        $akunId    = $request->input('akun_id', '');
        $perPage   = $request->input('per_page', 10);

        $query = DetailJurnal::with(['jurnal.periode', 'akun', 'jurnal.transaksi.dompet'])
            ->whereHas('jurnal', function ($q) use ($periodeId) {
                $q->where('status', 'POSTED');
                if ($periodeId) $q->where('periode_id', $periodeId);
            })
            ->when($akunId, fn($q) => $q->where('akun_id', $akunId))
            ->orderBy(
                \App\Models\Jurnal::select('tanggal')
                    ->whereColumn('jurnal.id', 'detail_jurnal.jurnal_id')
                    ->limit(1)
            )
            ->orderBy('id');

        $details = $query->paginate($perPage)->withQueryString();

        // Hitung running balance & summary
        $totalDebit  = $query->clone()->where('tipe', 'DEBIT')->sum('nominal');
        $totalKredit = $query->clone()->where('tipe', 'KREDIT')->sum('nominal');

        // Saldo awal: ambil dari akun yang dipilih atau semua akun
        $saldoAwal = 0;
        if ($akunId) {
            $akun = Akun::find($akunId);
            // Saldo awal dihitung dari jurnal PEMBUKA posted
            $saldoAwal = DetailJurnal::whereHas('jurnal', fn($q) =>
                $q->where('jenis_jurnal', 'PEMBUKA')->where('status', 'POSTED')
            )->where('akun_id', $akunId)->sum('nominal');
        }

        $saldoAkhir = $saldoAwal + $totalDebit - $totalKredit;

        $akuns   = Akun::with('kategoriAkun')->whereNotNull('parent_id')->orderBy('kode_akun')->get();
        $periodes = Periode::orderByDesc('tanggal_awal')->get();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('pages.buku-besar.table', compact(
                    'details', 'saldoAwal', 'totalDebit', 'totalKredit', 'saldoAkhir'
                ))->render(),
            ]);
        }

        return view('pages.buku-besar.index', compact(
            'details', 'akuns', 'periodes',
            'saldoAwal', 'totalDebit', 'totalKredit', 'saldoAkhir',
            'periodeId', 'akunId'
        ));
    }
}
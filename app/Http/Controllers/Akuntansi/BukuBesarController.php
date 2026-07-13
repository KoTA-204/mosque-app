<?php

namespace App\Http\Controllers\Akuntansi;

use App\Http\Controllers\Controller;
use App\Models\Akun;
use App\Models\Periode;
use App\Services\Akuntansi\BukuBesarService;
use Illuminate\Http\Request;

class BukuBesarController extends Controller
{
    public function __construct(private BukuBesarService $service) {}

    public function tampilkanBukuBesar(Request $request)
    {
        $periodeId = $request->input('periode_id', '');
        $akunId    = $request->input('akun_id', '');
        $perPage   = $request->input('per_page', 10);

        $query = $this->service->getMutasiQuery($periodeId, $akunId);

        // Summary dari SEMUA baris (bukan hanya halaman ini).
        $allDetails  = (clone $query)->get();
        $totalDebit  = $allDetails->where('tipe', 'DEBIT')->sum('nominal');
        $totalKredit = $allDetails->where('tipe', 'KREDIT')->sum('nominal');

        $akun       = $akunId ? Akun::find($akunId) : null;
        // Cara 2: PEMBUKA ditampilkan sebagai baris pertama di daftar mutasi,
        // sehingga saldo awal bawaan = 0 (tidak dihitung terpisah) agar tidak dobel.
        $saldoAwal  = 0;
        $mutasiNet  = ($akun && $akun->saldo_normal === 'KREDIT')
            ? $totalKredit - $totalDebit
            : $totalDebit - $totalKredit;
        $saldoAkhir = $saldoAwal + $mutasiNet;

        $badge      = $this->service->tentukanBadge($akun, $saldoAkhir);
        $badgeAkun  = $badge['akun'];
        $badgeWarna = $badge['warna'];

        $details     = $query->paginate($perPage)->withQueryString();
        $akuns       = Akun::with('kategoriAkun')->whereNotNull('parent_id')->orderBy('kode_akun')->get();
        $periodes    = Periode::orderByDesc('tanggal_awal')->get();
        $saldoNormal = $akun ? $akun->saldo_normal : 'DEBIT';

        if ($request->ajax()) {
            return response()->json([
                'html'        => view('pages.akuntansi.buku-besar.table', compact(
                    'details', 'saldoAwal', 'totalDebit', 'totalKredit', 'saldoAkhir', 'saldoNormal'
                ))->render(),
                'totalDebit'  => $totalDebit,
                'totalKredit' => $totalKredit,
                'saldoAwal'   => $saldoAwal,
                'saldoAkhir'  => $saldoAkhir,
                'badgeAkun'   => $badgeAkun,
                'badgeWarna'  => $badgeWarna,
            ]);
        }

        return view('pages.akuntansi.buku-besar.index', compact(
            'details', 'akuns', 'periodes',
            'saldoAwal', 'totalDebit', 'totalKredit', 'saldoAkhir',
            'periodeId', 'akunId', 'badgeAkun', 'badgeWarna', 'saldoNormal'
        ));
    }
}

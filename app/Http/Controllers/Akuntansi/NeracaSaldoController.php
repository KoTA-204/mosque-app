<?php

namespace App\Http\Controllers\Akuntansi;

use App\Http\Controllers\Controller;
use App\Models\KategoriAkun;
use App\Models\Periode;
use App\Services\Akuntansi\NeracaSaldoService;
use Illuminate\Http\Request;

class NeracaSaldoController extends Controller
{
    public function __construct(private NeracaSaldoService $service) {}

    public function tampilkanNeracaSaldo(Request $request)
    {
        $periodeId  = $request->input('periode_id', '');
        $akunFilter = $request->input('akun_filter', '');
        $sortBy     = $request->input('sort_by', 'kode_akun_asc');
        $perPage    = $request->input('per_page', 10);

        $akunQuery = $this->service->getAkunQuery($akunFilter, $sortBy);

        // Semua akun yang cocok filter (untuk grand total).
        $allAkuns   = (clone $akunQuery)->get();
        $allAkunIds = $allAkuns->pluck('id')->toArray();

        $semuaSaldo = $this->service->hitungSaldo($allAkunIds, $periodeId);

        $grandTotalDebit  = 0;
        $grandTotalKredit = 0;
        foreach ($allAkuns as $akun) {
            $rows = $semuaSaldo->get($akun->id, collect());
            $grandTotalDebit  += $rows->where('tipe', 'DEBIT')->sum('total');
            $grandTotalKredit += $rows->where('tipe', 'KREDIT')->sum('total');
        }
        $selisih = $grandTotalDebit - $grandTotalKredit;

        $akuns = $akunQuery->paginate($perPage)->withQueryString();

        $akuns->getCollection()->transform(function ($akun) use ($semuaSaldo) {
            $rows = $semuaSaldo->get($akun->id, collect());
            $akun->total_debit  = $rows->where('tipe', 'DEBIT')->sum('total');
            $akun->total_kredit = $rows->where('tipe', 'KREDIT')->sum('total');
            return $akun;
        });

        $periodes      = Periode::orderByDesc('tanggal_awal')->get();
        $kategoriAkuns = KategoriAkun::orderBy('kode_kategori')->get();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('pages.akuntansi.neraca-saldo.table', compact(
                    'akuns', 'grandTotalDebit', 'grandTotalKredit', 'selisih'
                ))->render(),
            ]);
        }

        return view('pages.akuntansi.neraca-saldo.index', compact(
            'akuns', 'periodes', 'kategoriAkuns',
            'grandTotalDebit', 'grandTotalKredit', 'selisih',
            'periodeId', 'akunFilter', 'sortBy'
        ));
    }
}

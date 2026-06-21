<?php

namespace App\Http\Controllers;

use App\Models\Akun;
use App\Models\DetailJurnal;
use App\Models\Jurnal;
use App\Models\Periode;
use Illuminate\Http\Request;

class BukuBesarController extends Controller
{
    public function index(Request $request)
    {
        $periodeId = $request->input('periode_id', '');
        $akunId    = $request->input('akun_id', '');
        $perPage   = $request->input('per_page', 10);

        // Mutasi periode: HANYA jurnal POSTED non-PEMBUKA.
        // PEMBUKA dikecualikan karena merupakan saldo awal (sudah dihitung di $saldoAwal),
        // bukan mutasi periode berjalan → mencegah double-count pada saldoAkhir.
        $baseJurnalIds = Jurnal::where('status', 'POSTED')
            ->where('jenis_jurnal', '!=', 'PEMBUKA')
            ->when($periodeId, fn($q) => $q->where('periode_id', $periodeId))
            ->pluck('id');

        $query = DetailJurnal::with(['jurnal', 'akun', 'jurnal.transaksi.dompet'])
            ->whereIn('jurnal_id', $baseJurnalIds)
            ->when($akunId, fn($q) => $q->where('akun_id', $akunId))
            ->orderBy(
                Jurnal::select('tanggal')
                    ->whereColumn('jurnal.id', 'detail_jurnal.jurnal_id')
                    ->limit(1)
            )
            ->orderBy('id');

        // Hitung summary dari SEMUA baris (bukan hanya halaman ini)
        $allDetails = (clone $query)->get();
        $totalDebit  = $allDetails->where('tipe', 'DEBIT')->sum('nominal');
        $totalKredit = $allDetails->where('tipe', 'KREDIT')->sum('nominal');

        // Akun yang sedang dilihat (dipakai untuk saldo awal & badge)
        $akun = $akunId ? Akun::find($akunId) : null;

        // Saldo awal dari jurnal PEMBUKA, menghormati saldo normal akun:
        // akun saldo normal DEBIT  → (debit - kredit)
        // akun saldo normal KREDIT → (kredit - debit)
        $saldoAwal = 0;
        if ($akun) {
            $pembuka = DetailJurnal::whereHas('jurnal', fn($q) =>
                $q->where('jenis_jurnal', 'PEMBUKA')->where('status', 'POSTED')
            )->where('akun_id', $akunId)->get();

            $debitPembuka  = $pembuka->where('tipe', 'DEBIT')->sum('nominal');
            $kreditPembuka = $pembuka->where('tipe', 'KREDIT')->sum('nominal');

            $saldoAwal = $akun->saldo_normal === 'DEBIT'
                ? $debitPembuka - $kreditPembuka
                : $kreditPembuka - $debitPembuka;
        }

        $saldoAkhir = $saldoAwal + $totalDebit - $totalKredit;

        // Tentukan badge akun sesuai saldo normal & posisi saldo akhir
        $badgeAkun  = null;
        $badgeWarna = null;
        if ($akun) {
            $netSaldo = $saldoAwal + $totalDebit - $totalKredit;

            if ($akun->saldo_normal === 'DEBIT') {
                $badgeAkun  = $netSaldo >= 0 ? 'DEBIT' : 'KREDIT';
                $badgeWarna = $netSaldo >= 0 ? 'blue' : 'red';
            } else {
                $badgeAkun  = $netSaldo <= 0 ? 'KREDIT' : 'DEBIT';
                $badgeWarna = $netSaldo <= 0 ? 'green' : 'red';
            }
        }

        $details  = $query->paginate($perPage)->withQueryString();
        $akuns    = Akun::with('kategoriAkun')->whereNotNull('parent_id')->orderBy('kode_akun')->get();
        $periodes = Periode::orderByDesc('tanggal_awal')->get();

        $saldoNormal = $akun ? $akun->saldo_normal : 'DEBIT';

        if ($request->ajax()) {
            return response()->json([
                'html'        => view('pages.buku-besar.table', compact(
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

        return view('pages.buku-besar.index', compact(
            'details', 'akuns', 'periodes',
            'saldoAwal', 'totalDebit', 'totalKredit', 'saldoAkhir',
            'periodeId', 'akunId', 'badgeAkun', 'badgeWarna', 'saldoNormal'
        ));
    }
}
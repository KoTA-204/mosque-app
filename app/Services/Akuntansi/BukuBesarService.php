<?php

namespace App\Services\Akuntansi;

use App\Models\Akun;
use App\Models\DetailJurnal;
use App\Models\Jurnal;
use Illuminate\Database\Eloquent\Builder;

/**
 * Service buku besar (Pure Fabrication).
 *
 * Mengeluarkan logika query & perhitungan saldo dari BukuBesarController
 * sehingga controller cukup mengorkestrasi request/response.
 */
class BukuBesarService
{
    /**
     * Query mutasi periode: HANYA jurnal POSTED non-PEMBUKA.
     * PEMBUKA dikecualikan karena merupakan saldo awal (dihitung di hitungSaldoAwal),
     * bukan mutasi periode berjalan — mencegah double-count pada saldo akhir.
     */
    public function getMutasiQuery(?string $periodeId, ?string $akunId): Builder
    {
        $baseJurnalIds = Jurnal::where('status', 'POSTED')
            ->when($periodeId, fn($q) => $q->where('periode_id', $periodeId))
            ->pluck('id');

        return DetailJurnal::with(['jurnal', 'akun', 'jurnal.transaksi.dompet'])
            ->whereIn('jurnal_id', $baseJurnalIds)
            ->when($akunId, fn($q) => $q->where('akun_id', $akunId))
            ->orderBy(
                Jurnal::select('tanggal')
                    ->whereColumn('jurnal.id', 'detail_jurnal.jurnal_id')
                    ->limit(1)
            )
            ->orderBy('id');
    }

    /**
     * Saldo awal dari jurnal PEMBUKA, menghormati saldo normal akun:
     * - akun saldo normal DEBIT  → (debit - kredit)
     * - akun saldo normal KREDIT → (kredit - debit)
     */
    public function hitungSaldoAwal(?Akun $akun): float
    {
        if (!$akun) return 0;

        $pembuka = DetailJurnal::whereHas('jurnal', fn($q) =>
            $q->where('jenis_jurnal', 'PEMBUKA')->where('status', 'POSTED')
        )->where('akun_id', $akun->id)->get();

        $debitPembuka  = $pembuka->where('tipe', 'DEBIT')->sum('nominal');
        $kreditPembuka = $pembuka->where('tipe', 'KREDIT')->sum('nominal');

        return $akun->saldo_normal === 'DEBIT'
            ? $debitPembuka - $kreditPembuka
            : $kreditPembuka - $debitPembuka;
    }

    /**
     * Tentukan badge akun sesuai saldo normal & posisi saldo akhir.
     *
     * @return array{akun: ?string, warna: ?string}
     */
    public function tentukanBadge(?Akun $akun, float $netSaldo): array
    {
        if (!$akun) {
            return ['akun' => null, 'warna' => null];
        }

        if ($akun->saldo_normal === 'DEBIT') {
            return [
                'akun'  => $netSaldo >= 0 ? 'DEBIT' : 'KREDIT',
                'warna' => $netSaldo >= 0 ? 'blue' : 'red',
            ];
        }

        return [
            'akun'  => $netSaldo <= 0 ? 'KREDIT' : 'DEBIT',
            'warna' => $netSaldo <= 0 ? 'green' : 'red',
        ];
    }
}

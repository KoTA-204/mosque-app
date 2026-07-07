<?php

namespace App\Services\LaporanKeuangan;

use App\Models\Akun;
use App\Models\DetailJurnal;
use Illuminate\Support\Collection;

/**
 * Kalkulator saldo akun.
 *
 * Kelas ini memusatkan seluruh primitif perhitungan saldo yang dipakai bersama
 * oleh semua laporan keuangan. (Sebelumnya bernama LaporanKeuanganService,
 * nama tersebut menyesatkan karena kelas ini bukan sebuah laporan.)
 */
class KalkulatorSaldoAkun
{
    /** Saldo satu kelompok akun (ber-prefix kode) pada 1 periode (flow). */
    public function hitungSaldoKelompok(string $kodeAwal, int $periodeId, string $saldoNormal = 'KREDIT'): float
    {
        $ids = Akun::where('kode_akun', 'like', $kodeAwal . '%')->pluck('id');
        if ($ids->isEmpty()) return 0;

        return $this->terapkanSaldoNormal(
            DetailJurnal::whereIn('akun_id', $ids)
                ->whereHas('jurnal', fn($q) => $q
                    ->where('periode_id', $periodeId)
                    ->where('status', 'POSTED')
                    ->whereNull('tipe_penutupan')
                ),
            $saldoNormal
        );
    }

    /** Saldo kumulatif satu kelompok akun s.d. periode ini. */
    public function hitungSaldoKumulatifKelompok(string $kodeAwal, array $periodeIds, string $saldoNormal = 'DEBIT'): float
    {
        $ids = Akun::where('kode_akun', 'like', $kodeAwal . '%')->pluck('id');
        if ($ids->isEmpty()) return 0;

        return $this->terapkanSaldoNormal(
            DetailJurnal::whereIn('akun_id', $ids)
                ->whereHas('jurnal', fn($q) => $q
                    ->whereIn('periode_id', $periodeIds)
                    ->where('status', 'POSTED')
                ),
            $saldoNormal
        );
    }

    /**
     * Saldo AWAL satu kelompok akun: hanya dari jurnal PEMBUKA.
     * (Nama lama saldoPosisiByPrefix menyembunyikan fakta bahwa ini saldo awal.)
     */
    public function hitungSaldoAwalKelompok(string $kodeAwal, int $periodeId, string $saldoNormal = 'DEBIT'): float
    {
        $ids = Akun::where('kode_akun', 'like', $kodeAwal . '%')->pluck('id');
        if ($ids->isEmpty()) return 0;

        return $this->terapkanSaldoNormal(
            DetailJurnal::whereIn('akun_id', $ids)
                ->whereHas('jurnal', fn($q) => $q
                    ->where('periode_id', $periodeId)
                    ->where('status', 'POSTED')
                    ->where('jenis_jurnal', 'PEMBUKA')
                ),
            $saldoNormal
        );
    }

    /** Saldo kumulatif satu akun (kode tepat) s.d. periode ini. */
    public function hitungSaldoKumulatifAkun(string $kodeAkun, array $periodeIds, string $saldoNormal = 'DEBIT'): float
    {
        $akun = Akun::where('kode_akun', $kodeAkun)->first();
        if (!$akun) return 0;

        return $this->terapkanSaldoNormal(
            DetailJurnal::where('akun_id', $akun->id)
                ->whereHas('jurnal', fn($q) => $q
                    ->whereIn('periode_id', $periodeIds)
                    ->where('status', 'POSTED')
                ),
            $saldoNormal
        );
    }

    /** Eksekusi query & terapkan aturan saldo normal (DEBIT: d-k, KREDIT: k-d). */
    public function terapkanSaldoNormal($query, string $saldoNormal): float
    {
        $row = $query->selectRaw("
            SUM(CASE WHEN tipe = 'DEBIT'  THEN nominal ELSE 0 END) AS total_debit,
            SUM(CASE WHEN tipe = 'KREDIT' THEN nominal ELSE 0 END) AS total_kredit
        ")->first();

        $d = (float)($row->total_debit  ?? 0);
        $k = (float)($row->total_kredit ?? 0);

        return $saldoNormal === 'DEBIT' ? ($d - $k) : ($k - $d);
    }

    /** Rincian saldo per akun leaf dalam 1 periode (flow). */
    public function getRincianSaldoKelompok(string $kodeAwal, int $periodeId, string $saldoNormal): Collection
    {
        return Akun::where('kode_akun', 'like', $kodeAwal . '%')
            ->whereNotNull('parent_id')
            ->orderBy('kode_akun')
            ->get()
            ->map(function (Akun $akun) use ($periodeId, $saldoNormal) {
                $saldo = $this->terapkanSaldoNormal(
                    DetailJurnal::where('akun_id', $akun->id)
                        ->whereHas('jurnal', fn($q) => $q
                            ->where('periode_id', $periodeId)
                            ->where('status', 'POSTED')
                            ->whereNull('tipe_penutupan')
                        ),
                    $saldoNormal
                );
                return (object)[
                    'kode_akun' => $akun->kode_akun,
                    'nama_akun' => $akun->nama_akun,
                    'saldo'     => $saldo,
                ];
            })->values();
    }

    /** Rincian saldo kumulatif per akun leaf (posisi). */
    public function getRincianSaldoKumulatifKelompok(string $kodeAwal, array $periodeIds, string $saldoNormal): Collection
    {
        return Akun::where('kode_akun', 'like', $kodeAwal . '%')
            ->whereNotNull('parent_id')
            ->orderBy('kode_akun')
            ->get()
            ->map(function (Akun $akun) use ($periodeIds, $saldoNormal) {
                $saldo = $this->terapkanSaldoNormal(
                    DetailJurnal::where('akun_id', $akun->id)
                        ->whereHas('jurnal', fn($q) => $q
                            ->whereIn('periode_id', $periodeIds)
                            ->where('status', 'POSTED')
                        ),
                    $saldoNormal
                );
                return (object)[
                    'kode_akun' => $akun->kode_akun,
                    'nama_akun' => $akun->nama_akun,
                    'saldo'     => $saldo,
                ];
            })->values();
    }
}

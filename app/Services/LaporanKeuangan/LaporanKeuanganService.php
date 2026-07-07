<?php

namespace App\Services\LaporanKeuangan;

use App\Models\Akun;
use App\Models\DetailJurnal;
use App\Models\Periode;
use Illuminate\Support\Collection;

class LaporanKeuanganService
{
    // untuk saldo akun ber-prefix pada 1 periode (flow)
    public function saldoByPrefix(string $prefix, int $periodeId, string $saldoNormal = 'KREDIT'): float
    {
        $ids = Akun::where('kode_akun', 'like', $prefix . '%')->pluck('id');
        if ($ids->isEmpty()) return 0;
        return $this->hitungSaldo(
            DetailJurnal::whereIn('akun_id', $ids)
                ->whereHas('jurnal', fn($q) => $q
                    ->where('periode_id', $periodeId)
                    ->where('status', 'POSTED')
                    ->whereNull('tipe_penutupan')
                ),
            $saldoNormal
        );
    }

    // untuk saldo kumulatif akun ber-prefix s.d. periode ini
    public function saldoKumulatifByPrefix(string $prefix, array $periodeIds, string $saldoNormal = 'DEBIT'): float
    {
        $ids = Akun::where('kode_akun', 'like', $prefix . '%')->pluck('id');
        if ($ids->isEmpty()) return 0;
        return $this->hitungSaldo(
            DetailJurnal::whereIn('akun_id', $ids)
                ->whereHas('jurnal', fn($q) => $q
                    ->whereIn('periode_id', $periodeIds)
                    ->where('status', 'POSTED')
                ),
            $saldoNormal
        );
    }

    // untuk saldo dari jurnal PEMBUKA saja
    public function saldoPosisiByPrefix(string $prefix, int $periodeId, string $saldoNormal = 'DEBIT'): float
    {
        $ids = Akun::where('kode_akun', 'like', $prefix . '%')->pluck('id');
        if ($ids->isEmpty()) return 0;
        return $this->hitungSaldo(
            DetailJurnal::whereIn('akun_id', $ids)
                ->whereHas('jurnal', fn($q) => $q
                    ->where('periode_id', $periodeId)
                    ->where('status', 'POSTED')
                    ->where('jenis_jurnal', 'PEMBUKA')
                ),
            $saldoNormal
        );
    }

    // untuk saldo kumulatif 1 kode_akun tepat
    public function saldoKumulatifByKode(string $kode, array $periodeIds, string $saldoNormal = 'DEBIT'): float
    {
        $akun = Akun::where('kode_akun', $kode)->first();
        if (!$akun) return 0;
        return $this->hitungSaldo(
            DetailJurnal::where('akun_id', $akun->id)
                ->whereHas('jurnal', fn($q) => $q
                    ->whereIn('periode_id', $periodeIds)
                    ->where('status', 'POSTED')
                ),
            $saldoNormal
        );
    }

    // untuk eksekusi query dan hitung saldo berdasarkan saldo normal
    public function hitungSaldo($query, string $saldoNormal): float
    {
        $row = $query->selectRaw("
            SUM(CASE WHEN tipe = 'DEBIT'  THEN nominal ELSE 0 END) AS total_debit,
            SUM(CASE WHEN tipe = 'KREDIT' THEN nominal ELSE 0 END) AS total_kredit
        ")->first();

        $d = (float)($row->total_debit  ?? 0);
        $k = (float)($row->total_kredit ?? 0);

        return $saldoNormal === 'DEBIT' ? ($d - $k) : ($k - $d);
    }

    // untuk rincian saldo per akun leaf 1 periode (flow)
    public function getRincianAkun(string $prefix, int $periodeId, string $saldoNormal): Collection
    {
        $akuns = Akun::where('kode_akun', 'like', $prefix . '%')
            ->whereNotNull('parent_id')
            ->orderBy('kode_akun')
            ->get();

        return $akuns->map(function (Akun $akun) use ($periodeId, $saldoNormal) {
            $saldo = $this->hitungSaldo(
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

    // untuk rincian saldo kumulatif per akun leaf (posisi)
    public function getRincianAkunKumulatif(string $prefix, array $periodeIds, string $saldoNormal): Collection
    {
        $akuns = Akun::where('kode_akun', 'like', $prefix . '%')
            ->whereNotNull('parent_id')
            ->orderBy('kode_akun')
            ->get();

        return $akuns->map(function (Akun $akun) use ($periodeIds, $saldoNormal) {
            $saldo = $this->hitungSaldo(
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

    // untuk ambil semua periode_id dengan tanggal_akhir <= periode ini
    public function getPeriodeIdsUpTo(int $periodeId): array
    {
        $periode = Periode::find($periodeId);
        if (!$periode) return [$periodeId];
        return Periode::where('tanggal_akhir', '<=', $periode->tanggal_akhir)
            ->pluck('id')
            ->toArray();
    }
}
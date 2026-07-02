<?php

namespace App\Services\LaporanKeuangan;

use App\Models\Akun;
use App\Models\DetailJurnal;
use App\Models\Periode;

class ArusKasService
{
    public function __construct(private LaporanKeuanganService $saldo) {}

    public function build(?Periode $periode): array
    {
        $empty = [
            'penerimaanOperasional' => collect(),
            'pengeluaranOperasional' => collect(),
            'kasNetoOperasional'    => 0,
            'pengeluaranInvestasi'  => collect(),
            'penerimaanInvestasi'   => collect(),
            'kasNetoInvestasi'      => 0,
            'penerimaanPendanaan'   => collect(),
            'penyaluranPendanaan'   => collect(),
            'kasNetoPendanaan'      => 0,
            'kenaikanNeto'          => 0,
            'kasAwal'               => 0,
            'kasAkhir'              => 0,
        ];
        if (!$periode) return $empty;

        $pid  = $periode->id;
        $pids = $this->saldo->getPeriodeIdsUpTo($pid);

        // periode sebelumnya untuk delta aset tetap
        $periodePrevObj = Periode::where('tipe', $periode->tipe)
            ->where('tanggal_akhir', '<', $periode->tanggal_awal)
            ->orderByDesc('tanggal_akhir')
            ->first();

        // operasional: penerimaan = 4-1
        $penerimaanOperasional = $this->saldo->getRincianAkun('4-1', $pid, 'KREDIT')
            ->filter(fn($r) => $r->saldo != 0)->values();
        $totalPenerimaanOp = $penerimaanOperasional->sum('saldo');

        // operasional: pengeluaran = 5- KECUALI penyusutan/depresiasi
        $semuaBeban = collect();
        $headerBeban = Akun::where('kode_akun', 'like', '5-%')
            ->whereNull('parent_id')
            ->orderBy('kode_akun')
            ->get();
        foreach ($headerBeban as $header) {
            $kode    = rtrim($header->kode_akun, '0');
            $rincian = $this->saldo->getRincianAkun($kode, $pid, 'DEBIT')
                ->filter(fn($r) => $r->saldo != 0)
                ->filter(fn($r) => !preg_match('/penyusutan|depresiasi/i', $r->nama_akun))
                ->values();
            foreach ($rincian as $r) {
                $semuaBeban->push($r);
            }
        }

        // pisah operasional vs dana terikat (zakat/santunan/wakaf -> pendanaan)
        $pengeluaranOperasional = $semuaBeban
            ->filter(fn($r) => !preg_match('/zakat|santunan|wakaf/i', $r->nama_akun))
            ->values();
        $penyaluranPendanaan = $semuaBeban
            ->filter(fn($r) => preg_match('/zakat|santunan|wakaf/i', $r->nama_akun))
            ->values();

        $totalPengeluaranOp  = $pengeluaranOperasional->sum('saldo');
        $kasNetoOperasional  = $totalPenerimaanOp - $totalPengeluaranOp;

        // investasi: delta aset tetap (1-2 DEBIT) vs periode lalu
        $asetTetapSekarang = Akun::where('kode_akun', 'like', '1-2%')
            ->whereNotNull('parent_id')
            ->where('saldo_normal', 'DEBIT')
            ->get()
            ->map(fn($akun) => $this->saldo->hitungSaldo(
                DetailJurnal::where('akun_id', $akun->id)
                    ->whereHas('jurnal', fn($q) => $q
                        ->whereIn('periode_id', $pids)
                        ->where('status', 'POSTED')
                    ),
                'DEBIT'
            ))->sum();

        $asetTetapSebelumnya = 0;
        if ($periodePrevObj) {
            $prevPids = $this->saldo->getPeriodeIdsUpTo($periodePrevObj->id);
            $asetTetapSebelumnya = Akun::where('kode_akun', 'like', '1-2%')
                ->whereNotNull('parent_id')
                ->where('saldo_normal', 'DEBIT')
                ->get()
                ->map(fn($akun) => $this->saldo->hitungSaldo(
                    DetailJurnal::where('akun_id', $akun->id)
                        ->whereHas('jurnal', fn($q) => $q
                            ->whereIn('periode_id', $prevPids)
                            ->where('status', 'POSTED')
                        ),
                    'DEBIT'
                ))->sum();
        }

        $deltaAsetTetap = $asetTetapSekarang - $asetTetapSebelumnya;

        $pengeluaranInvestasi = collect();
        if ($deltaAsetTetap > 0) {
            $pengeluaranInvestasi->push((object)[
                'nama_akun' => 'Pembelian peralatan & inventaris',
                'saldo'     => $deltaAsetTetap,
            ]);
        }

        // penerimaan investasi = 4-1 yg mengandung "penjualan aset"/"divestasi"
        $penerimaanInvestasi = $this->saldo->getRincianAkun('4-1', $pid, 'KREDIT')
            ->filter(fn($r) => preg_match('/penjualan aset|divestasi/i', $r->nama_akun))
            ->values();

        $kasNetoInvestasi = $penerimaanInvestasi->sum('saldo') - $pengeluaranInvestasi->sum('saldo');

        // pendanaan: penerimaan = 4-2 (terikat)
        $penerimaanPendanaan = $this->saldo->getRincianAkun('4-2', $pid, 'KREDIT')
            ->filter(fn($r) => $r->saldo != 0)->values();
        $kasNetoPendanaan = $penerimaanPendanaan->sum('saldo') - $penyaluranPendanaan->sum('saldo');

        // rekonsiliasi
        $kenaikanNeto = $kasNetoOperasional + $kasNetoInvestasi + $kasNetoPendanaan;

        // kas awal: 1-1 (bukan piutang) s.d. periode lalu
        $kasAwal = 0;
        if ($periodePrevObj) {
            $prevPids2 = $this->saldo->getPeriodeIdsUpTo($periodePrevObj->id);
            $kasAwal = $this->saldo->getRincianAkunKumulatif('1-1', $prevPids2, 'DEBIT')
                ->filter(fn($r) => !preg_match('/piutang/i', $r->nama_akun))
                ->sum('saldo');
        }

        $kasAkhir = $kasAwal + $kenaikanNeto;

        return compact(
            'penerimaanOperasional', 'pengeluaranOperasional', 'kasNetoOperasional',
            'penerimaanInvestasi', 'pengeluaranInvestasi', 'kasNetoInvestasi',
            'penerimaanPendanaan', 'penyaluranPendanaan', 'kasNetoPendanaan',
            'kenaikanNeto', 'kasAwal', 'kasAkhir'
        );
    }
}
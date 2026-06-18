<?php

namespace App\Services\Laporan;

use App\Models\Akun;
use App\Models\DetailJurnal;
use App\Models\Periode;

class CalkService
{
    public function __construct(
        private LaporanKeuanganService $saldo,
        private PerubahanAsetNetoService $perubahan,
        private PenghasilanKomprehensifService $penghasilan,
    ) {}

    public function build(?Periode $periode): array
    {
        if (!$periode) {
            return [
                'kasSetaraKas'      => collect(),
                'totalKas'          => 0,
                'piutang'           => collect(),
                'totalPiutang'      => 0,
                'asetTetap'         => collect(),
                'totalHargaPerolehan'   => 0,
                'totalAkumulasi'        => 0,
                'totalNilaiBuku'        => 0,
                'liabilitas'        => collect(),
                'totalLiabilitas'   => 0,
                'pendapatanTanpa'   => collect(),
                'totalPendapatanTanpa' => 0,
                'beban'             => collect(),
                'totalBeban'        => 0,
                'asetNeto'          => [],
                'arusKas'           => [],
            ];
        }

        $pids = $this->saldo->getPeriodeIdsUpTo($periode->id);
        $pid  = $periode->id;

        // 1. kas & setara kas (1-1 DEBIT kumulatif)
        $kasSetaraKas = $this->saldo->getRincianAkunKumulatif('1-1', $pids, 'DEBIT')
            ->filter(fn($r) => $r->saldo != 0)->values();
        $totalKas = $kasSetaraKas->sum('saldo');

        // 2. piutang (subset 1-1 yg mengandung "piutang")
        $piutang = $kasSetaraKas->filter(fn($r) => stripos($r->nama_akun, 'piutang') !== false)->values();
        $kasSetaraKas = $kasSetaraKas->filter(fn($r) => stripos($r->nama_akun, 'piutang') === false)->values();
        $totalKas = $kasSetaraKas->sum('saldo');
        $totalPiutang = $piutang->sum('saldo');

        // 3. aset tetap (1-2)
        $akunAsetTetap = Akun::where('kode_akun', 'like', '1-2%')
            ->whereNotNull('parent_id')
            ->orderBy('kode_akun')
            ->get();

        $asetTetap = $akunAsetTetap->map(function (Akun $akun) use ($pids) {
            $saldo = $this->saldo->hitungSaldo(
                DetailJurnal::where('akun_id', $akun->id)
                    ->whereHas('jurnal', fn($q) => $q
                        ->whereIn('periode_id', $pids)
                        ->where('status', 'POSTED')
                    ),
                $akun->saldo_normal
            );
            $isAkumulasi = $akun->saldo_normal === 'KREDIT';
            return (object)[
                'nama_akun'    => $akun->nama_akun,
                'is_akumulasi' => $isAkumulasi,
                'harga_perolehan' => $isAkumulasi ? 0 : $saldo,
                'akumulasi'       => $isAkumulasi ? $saldo : 0,
                'nilai_buku'      => $isAkumulasi ? -$saldo : $saldo,
            ];
        })->values();

        $totalHargaPerolehan = $asetTetap->sum('harga_perolehan');
        $totalAkumulasi      = $asetTetap->sum('akumulasi');
        $totalNilaiBuku      = $asetTetap->sum('nilai_buku');

        // 4. liabilitas (2-)
        $liabilitas = collect();
        $headerLiab = Akun::where('kode_akun', 'like', '2-%')
            ->whereNull('parent_id')
            ->orderBy('kode_akun')
            ->get();
        foreach ($headerLiab as $header) {
            $kode    = rtrim($header->kode_akun, '0');
            $rincian = $this->saldo->getRincianAkunKumulatif($kode, $pids, 'KREDIT')
                ->filter(fn($r) => $r->saldo != 0)->values();
            foreach ($rincian as $r) {
                $liabilitas->push($r);
            }
        }
        $totalLiabilitas = $liabilitas->sum('saldo');

        // 5. pendapatan tanpa pembatasan (4-1 periode ini)
        $pendapatanTanpa      = $this->saldo->getRincianAkun('4-1', $pid, 'KREDIT')
            ->filter(fn($r) => $r->saldo != 0)->values();
        $totalPendapatanTanpa = $pendapatanTanpa->sum('saldo');

        // 6. beban (5-)
        $beban = collect();
        $headerBeban = Akun::where('kode_akun', 'like', '5-%')
            ->whereNull('parent_id')
            ->orderBy('kode_akun')
            ->get();
        foreach ($headerBeban as $header) {
            $kode    = rtrim($header->kode_akun, '0');
            $rincian = $this->saldo->getRincianAkun($kode, $pid, 'DEBIT')
                ->filter(fn($r) => $r->saldo != 0)->values();
            foreach ($rincian as $r) {
                $beban->push($r);
            }
        }
        $totalBeban = $beban->sum('saldo');

        // 7. aset neto (dari perubahan aset neto)
        $periodeObj  = $periode;
        $periodePrevObj = Periode::where('tipe', $periode->tipe)
            ->where('tanggal_akhir', '<', $periode->tanggal_awal)
            ->orderByDesc('tanggal_akhir')
            ->first();

        $pan = $this->perubahan->build($periodeObj, $periodePrevObj);
        $asetNeto = [
            'saldoAwalTanpa'   => $pan['saldoAwalTanpa'],
            'saldoAwalDengan'  => $pan['saldoAwalDengan'],
            'totalSaldoAwal'   => $pan['totalSaldoAwal'],
            'surplusTanpa'     => $pan['surplusTanpa'],
            'surplusDengan'    => $pan['surplusDengan'],
            'saldoAkhirTanpa'  => $pan['saldoAkhirTanpa'],
            'saldoAkhirDengan' => $pan['saldoAkhirDengan'],
            'totalSaldoAkhir'  => $pan['totalSaldoAkhir'],
        ];

        // 8. arus kas (estimasi dari perubahan kas)
        $peng = $this->penghasilan->build($pid);
        $surplusDefisit = $peng['surplusDefisit'];

        // penyusutan = beban mengandung 'penyusutan'
        $penyusutan = $beban->filter(fn($r) => stripos($r->nama_akun, 'penyusutan') !== false)->sum('saldo');

        // perubahan kas & setara kas
        $kasAkhir = $totalKas;
        $kasAwal  = 0;
        if ($periodePrevObj) {
            $prevPids = $this->saldo->getPeriodeIdsUpTo($periodePrevObj->id);
            $prevKasRincian = $this->saldo->getRincianAkunKumulatif('1-1', $prevPids, 'DEBIT')
                ->filter(fn($r) => stripos($r->nama_akun, 'piutang') === false);
            $kasAwal = $prevKasRincian->sum('saldo');
        }
        $kasNeto = $kasAkhir - $kasAwal;

        // investasi = perubahan aset tetap (harga perolehan)
        $prevHargaPerolehan = 0;
        if ($periodePrevObj) {
            $prevPids2   = $this->saldo->getPeriodeIdsUpTo($periodePrevObj->id);
            $prevAsetTetap = Akun::where('kode_akun', 'like', '1-2%')
                ->whereNotNull('parent_id')
                ->where('saldo_normal', 'DEBIT')
                ->orderBy('kode_akun')
                ->get()
                ->map(function (Akun $akun) use ($prevPids2) {
                    return $this->saldo->hitungSaldo(
                        DetailJurnal::where('akun_id', $akun->id)
                            ->whereHas('jurnal', fn($q) => $q
                                ->whereIn('periode_id', $prevPids2)
                                ->where('status', 'POSTED')
                            ),
                        'DEBIT'
                    );
                })->sum();
            $prevHargaPerolehan = $prevAsetTetap;
        }

        $kasInvestasi   = -($totalHargaPerolehan - $prevHargaPerolehan);
        $kasOperasional = $kasNeto - $kasInvestasi;

        $arusKas = [
            'operasional' => $kasOperasional,
            'investasi'   => $kasInvestasi,
            'pendanaan'   => 0,
            'kenaikan'    => $kasNeto,
            'kasAwal'     => $kasAwal,
            'kasAkhir'    => $kasAkhir,
        ];

        return compact(
            'kasSetaraKas', 'totalKas',
            'piutang', 'totalPiutang',
            'asetTetap', 'totalHargaPerolehan', 'totalAkumulasi', 'totalNilaiBuku',
            'liabilitas', 'totalLiabilitas',
            'pendapatanTanpa', 'totalPendapatanTanpa',
            'beban', 'totalBeban',
            'asetNeto', 'arusKas'
        );
    }
}
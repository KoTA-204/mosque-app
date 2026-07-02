<?php

namespace App\Services\LaporanKeuangan;

use App\Models\Akun;
use App\Models\DetailJurnal;

class PosisiKeuanganService
{
    public function __construct(private LaporanKeuanganService $saldo) {}

    public function build(?int $periodeId): array
    {
        if (!$periodeId) {
            return [
                'grupAsetLancar'           => collect(),
                'jumlahAsetLancar'         => 0,
                'grupAsetTetap'            => collect(),
                'jumlahAsetTetap'          => 0,
                'jumlahAset'               => 0,
                'grupLiabilitas'           => collect(),
                'jumlahLiabilitas'         => 0,
                'asetNetoTanpaPembatasan'  => 0,
                'rincianAsetNetoTanpa'     => collect(),
                'asetNetoDenganPembatasan' => 0,
                'rincianAsetNetoDengan'    => collect(),
                'jumlahAsetNeto'           => 0,
            ];
        }

        $pids = $this->saldo->getPeriodeIdsUpTo($periodeId);

        // aset lancar (1-1)
        $rincianAsetLancar = $this->saldo->getRincianAkunKumulatif('1-1', $pids, 'DEBIT');
        $jumlahAsetLancar  = $rincianAsetLancar->sum('saldo');

        // aset tetap (1-2) — tangani akumulasi penyusutan
        $akunAsetTetap = Akun::where('kode_akun', 'like', '1-2%')
            ->whereNotNull('parent_id')
            ->orderBy('kode_akun')
            ->get();

        $rincianAsetTetap = $akunAsetTetap->map(function (Akun $akun) use ($pids) {
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
                'kode_akun'    => $akun->kode_akun,
                'nama_akun'    => $akun->nama_akun,
                'saldo'        => $isAkumulasi ? -$saldo : $saldo,
                'is_akumulasi' => $isAkumulasi,
            ];
        })->values();

        $jumlahAsetTetap = $rincianAsetTetap->sum('saldo');
        $jumlahAset      = $jumlahAsetLancar + $jumlahAsetTetap;

        // liabilitas (2-) per grup header
        $headerLiab = Akun::where('kode_akun', 'like', '2-%')
            ->whereNull('parent_id')
            ->orderBy('kode_akun')
            ->get();

        $grupLiabilitas = collect();
        $jumlahLiabilitas = 0;
        foreach ($headerLiab as $header) {
            $kode = rtrim($header->kode_akun, '0');
            $rincian = $this->saldo->getRincianAkunKumulatif($kode, $pids, 'KREDIT');
            $total   = $rincian->sum('saldo');
            if ($total == 0) continue;
            $grupLiabilitas->push((object)[
                'kode_akun' => $header->kode_akun,
                'nama_akun' => $header->nama_akun,
                'rincian'   => $rincian,
                'total'     => $total,
            ]);
            $jumlahLiabilitas += $total;
        }

        // aset neto (3-1 tanpa, 3-2 dengan)
        $asetNetoTanpaPembatasan  = $this->saldo->saldoKumulatifByPrefix('3-1', $pids, 'KREDIT');
        $rincianAsetNetoTanpa     = $this->saldo->getRincianAkunKumulatif('3-1', $pids, 'KREDIT');
        $asetNetoDenganPembatasan = $this->saldo->saldoKumulatifByPrefix('3-2', $pids, 'KREDIT');
        $rincianAsetNetoDengan    = $this->saldo->getRincianAkunKumulatif('3-2', $pids, 'KREDIT');

        $jumlahAsetNeto = $asetNetoTanpaPembatasan + $asetNetoDenganPembatasan;

        return compact(
            'rincianAsetLancar', 'jumlahAsetLancar',
            'rincianAsetTetap',  'jumlahAsetTetap', 'jumlahAset',
            'grupLiabilitas',    'jumlahLiabilitas',
            'asetNetoTanpaPembatasan', 'asetNetoDenganPembatasan',
            'jumlahAsetNeto'
        );
    }
}
<?php

namespace App\Services\LaporanKeuangan;

use App\Models\Periode;

class PerubahanAsetNetoService
{
    public function __construct(
        private LaporanKeuanganService $saldo,
        private PosisiKeuanganService $posisi,
        private PenghasilanKomprehensifService $penghasilan,
    ) {}

    public function build(?Periode $periode, ?Periode $periodePrev): array
    {
        $empty = [
            'saldoAwalTanpa'   => 0, 'saldoAwalDengan'   => 0, 'totalSaldoAwal'   => 0,
            'surplusTanpa'     => 0, 'surplusDengan'     => 0,
            'rincianTanpa'     => collect(), 'rincianDengan' => collect(),
            'dibebaskan'       => 0,
            'pkl'              => 0,
            'saldoAkhirTanpa'  => 0, 'saldoAkhirDengan'  => 0, 'totalSaldoAkhir'  => 0,
        ];
        if (!$periode) return $empty;

        // saldo awal = posisi keuangan akhir periode sebelumnya
        $saldoAwalTanpa  = 0;
        $saldoAwalDengan = 0;
        if ($periodePrev) {
            $prevPos         = $this->posisi->build($periodePrev->id);
            $saldoAwalTanpa  = $prevPos['asetNetoTanpaPembatasan'];
            $saldoAwalDengan = $prevPos['asetNetoDenganPembatasan'];
        } else {
            // periode pertama -> dari jurnal pembuka periode ini
            $saldoAwalTanpa  = $this->saldo->saldoPosisiByPrefix('3-1', $periode->id, 'KREDIT');
            $saldoAwalDengan = $this->saldo->saldoPosisiByPrefix('3-2', $periode->id, 'KREDIT');
        }

        // surplus dari penghasilan komprehensif
        $peng = $this->penghasilan->build($periode->id);
        $surplusTanpa  = $peng['surplusTanpaPembatasan'];
        $surplusDengan = $peng['surplusDenganPembatasan'];

        // saldo akhir
        $saldoAkhirTanpa  = $saldoAwalTanpa  + $surplusTanpa;
        $saldoAkhirDengan = $saldoAwalDengan + $surplusDengan;
        $totalSaldoAwal   = $saldoAwalTanpa  + $saldoAwalDengan;
        $totalSaldoAkhir  = $saldoAkhirTanpa + $saldoAkhirDengan;

        return compact(
            'saldoAwalTanpa',  'saldoAwalDengan',  'totalSaldoAwal',
            'surplusTanpa',    'surplusDengan',
            'saldoAkhirTanpa', 'saldoAkhirDengan', 'totalSaldoAkhir'
        );
    }
}
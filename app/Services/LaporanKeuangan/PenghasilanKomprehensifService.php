<?php

namespace App\Services\LaporanKeuangan;

use App\Models\Akun;

class PenghasilanKomprehensifService
{
    public function __construct(private LaporanKeuanganService $saldo) {}

    public function build(?int $periodeId): array
    {
        if (!$periodeId) {
            return [
                'pendapatanTanpaPembatasan' => 0,
                'rincianTanpaPembatasan'   => collect(),
                'grupBeban'                => collect(),
                'jumlahBeban'              => 0,
                'surplusTanpaPembatasan'   => 0,
                'pendapatanDenganPembatasan' => 0,
                'rincianDenganPembatasan'    => collect(),
                'surplusDenganPembatasan'    => 0,
                'surplusDefisit'        => 0,
                'penghasilanKomprLain'  => 0,
                'totalKomprehensif'     => 0,
            ];
        }

        // tanpa pembatasan (4-1)
        $pendapatanTanpaPembatasan = $this->saldo->saldoByPrefix('4-1', $periodeId, 'KREDIT');
        $rincianTanpaPembatasan    = $this->saldo->getRincianAkun('4-1', $periodeId, 'KREDIT');

        // beban dinamis per grup header (5-xxxx)
        $headerBeban = Akun::where('kode_akun', 'like', '5-%')
            ->whereNull('parent_id')
            ->orderBy('kode_akun')
            ->get();

        $grupBeban   = collect();
        $jumlahBeban = 0;
        foreach ($headerBeban as $header) {
            $kode = rtrim($header->kode_akun, '0');
            $rincian = $this->saldo->getRincianAkun($kode, $periodeId, 'DEBIT');
            $total   = $rincian->sum('saldo');
            $grupBeban->push((object)[
                'kode_akun'  => $header->kode_akun,
                'nama_akun'  => $header->nama_akun,
                'rincian'    => $rincian,
                'total'      => $total,
            ]);
            $jumlahBeban += $total;
        }

        $surplusTanpaPembatasan = $pendapatanTanpaPembatasan - $jumlahBeban;

        // dengan pembatasan (4-2) — tanpa beban
        $pendapatanDenganPembatasan = $this->saldo->saldoByPrefix('4-2', $periodeId, 'KREDIT');
        $rincianDenganPembatasan    = $this->saldo->getRincianAkun('4-2', $periodeId, 'KREDIT');
        $surplusDenganPembatasan    = $pendapatanDenganPembatasan;

        // total
        $surplusDefisit       = $surplusTanpaPembatasan + $surplusDenganPembatasan;
        $penghasilanKomprLain = 0;
        $totalKomprehensif    = $surplusDefisit + $penghasilanKomprLain;

        return compact(
            'pendapatanTanpaPembatasan', 'rincianTanpaPembatasan',
            'grupBeban', 'jumlahBeban', 'surplusTanpaPembatasan',
            'pendapatanDenganPembatasan', 'rincianDenganPembatasan', 'surplusDenganPembatasan',
            'surplusDefisit', 'penghasilanKomprLain', 'totalKomprehensif'
        );
    }
}
<?php

namespace App\Services\LaporanKeuangan;

use App\Models\Akun;
use App\Models\Periode;

/** Laporan Penghasilan Komprehensif. */
class PenghasilanKomprehensifService implements LaporanKeuanganInterface
{
    public function __construct(private KalkulatorSaldoAkun $kalkulator) {}

    public function judulLaporan(): string   { return 'Laporan Penghasilan Komprehensif'; }
    public function namaViewHalaman(): string { return 'pages.laporan.penghasilan-komprehensif'; }
    public function namaViewPdf(): string     { return 'pages.laporan.pdf.penghasilan-komprehensif'; }

    public function susunLaporan(?Periode $periode, ?Periode $periodeSebelumnya): array
    {
        if (!$periode) {
            return [
                'pendapatanTanpaPembatasan'  => 0,
                'rincianTanpaPembatasan'     => collect(),
                'grupBeban'                  => collect(),
                'jumlahBeban'                => 0,
                'surplusTanpaPembatasan'     => 0,
                'pendapatanDenganPembatasan' => 0,
                'rincianDenganPembatasan'    => collect(),
                'surplusDenganPembatasan'    => 0,
                'surplusDefisit'             => 0,
                'penghasilanKomprLain'       => 0,
                'totalKomprehensif'          => 0,
            ];
        }

        $periodeId = $periode->id;

        // Pendapatan tanpa pembatasan (4-1)
        $rincianTanpaPembatasan    = $this->kalkulator->getRincianSaldoKelompok('4-1', $periodeId, 'KREDIT');
        $pendapatanTanpaPembatasan = $rincianTanpaPembatasan->sum('saldo');

        // Beban (5-) dinamis per grup header
        $grupBeban   = collect();
        $jumlahBeban = 0;
        foreach ($this->headerBeban() as $header) {
            $kode    = rtrim($header->kode_akun, '0');
            $rincian = $this->kalkulator->getRincianSaldoKelompok($kode, $periodeId, 'DEBIT');
            $total   = $rincian->sum('saldo');

            $grupBeban->push((object)[
                'kode_akun' => $header->kode_akun,
                'nama_akun' => $header->nama_akun,
                'rincian'   => $rincian,
                'total'     => $total,
            ]);
            $jumlahBeban += $total;
        }

        $surplusTanpaPembatasan = $pendapatanTanpaPembatasan - $jumlahBeban;

        // Pendapatan dengan pembatasan (4-2) — tidak dibebani beban operasional
        $rincianDenganPembatasan    = $this->kalkulator->getRincianSaldoKelompok('4-2', $periodeId, 'KREDIT');
        $pendapatanDenganPembatasan = $rincianDenganPembatasan->sum('saldo');
        $surplusDenganPembatasan    = $pendapatanDenganPembatasan;

        // Total
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

    private function headerBeban()
    {
        return Akun::where('kode_akun', 'like', '5-%')
            ->whereNull('parent_id')
            ->orderBy('kode_akun')
            ->get();
    }
}

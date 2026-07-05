<?php

namespace App\Services\LaporanKeuangan;

use App\Models\Periode;

/**
 * Laporan Perubahan Aset Neto.
 * Mengomposisi Laporan Posisi Keuangan (untuk saldo awal) dan Laporan
 * Penghasilan Komprehensif (untuk surplus/defisit).
 */
class PerubahanAsetNetoService implements LaporanKeuanganInterface
{
    public function __construct(
        private KalkulatorSaldoAkun $kalkulator,
        private PosisiKeuanganService $posisi,
        private PenghasilanKomprehensifService $penghasilan,
    ) {}

    public function judulLaporan(): string   { return 'Laporan Perubahan Aset Neto'; }
    public function namaViewHalaman(): string { return 'pages.laporan.perubahan-aset-neto'; }
    public function namaViewPdf(): string     { return 'pages.laporan.pdf.perubahan-aset-neto'; }

    public function susunLaporan(?Periode $periode, ?Periode $periodeSebelumnya): array
    {
        if (!$periode) {
            return [
                'saldoAwalTanpa'   => 0,
                'saldoAwalDengan'  => 0,
                'totalSaldoAwal'   => 0,
                'surplusTanpa'     => 0,
                'surplusDengan'    => 0,
                'dibebaskan'       => 0,
                'pkl'              => 0,
                'saldoAkhirTanpa'  => 0,
                'saldoAkhirDengan' => 0,
                'totalSaldoAkhir'  => 0,
            ];
        }

        // Saldo awal = posisi aset neto akhir periode sebelumnya.
        // Jika ini periode pertama, ambil dari jurnal PEMBUKA periode ini.
        if ($periodeSebelumnya) {
            $posisiSebelumnya = $this->posisi->susunLaporan(
                $periodeSebelumnya,
                $periodeSebelumnya->periodeSebelumnya()
            );
            $saldoAwalTanpa  = $posisiSebelumnya['asetNetoTanpaPembatasan'];
            $saldoAwalDengan = $posisiSebelumnya['asetNetoDenganPembatasan'];
        } else {
            $saldoAwalTanpa  = $this->kalkulator->hitungSaldoAwalKelompok('3-1', $periode->id, 'KREDIT');
            $saldoAwalDengan = $this->kalkulator->hitungSaldoAwalKelompok('3-2', $periode->id, 'KREDIT');
        }

        // Surplus/defisit dari laporan penghasilan komprehensif.
        $penghasilanData = $this->penghasilan->susunLaporan($periode, $periodeSebelumnya);
        $surplusTanpa    = $penghasilanData['surplusTanpaPembatasan'];
        $surplusDengan   = $penghasilanData['surplusDenganPembatasan'];

        // Aset neto yang dibebaskan dari pembatasan (ISAK 35): sebesar penyaluran
        // dana terikat pada periode berjalan. Untuk zakat = total beban 5-4xxx.
        // Nilai ini MENAMBAH aset neto tanpa pembatasan dan MENGURANGI yang
        // dengan pembatasan (reklasifikasi) — sejalan dengan tahap penutupan
        // PELEPASAN_PEMBATASAN pada modul akuntansi.
        $dibebaskan = $this->kalkulator->hitungSaldoKelompok('5-4', $periode->id, 'DEBIT');
        $pkl        = 0;

        $saldoAkhirTanpa  = $saldoAwalTanpa  + $surplusTanpa  + $dibebaskan;
        $saldoAkhirDengan = $saldoAwalDengan + $surplusDengan - $dibebaskan;
        $totalSaldoAwal   = $saldoAwalTanpa  + $saldoAwalDengan;
        $totalSaldoAkhir  = $saldoAkhirTanpa + $saldoAkhirDengan;

        return compact(
            'saldoAwalTanpa',  'saldoAwalDengan',  'totalSaldoAwal',
            'surplusTanpa',    'surplusDengan',
            'dibebaskan',      'pkl',
            'saldoAkhirTanpa', 'saldoAkhirDengan', 'totalSaldoAkhir'
        );
    }
}

<?php

namespace App\Services\LaporanKeuangan;

use App\Models\Akun;
use App\Models\DetailJurnal;
use App\Models\Periode;
use Illuminate\Support\Collection;

/** Laporan Posisi Keuangan (neraca). */
class PosisiKeuanganService implements LaporanKeuanganInterface
{
    public function __construct(private KalkulatorSaldoAkun $kalkulator) {}

    public function judulLaporan(): string   { return 'Laporan Posisi Keuangan'; }
    public function namaViewHalaman(): string { return 'pages.laporan.posisi-keuangan'; }
    public function namaViewPdf(): string     { return 'pages.laporan.pdf.posisi-keuangan'; }

    public function susunLaporan(?Periode $periode, ?Periode $periodeSebelumnya): array
    {
        if (!$periode) {
            return [
                'rincianAsetLancar'        => collect(),
                'jumlahAsetLancar'         => 0,
                'rincianAsetTetap'         => collect(),
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

        $pids = $periode->getIdsSampaiSekarang();

        // Aset lancar (1-1)
        $rincianAsetLancar = $this->kalkulator->getRincianSaldoKumulatifKelompok('1-1', $pids, 'DEBIT');
        $jumlahAsetLancar  = $rincianAsetLancar->sum('saldo');

        // Aset tetap (1-2), memperhitungkan akumulasi penyusutan
        $rincianAsetTetap = $this->rincikanAsetTetap($pids);
        $jumlahAsetTetap  = $rincianAsetTetap->sum('saldo');

        $jumlahAset = $jumlahAsetLancar + $jumlahAsetTetap;

        // Liabilitas (2-) per grup header
        $grupLiabilitas   = collect();
        $jumlahLiabilitas = 0;
        foreach ($this->headerAkun('2-%') as $header) {
            $kode    = rtrim($header->kode_akun, '0');
            $rincian = $this->kalkulator->getRincianSaldoKumulatifKelompok($kode, $pids, 'KREDIT');
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

        // Aset neto (3-1 tanpa pembatasan, 3-2 dengan pembatasan)
        $rincianAsetNetoTanpa     = $this->kalkulator->getRincianSaldoKumulatifKelompok('3-1', $pids, 'KREDIT');
        $asetNetoTanpaPembatasan  = $rincianAsetNetoTanpa->sum('saldo');
        $rincianAsetNetoDengan    = $this->kalkulator->getRincianSaldoKumulatifKelompok('3-2', $pids, 'KREDIT');
        $asetNetoDenganPembatasan = $rincianAsetNetoDengan->sum('saldo');

        $jumlahAsetNeto = $asetNetoTanpaPembatasan + $asetNetoDenganPembatasan;

        return compact(
            'rincianAsetLancar', 'jumlahAsetLancar',
            'rincianAsetTetap',  'jumlahAsetTetap', 'jumlahAset',
            'grupLiabilitas',    'jumlahLiabilitas',
            'asetNetoTanpaPembatasan', 'rincianAsetNetoTanpa',
            'asetNetoDenganPembatasan', 'rincianAsetNetoDengan',
            'jumlahAsetNeto'
        );
    }

    /** Header akun (level teratas) untuk sebuah prefix kode. */
    private function headerAkun(string $like): Collection
    {
        return Akun::where('kode_akun', 'like', $like)
            ->whereNull('parent_id')
            ->orderBy('kode_akun')
            ->get();
    }

    /** Rincian aset tetap (1-2) termasuk penanganan akumulasi penyusutan (kredit). */
    private function rincikanAsetTetap(array $periodeIds): Collection
    {
        return Akun::where('kode_akun', 'like', '1-2%')
            ->whereNotNull('parent_id')
            ->orderBy('kode_akun')
            ->get()
            ->map(function (Akun $akun) use ($periodeIds) {
                $saldo = $this->kalkulator->terapkanSaldoNormal(
                    DetailJurnal::where('akun_id', $akun->id)
                        ->whereHas('jurnal', fn($q) => $q
                            ->whereIn('periode_id', $periodeIds)
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
    }
}

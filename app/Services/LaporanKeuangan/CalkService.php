<?php

namespace App\Services\LaporanKeuangan;

use App\Models\Akun;
use App\Models\DetailJurnal;
use App\Models\Periode;
use Illuminate\Support\Collection;

/**
 * Catatan atas Laporan Keuangan (CALK).
 *
 * Untuk bagian arus kas, CALK TIDAK menghitung ulang sendiri, melainkan memakai
 * Laporan Arus Kas sebagai satu-satunya sumber kebenaran (menghindari dua cara
 * perhitungan yang bisa berbeda hasil).
 */
class CalkService implements LaporanKeuanganInterface
{
    public function __construct(
        private KalkulatorSaldoAkun $kalkulator,
        private PerubahanAsetNetoService $perubahanAsetNeto,
        private ArusKasService $arusKas,
    ) {}

    public function judulLaporan(): string   { return 'Catatan atas Laporan Keuangan'; }
    public function namaViewHalaman(): string { return 'pages.laporan.calk'; }
    public function namaViewPdf(): string     { return 'pages.laporan.pdf.calk'; }

    public function susunLaporan(?Periode $periode, ?Periode $periodeSebelumnya): array
    {
        if (!$periode) {
            return [
                'kasSetaraKas'         => collect(),
                'totalKas'             => 0,
                'piutang'              => collect(),
                'totalPiutang'         => 0,
                'asetTetap'            => collect(),
                'totalHargaPerolehan'  => 0,
                'totalAkumulasi'       => 0,
                'totalNilaiBuku'       => 0,
                'liabilitas'           => collect(),
                'totalLiabilitas'      => 0,
                'pendapatanTanpa'      => collect(),
                'totalPendapatanTanpa' => 0,
                'beban'                => collect(),
                'totalBeban'           => 0,
                'asetNeto'             => [],
                'arusKas'              => [],
            ];
        }

        $pids = $periode->getIdsSampaiSekarang();
        $pid  = $periode->id;

        // 1. Kas & setara kas (1-1 DEBIT kumulatif); pisahkan piutang.
        $rincianKasPiutang = $this->kalkulator->getRincianSaldoKumulatifKelompok('1-1', $pids, 'DEBIT')
            ->filter(fn($r) => $r->saldo != 0)->values();

        $piutang      = $rincianKasPiutang->filter(fn($r) => stripos($r->nama_akun, 'piutang') !== false)->values();
        $kasSetaraKas = $rincianKasPiutang->reject(fn($r) => stripos($r->nama_akun, 'piutang') !== false)->values();
        $totalKas     = $kasSetaraKas->sum('saldo');
        $totalPiutang = $piutang->sum('saldo');

        // 2. Aset tetap (1-2): harga perolehan, akumulasi penyusutan, nilai buku.
        $asetTetap = Akun::where('kode_akun', 'like', '1-2%')
            ->whereNotNull('parent_id')
            ->orderBy('kode_akun')
            ->get()
            ->map(function (Akun $akun) use ($pids) {
                $saldo = $this->kalkulator->terapkanSaldoNormal(
                    DetailJurnal::where('akun_id', $akun->id)
                        ->whereHas('jurnal', fn($q) => $q
                            ->whereIn('periode_id', $pids)
                            ->where('status', 'POSTED')
                        ),
                    $akun->saldo_normal
                );
                $isAkumulasi = $akun->saldo_normal === 'KREDIT';
                return (object)[
                    'nama_akun'       => $akun->nama_akun,
                    'is_akumulasi'    => $isAkumulasi,
                    'harga_perolehan' => $isAkumulasi ? 0 : $saldo,
                    'akumulasi'       => $isAkumulasi ? $saldo : 0,
                    'nilai_buku'      => $isAkumulasi ? -$saldo : $saldo,
                ];
            })->values();

        $totalHargaPerolehan = $asetTetap->sum('harga_perolehan');
        $totalAkumulasi      = $asetTetap->sum('akumulasi');
        $totalNilaiBuku      = $asetTetap->sum('nilai_buku');

        // 3. Liabilitas (2-).
        $liabilitas = collect();
        foreach ($this->header('2-%') as $header) {
            $kode    = rtrim($header->kode_akun, '0');
            $rincian = $this->kalkulator->getRincianSaldoKumulatifKelompok($kode, $pids, 'KREDIT')
                ->filter(fn($r) => $r->saldo != 0);
            $liabilitas = $liabilitas->concat($rincian);
        }
        $liabilitas      = $liabilitas->values();
        $totalLiabilitas = $liabilitas->sum('saldo');

        // 4. Pendapatan tanpa pembatasan (4-1, periode ini).
        $pendapatanTanpa      = $this->kalkulator->getRincianSaldoKelompok('4-1', $pid, 'KREDIT')
            ->filter(fn($r) => $r->saldo != 0)->values();
        $totalPendapatanTanpa = $pendapatanTanpa->sum('saldo');

        // 5. Beban (5-).
        $beban = collect();
        foreach ($this->header('5-%') as $header) {
            $kode    = rtrim($header->kode_akun, '0');
            $rincian = $this->kalkulator->getRincianSaldoKelompok($kode, $pid, 'DEBIT')
                ->filter(fn($r) => $r->saldo != 0);
            $beban = $beban->concat($rincian);
        }
        $beban      = $beban->values();
        $totalBeban = $beban->sum('saldo');

        // 6. Aset neto (dari Laporan Perubahan Aset Neto).
        $pan = $this->perubahanAsetNeto->susunLaporan($periode, $periodeSebelumnya);
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

        // 7. Arus kas — SATU SUMBER KEBENARAN: memakai Laporan Arus Kas.
        $ak = $this->arusKas->susunLaporan($periode, $periodeSebelumnya);
        $arusKas = [
            'operasional' => $ak['kasNetoOperasional'],
            'investasi'   => $ak['kasNetoInvestasi'],
            'pendanaan'   => $ak['kasNetoPendanaan'],
            'kenaikan'    => $ak['kenaikanNeto'],
            'kasAwal'     => $ak['kasAwal'],
            'kasAkhir'    => $ak['kasAkhir'],
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

    /** Header akun (level teratas) untuk sebuah prefix kode. */
    private function header(string $like): Collection
    {
        return Akun::where('kode_akun', 'like', $like)
            ->whereNull('parent_id')
            ->orderBy('kode_akun')
            ->get();
    }
}

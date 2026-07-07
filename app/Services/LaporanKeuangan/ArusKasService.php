<?php

namespace App\Services\LaporanKeuangan;

use App\Models\Akun;
use App\Models\DetailJurnal;
use App\Models\Periode;
use Illuminate\Support\Collection;

/** Laporan Arus Kas (metode langsung, disederhanakan). */
class ArusKasService implements LaporanKeuanganInterface
{
    public function __construct(private KalkulatorSaldoAkun $kalkulator) {}

    public function judulLaporan(): string   { return 'Laporan Arus Kas'; }
    public function namaViewHalaman(): string { return 'pages.laporan.arus-kas'; }
    public function namaViewPdf(): string     { return 'pages.laporan.pdf.arus-kas'; }

    public function susunLaporan(?Periode $periode, ?Periode $periodeSebelumnya): array
    {
        if (!$periode) {
            return [
                'penerimaanOperasional'  => collect(),
                'pengeluaranOperasional' => collect(),
                'kasNetoOperasional'     => 0,
                'penerimaanInvestasi'    => collect(),
                'pengeluaranInvestasi'   => collect(),
                'kasNetoInvestasi'       => 0,
                'penerimaanPendanaan'    => collect(),
                'penyaluranPendanaan'    => collect(),
                'kasNetoPendanaan'       => 0,
                'kenaikanNeto'           => 0,
                'kasAwal'                => 0,
                'kasAkhir'               => 0,
            ];
        }

        $pid = $periode->id;

        // ── Aktivitas Operasional ─────────────────────────────────────
        $penerimaanOperasional = $this->kalkulator->getRincianSaldoKelompok('4-1', $pid, 'KREDIT')
            ->filter(fn($r) => $r->saldo != 0)->values();
        $totalPenerimaanOp = $penerimaanOperasional->sum('saldo');

        // Semua beban (5-) KECUALI penyusutan/depresiasi (beban non-kas)
        $semuaBeban = collect();
        foreach ($this->headerBeban() as $header) {
            $kode    = rtrim($header->kode_akun, '0');
            $rincian = $this->kalkulator->getRincianSaldoKelompok($kode, $pid, 'DEBIT')
                ->filter(fn($r) => $r->saldo != 0)
                ->reject(fn($r) => preg_match('/penyusutan|depresiasi/i', $r->nama_akun));
            $semuaBeban = $semuaBeban->concat($rincian);
        }

        // Dana terikat (zakat/santunan/wakaf) diklasifikasikan sebagai pendanaan
        $pengeluaranOperasional = $semuaBeban
            ->reject(fn($r) => preg_match('/zakat|santunan|wakaf/i', $r->nama_akun))
            ->values();
        $penyaluranPendanaan = $semuaBeban
            ->filter(fn($r) => preg_match('/zakat|santunan|wakaf/i', $r->nama_akun))
            ->values();

        $kasNetoOperasional = $totalPenerimaanOp - $pengeluaranOperasional->sum('saldo');

        // ── Aktivitas Investasi ─────────────────────────────────────
        $asetTetapSekarang   = $this->totalHargaPerolehanAsetTetap($periode->getIdsSampaiSekarang());
        $asetTetapSebelumnya = $periodeSebelumnya
            ? $this->totalHargaPerolehanAsetTetap($periodeSebelumnya->getIdsSampaiSekarang())
            : 0;
        $deltaAsetTetap = $asetTetapSekarang - $asetTetapSebelumnya;

        $pengeluaranInvestasi = collect();
        if ($deltaAsetTetap > 0) {
            $pengeluaranInvestasi->push((object)[
                'nama_akun' => 'Pembelian peralatan & inventaris',
                'saldo'     => $deltaAsetTetap,
            ]);
        }

        $penerimaanInvestasi = $this->kalkulator->getRincianSaldoKelompok('4-1', $pid, 'KREDIT')
            ->filter(fn($r) => preg_match('/penjualan aset|divestasi/i', $r->nama_akun))
            ->values();

        $kasNetoInvestasi = $penerimaanInvestasi->sum('saldo') - $pengeluaranInvestasi->sum('saldo');

        // ── Aktivitas Pendanaan ─────────────────────────────────────
        $penerimaanPendanaan = $this->kalkulator->getRincianSaldoKelompok('4-2', $pid, 'KREDIT')
            ->filter(fn($r) => $r->saldo != 0)->values();
        $kasNetoPendanaan = $penerimaanPendanaan->sum('saldo') - $penyaluranPendanaan->sum('saldo');

        // ── Rekonsiliasi ──────────────────────────────────────────
        $kenaikanNeto = $kasNetoOperasional + $kasNetoInvestasi + $kasNetoPendanaan;

        // Kas awal: saldo kas (1-1, bukan piutang) s.d. periode sebelumnya
        $kasAwal = 0;
        if ($periodeSebelumnya) {
            $kasAwal = $this->kalkulator
                ->getRincianSaldoKumulatifKelompok('1-1', $periodeSebelumnya->getIdsSampaiSekarang(), 'DEBIT')
                ->reject(fn($r) => preg_match('/piutang/i', $r->nama_akun))
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

    private function headerBeban()
    {
        return Akun::where('kode_akun', 'like', '5-%')
            ->whereNull('parent_id')
            ->orderBy('kode_akun')
            ->get();
    }

    /** Total harga perolehan aset tetap (1-2, saldo normal DEBIT) s.d. periode tertentu. */
    private function totalHargaPerolehanAsetTetap(array $periodeIds): float
    {
        return (float) Akun::where('kode_akun', 'like', '1-2%')
            ->whereNotNull('parent_id')
            ->where('saldo_normal', 'DEBIT')
            ->get()
            ->sum(fn($akun) => $this->kalkulator->terapkanSaldoNormal(
                DetailJurnal::where('akun_id', $akun->id)
                    ->whereHas('jurnal', fn($q) => $q
                        ->whereIn('periode_id', $periodeIds)
                        ->where('status', 'POSTED')
                    ),
                'DEBIT'
            ));
    }
}

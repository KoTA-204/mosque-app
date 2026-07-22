<?php
namespace App\Services;

use App\Models\Akun;
use App\Models\DetailJurnal;
use App\Models\Periode;
use Illuminate\Support\Collection;

class DashboardService
{
    public function hitungSaldo($query, string $saldoNormal): float
    {
        $row = $query->selectRaw("
            SUM(CASE WHEN tipe = 'DEBIT'  THEN nominal ELSE 0 END) AS total_debit,
            SUM(CASE WHEN tipe = 'KREDIT' THEN nominal ELSE 0 END) AS total_kredit
        ")->first();

        $d = (float)($row->total_debit  ?? 0);
        $k = (float)($row->total_kredit ?? 0);

        return $saldoNormal === 'DEBIT' ? ($d - $k) : ($k - $d);
    }

    public function getPeriodeIdsUpTo(int $periodeId): array
    {
        $periode = Periode::find($periodeId);
        if (!$periode) return [$periodeId];

        return Periode::where('tanggal_akhir', '<=', $periode->tanggal_akhir)
            ->pluck('id')->toArray();
    }

    public function totalAsetLancar(array $periodeIds): float
    {
        $ids = Akun::where('kode_akun', 'like', '1-1%')
            ->whereNotNull('parent_id')
            ->pluck('id');

        if ($ids->isEmpty()) return 0;

        return $this->hitungSaldo(
            DetailJurnal::whereIn('akun_id', $ids)
                ->whereHas('jurnal', fn($q) => $q
                    ->whereIn('periode_id', $periodeIds)
                    ->where('status', 'POSTED')),
            'DEBIT'
        );
    }

    public function totalAsetTetap(array $periodeIds): float
    {
        $ids = Akun::where('kode_akun', 'like', '1-2%')
            ->whereNotNull('parent_id')
            ->pluck('id');

        if ($ids->isEmpty()) return 0;

        // Untuk aset tetap, ambil per akun karena ada yang saldo normal KREDIT (akumulasi)
        return Akun::where('kode_akun', 'like', '1-2%')
            ->whereNotNull('parent_id')
            ->get()
            ->sum(function (Akun $akun) use ($periodeIds) {
                $saldo = $this->hitungSaldo(
                    DetailJurnal::where('akun_id', $akun->id)
                        ->whereHas('jurnal', fn($q) => $q
                            ->whereIn('periode_id', $periodeIds)
                            ->where('status', 'POSTED')),
                    $akun->saldo_normal
                );
                return $akun->saldo_normal === 'KREDIT' ? -$saldo : $saldo;
            });
    }

    // ── Saldo Awal: Σ akun aset neto (3-xxxx) dari jurnal PEMBUKA periode aktif ──
    // Jika ada periode sebelumnya, ambil dari saldo aset neto kumulatif sampai periode lalu
    public function saldoAwalAsetNeto(int $periodeAktifId, ?int $periodeSebelumnyaId): float
    {
        if ($periodeSebelumnyaId) {
            // Saldo akhir periode sebelumnya = saldo awal periode aktif
            $pidsPrev = $this->getPeriodeIdsUpTo($periodeSebelumnyaId);
            return $this->totalAsetNeto($pidsPrev);
        }

        // Periode pertama: ambil dari jurnal PEMBUKA akun 3-xxxx
        $ids = Akun::where('kode_akun', 'like', '3-%')
            ->whereNotNull('parent_id')
            ->pluck('id');

        if ($ids->isEmpty()) return 0;

        return $this->hitungSaldo(
            DetailJurnal::whereIn('akun_id', $ids)
                ->whereHas('jurnal', fn($q) => $q
                    ->where('periode_id', $periodeAktifId)
                    ->where('status', 'POSTED')
                    ->where('jenis_jurnal', 'PEMBUKA')),
            'KREDIT' // akun ekuitas/aset neto bersaldo normal KREDIT
        );
    }

    // ── Total Aset Neto kumulatif (akun 3-xxxx) ──────────────────────────────
    public function totalAsetNeto(array $periodeIds): float
    {
        $ids = Akun::where('kode_akun', 'like', '3-%')
            ->whereNotNull('parent_id')
            ->pluck('id');

        if ($ids->isEmpty()) return 0;

        return $this->hitungSaldo(
            DetailJurnal::whereIn('akun_id', $ids)
                ->whereHas('jurnal', fn($q) => $q
                    ->whereIn('periode_id', $periodeIds)
                    ->where('status', 'POSTED')),
            'KREDIT'
        );
    }

    // ── Pemasukan: Σ akun pendapatan (4-1xxx, 4-2xxx) periode aktif ──────────
    public function totalPendapatan(int $periodeId): float
    {
        $ids = Akun::where(function ($q) {
                $q->where('kode_akun', 'like', '4-1%')
                  ->orWhere('kode_akun', 'like', '4-2%');
            })
            ->whereNotNull('parent_id')
            ->pluck('id');

        if ($ids->isEmpty()) return 0;

        return $this->hitungSaldo(
            DetailJurnal::whereIn('akun_id', $ids)
                ->whereHas('jurnal', fn($q) => $q
                    ->where('periode_id', $periodeId)
                    ->where('status', 'POSTED')
                    ->whereNull('tipe_penutupan')),
            'KREDIT'
        );
    }

    // ── Pengeluaran: Σ akun beban (5-xxxx) periode aktif ─────────────────────
    public function totalBeban(int $periodeId): float
    {
        $ids = Akun::where('kode_akun', 'like', '5-%')
            ->whereNotNull('parent_id')
            ->pluck('id');

        if ($ids->isEmpty()) return 0;

        return $this->hitungSaldo(
            DetailJurnal::whereIn('akun_id', $ids)
                ->whereHas('jurnal', fn($q) => $q
                    ->where('periode_id', $periodeId)
                    ->where('status', 'POSTED')
                    ->whereNull('tipe_penutupan')),
            'DEBIT'
        );
    }

    // ── Saldo Kas per dompet (untuk kartu dompet, tetap dari transaksi) ──────
    public function totalKasSetaraKas(array $periodeIds): float
    {
        $ids = Akun::where('kode_akun', 'like', '1-1%')
            ->whereNotNull('parent_id')
            ->get()
            ->filter(fn($a) => stripos($a->nama_akun, 'piutang') === false)
            ->pluck('id');

        if ($ids->isEmpty()) return 0;

        return $this->hitungSaldo(
            DetailJurnal::whereIn('akun_id', $ids)
                ->whereHas('jurnal', fn($q) => $q
                    ->whereIn('periode_id', $periodeIds)
                    ->where('status', 'POSTED')),
            'DEBIT'
        );
    }

    public function resolvePeriodeAktif(): array
    {
        $periode = Periode::berjalan();

        $periodePrev = null;
        if ($periode) {
            $periodePrev = Periode::where('tipe', $periode->tipe)
                ->where('tanggal_akhir', '<', $periode->tanggal_awal)
                ->orderByDesc('tanggal_akhir')
                ->first();
        }

        return [$periode, $periodePrev];
    }

    public function getRincianAkunKumulatif(string $prefix, array $periodeIds, string $saldoNormal): Collection
    {
        $akuns = Akun::where('kode_akun', 'like', $prefix . '%')
            ->whereNotNull('parent_id')
            ->orderBy('kode_akun')
            ->get();

        return $akuns->map(function (Akun $akun) use ($periodeIds, $saldoNormal) {
            $saldo = $this->hitungSaldo(
                DetailJurnal::where('akun_id', $akun->id)
                    ->whereHas('jurnal', fn($q) => $q
                        ->whereIn('periode_id', $periodeIds)
                        ->where('status', 'POSTED')),
                $saldoNormal
            );
            return (object)[
                'kode_akun' => $akun->kode_akun,
                'nama_akun' => $akun->nama_akun,
                'saldo'     => $saldo,
            ];
        })->values();
    }

    public function totalPendapatanTanpaPembatasan(int $periodeId): float
    {
        $ids = Akun::where('kode_akun', 'like', '4-1%')
            ->whereNotNull('parent_id')
            ->pluck('id');

        if ($ids->isEmpty()) return 0;

        return $this->hitungSaldo(
            DetailJurnal::whereIn('akun_id', $ids)
                ->whereHas('jurnal', fn($q) => $q
                    ->where('periode_id', $periodeId)
                    ->where('status', 'POSTED')
                    ->whereNull('tipe_penutupan')),
            'KREDIT'
        );
    }

    public function totalPendapatanDenganPembatasan(int $periodeId): float
    {
        $ids = Akun::where('kode_akun', 'like', '4-2%')
            ->whereNotNull('parent_id')
            ->pluck('id');

        if ($ids->isEmpty()) return 0;

        return $this->hitungSaldo(
            DetailJurnal::whereIn('akun_id', $ids)
                ->whereHas('jurnal', fn($q) => $q
                    ->where('periode_id', $periodeId)
                    ->where('status', 'POSTED')
                    ->whereNull('tipe_penutupan')),
            'KREDIT'
        );
    }

    public function ringkasanPublik(): array
    {
        [$periodeAktif, ] = $this->resolvePeriodeAktif();

        $approved = fn() => \App\Models\Transaksi::where(fn($q) =>
            $q->whereNull('status_persetujuan')->orWhere('status_persetujuan', 'APPROVED')
        );

        $dompetList = \App\Models\Dompet::all();
        $dompetIds  = $dompetList->pluck('id');

        $saldoAwal = $periodeAktif
            ? $dompetList->sum(function ($d) use ($approved, $periodeAktif) {
                $mutasiSebelum = (float) $approved()
                    ->where('dompet_id', $d->id)
                    ->where('tanggal_transaksi', '<', $periodeAktif->tanggal_awal->startOfDay())
                    ->selectRaw("SUM(CASE WHEN jenis_transaksi = 'PEMASUKAN' THEN jumlah ELSE -jumlah END) as mutasi")
                    ->value('mutasi') ?? 0;
                return (float) $d->saldo_awal + $mutasiSebelum;
            })
            : (float) $dompetList->sum('saldo_awal');

        $batasAwal  = $periodeAktif ? $periodeAktif->tanggal_awal->startOfDay() : \Carbon\Carbon::today()->startOfDay();
        $batasAkhir = $periodeAktif ? min($periodeAktif->tanggal_akhir->endOfDay(), \Carbon\Carbon::now()) : \Carbon\Carbon::now();

        $pemasukan = (float) $approved()
            ->whereIn('dompet_id', $dompetIds)
            ->where('jenis_transaksi', 'PEMASUKAN')
            ->whereBetween('tanggal_transaksi', [$batasAwal, $batasAkhir])
            ->sum('jumlah');

        $pengeluaran = (float) $approved()
            ->whereIn('dompet_id', $dompetIds)
            ->where('jenis_transaksi', 'PENGELUARAN')
            ->whereBetween('tanggal_transaksi', [$batasAwal, $batasAkhir])
            ->sum('jumlah');

        return [
            'periode_awal'  => 'Per ' . ucfirst($batasAwal->translatedFormat('l, d F Y')),
            'periode_akhir' => 'Saldo hari ini (' . \Carbon\Carbon::now()->translatedFormat('d F Y') . ')',
            'saldo_awal'    => $saldoAwal,
            'pemasukan'     => $pemasukan,
            'pengeluaran'   => $pengeluaran,
            'saldo_akhir'   => $saldoAwal + $pemasukan - $pengeluaran,
        ];
    }
}
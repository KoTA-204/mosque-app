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

    public function saldoByPrefix(string $prefix, int $periodeId, string $saldoNormal = 'KREDIT'): float
    {
        $ids = Akun::where('kode_akun', 'like', $prefix . '%')->pluck('id');
        if ($ids->isEmpty()) return 0;

        return $this->hitungSaldo(
            DetailJurnal::whereIn('akun_id', $ids)
                ->whereHas('jurnal', fn($q) => $q
                    ->where('periode_id', $periodeId)
                    ->where('status', 'POSTED')
                    ->whereNull('tipe_penutupan')),
            $saldoNormal
        );
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

    public function saldoPosisiByPrefix(string $prefix, int $periodeId, string $saldoNormal = 'DEBIT'): float
    {
        $ids = Akun::where('kode_akun', 'like', $prefix . '%')->pluck('id');
        if ($ids->isEmpty()) return 0;

        return $this->hitungSaldo(
            DetailJurnal::whereIn('akun_id', $ids)
                ->whereHas('jurnal', fn($q) => $q
                    ->where('periode_id', $periodeId)
                    ->where('status', 'POSTED')
                    ->where('jenis_jurnal', 'PEMBUKA')),
            $saldoNormal
        );
    }

    public function totalKasSetaraKas(array $periodeIds): float
    {
        return $this->getRincianAkunKumulatif('1-1', $periodeIds, 'DEBIT')
            ->filter(fn($r) => stripos($r->nama_akun, 'piutang') === false)
            ->sum('saldo');
    }

    public function totalKasSetaraKasPembuka(int $periodeId): float
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
                    ->where('periode_id', $periodeId)
                    ->where('status', 'POSTED')
                    ->where('jenis_jurnal', 'PEMBUKA')),
            'DEBIT'
        );
    }

    public function totalPendapatan(int $periodeId): float
    {
        return $this->saldoByPrefix('4-1', $periodeId, 'KREDIT')
             + $this->saldoByPrefix('4-2', $periodeId, 'KREDIT');
    }

    public function totalBeban(int $periodeId): float
    {
        $total = 0;
        $headerBeban = Akun::where('kode_akun', 'like', '5-%')
            ->whereNull('parent_id')
            ->get();

        foreach ($headerBeban as $header) {
            $kode = rtrim($header->kode_akun, '0');
            $total += $this->getRincianAkunKumulatif($kode, [$periodeId], 'DEBIT')
                ->sum('saldo');
        }

        return $total;
    }

    public function resolvePeriodeAktif(): array
    {
        $periode = Periode::where('status', true)->first()
            ?? Periode::orderByDesc('tanggal_akhir')->first();

        $periodePrev = null;
        if ($periode) {
            $periodePrev = Periode::where('tipe', $periode->tipe)
                ->where('tanggal_akhir', '<', $periode->tanggal_awal)
                ->orderByDesc('tanggal_akhir')
                ->first();
        }

        return [$periode, $periodePrev];
    }
}
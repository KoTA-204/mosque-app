<?php

namespace App\Services\Operasional;

use App\Models\Kegiatan;
use App\Models\Transaksi;
use Illuminate\Support\Facades\DB;

class ApprovalService
{
    // ── Query Dasar ───────────────────────────────────────────────────────
    private function queryDasarApproval()
    {
        return Transaksi::query()
            ->where(function ($q) {
                $q->whereNotNull('kegiatan_id')
                  ->orWhereHas('kencleng');
            });
    }

    public function hitungStatistikApproval(): array
    {
        return [
            'kencleng' => $this->queryDasarApproval()->whereHas('kencleng')->count(),
            'kegiatan' => $this->queryDasarApproval()->whereNotNull('kegiatan_id')->whereDoesntHave('kencleng')->count(),
            'pending'  => $this->queryDasarApproval()->where('status_approval', 'PENDING')->count(),
            'approved' => $this->queryDasarApproval()->where('status_approval', 'APPROVED')->count(),
            'rejected' => $this->queryDasarApproval()->where('status_approval', 'REJECTED')->count(),
            'revision' => $this->queryDasarApproval()->where('status_approval', 'REVISION')->count(),
        ];
    }

    // ── Query List ────────────────────────────────────────────────────────
    public function getTransaksiBerdasarkanStatus(
        string $status,
        string $search  = '',
        string $sumber  = '',
        string $dari    = '',
        string $sampai  = '',
        string $urut    = 'asc',
        int    $perPage = 10
    ) {
        return $this->queryDasarApproval()
            ->with(['dompet', 'kategoriTransaksi', 'pengguna', 'kegiatan', 'buktiTransaksi', 'kencleng.detail'])
            ->where('status_approval', $status)
            ->when($sumber === 'kegiatan', fn($q) => $q->whereNotNull('kegiatan_id')->whereDoesntHave('kencleng'))
            ->when($sumber === 'kencleng', fn($q) => $q->whereHas('kencleng'))
            ->when($dari,   fn($q) => $q->whereDate('tanggal_transaksi', '>=', $dari))
            ->when($sampai, fn($q) => $q->whereDate('tanggal_transaksi', '<=', $sampai))
            ->when($search, fn($q) => $q->where(function ($inner) use ($search) {
                $inner->whereHas('kegiatan', fn($k) => $k->where('nama_kegiatan', 'ilike', "%{$search}%"))
                      ->orWhereHas('pengguna',    fn($u) => $u->where('nama', 'ilike', "%{$search}%"))
                      ->orWhere('deskripsi', 'ilike', "%{$search}%");
            }))
            ->orderBy('tanggal_transaksi', in_array($urut, ['asc', 'desc']) ? $urut : 'asc')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function getDetailTransaksi(Transaksi $transaksi): Transaksi
    {
        return $transaksi->load('dompet', 'kategoriTransaksi', 'pengguna', 'kegiatan', 'buktiTransaksi');
    }

    // ── Core ──────────────────────────────────────────────────────────────
    private function ubahStatusApproval(Transaksi $transaksi, string $status, ?string $catatan = null): true|string
    {
        if ($transaksi->status_approval !== 'PENDING') {
            return 'Transaksi tidak dalam status PENDING';
        }

        $transaksi->update(['status_approval' => $status, 'catatan' => $catatan]);

        return true;
    }

    // ── Single Actions ────────────────────────────────────────────────────
    public function setujuiTransaksi(Transaksi $transaksi): true|string
    {
        return DB::transaction(function () use ($transaksi) {
            $result = $this->ubahStatusApproval($transaksi, 'APPROVED');

            if ($result === true && $transaksi->kegiatan_id) {
                $transaksi->kegiatan->tutupJikaSelesai();
            }

            return $result;
        });
    }

    public function tolakTransaksi(Transaksi $transaksi, string $catatan = ''): true|string
    {
        return $this->ubahStatusApproval($transaksi, 'REJECTED', $catatan ?: null);
    }

    public function revisiTransaksi(Transaksi $transaksi, string $catatan): true|string
    {
        return $this->ubahStatusApproval($transaksi, 'REVISION', $catatan);
    }

    // ── Bulk Actions ──────────────────────────────────────────────────────
    private function ubahStatusApprovalMassal(array $catatanMap, string $status): array
    {
        $done    = 0;
        $skipped = 0;

        DB::transaction(function () use ($catatanMap, $status, &$done, &$skipped) {
            $kegiatanIds = [];

            Transaksi::whereIn('id', array_keys($catatanMap))->get()
                ->each(function ($t) use ($catatanMap, $status, &$done, &$skipped, &$kegiatanIds) {
                    $result = $this->ubahStatusApproval($t, $status, $catatanMap[$t->id] ?? null);

                    if ($result !== true) {
                        $skipped++;
                        return;
                    }

                    if ($status === 'APPROVED' && $t->kegiatan_id) {
                        $kegiatanIds[] = $t->kegiatan_id;
                    }

                    $done++;
                });

            foreach (array_unique($kegiatanIds) as $kegiatanId) {
                Kegiatan::find($kegiatanId)?->tutupJikaSelesai();
            }
        });

        return compact('done', 'skipped');
    }

    public function setujuiTransaksiMassal(array $ids): array
    {
        return $this->ubahStatusApprovalMassal(array_fill_keys($ids, null), 'APPROVED');
    }

    public function tolakTransaksiMassal(array $catatanMap): array
    {
        return $this->ubahStatusApprovalMassal($catatanMap, 'REJECTED');
    }

    public function revisiTransaksiMassal(array $catatanMap): array
    {
        return $this->ubahStatusApprovalMassal($catatanMap, 'REVISION');
    }
}
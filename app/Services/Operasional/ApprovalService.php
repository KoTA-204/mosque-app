<?php

namespace App\Services\Operasional;

use App\Models\Kegiatan;
use App\Models\Transaksi;
use Illuminate\Support\Facades\DB;

class ApprovalService
{
    // ── Query Dasar ───────────────────────────────────────────────────────
    private function baseQuery()
    {
        return Transaksi::query()
            ->where(function ($q) {
                $q->whereNotNull('kegiatan_id')
                  ->orWhereHas('kencleng');
            });
    }

    public function getStats(): array
    {
        return [
            'kencleng' => $this->baseQuery()->whereHas('kencleng')->count(),
            'kegiatan' => $this->baseQuery()->whereNotNull('kegiatan_id')->whereDoesntHave('kencleng')->count(),
            'pending'  => $this->baseQuery()->where('status_approval', 'PENDING')->count(),
            'approved' => $this->baseQuery()->where('status_approval', 'APPROVED')->count(),
            'rejected' => $this->baseQuery()->where('status_approval', 'REJECTED')->count(),
            'revision' => $this->baseQuery()->where('status_approval', 'REVISION')->count(),
        ];
    }

    // ── Query List ────────────────────────────────────────────────────────
    public function getTransaksiByStatus(
        string $status,
        string $search  = '',
        string $sumber  = '',
        string $dari    = '',
        string $sampai  = '',
        string $urut    = 'asc',
        int    $perPage = 10
    ) {
        return $this->baseQuery()
            ->with(['dompet', 'kategoriTransaksi', 'user', 'kegiatan', 'buktiTransaksi', 'kencleng.detail'])
            ->where('status_approval', $status)
            ->when($sumber === 'kegiatan', fn($q) => $q->whereNotNull('kegiatan_id')->whereDoesntHave('kencleng'))
            ->when($sumber === 'kencleng', fn($q) => $q->whereHas('kencleng'))
            ->when($dari,   fn($q) => $q->whereDate('tanggal_transaksi', '>=', $dari))
            ->when($sampai, fn($q) => $q->whereDate('tanggal_transaksi', '<=', $sampai))
            ->when($search, fn($q) => $q->where(function ($inner) use ($search) {
                $inner->whereHas('kegiatan', fn($k) => $k->where('nama_kegiatan', 'ilike', "%{$search}%"))
                      ->orWhereHas('user',    fn($u) => $u->where('name', 'ilike', "%{$search}%"))
                      ->orWhere('deskripsi', 'ilike', "%{$search}%");
            }))
            ->orderBy('tanggal_transaksi', in_array($urut, ['asc', 'desc']) ? $urut : 'asc')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function getTransaksiById(Transaksi $transaksi): Transaksi
    {
        return $transaksi->load('dompet', 'kategoriTransaksi', 'user', 'kegiatan', 'buktiTransaksi');
    }

    // ── Core ──────────────────────────────────────────────────────────────
    private function changeStatus(Transaksi $transaksi, string $status, ?string $catatan = null): true|string
    {
        if ($transaksi->status_approval !== 'PENDING') {
            return 'Transaksi tidak dalam status PENDING';
        }

        $transaksi->update(['status_approval' => $status, 'catatan' => $catatan]);

        return true;
    }

    // ── Single Actions ────────────────────────────────────────────────────
    public function approve(Transaksi $transaksi): true|string
    {
        return DB::transaction(function () use ($transaksi) {
            $result = $this->changeStatus($transaksi, 'APPROVED');

            if ($result === true && $transaksi->kegiatan_id) {
                $transaksi->kegiatan->tutupJikaSelesai();
            }

            return $result;
        });
    }

    public function reject(Transaksi $transaksi, string $catatan = ''): true|string
    {
        return $this->changeStatus($transaksi, 'REJECTED', $catatan ?: null);
    }

    public function revision(Transaksi $transaksi, string $catatan): true|string
    {
        return $this->changeStatus($transaksi, 'REVISION', $catatan);
    }

    // ── Bulk Actions ──────────────────────────────────────────────────────
    private function bulkChangeStatus(array $catatanMap, string $status): array
    {
        $done    = 0;
        $skipped = 0;

        DB::transaction(function () use ($catatanMap, $status, &$done, &$skipped) {
            $kegiatanIds = [];

            Transaksi::whereIn('id', array_keys($catatanMap))->get()
                ->each(function ($t) use ($catatanMap, $status, &$done, &$skipped, &$kegiatanIds) {
                    $result = $this->changeStatus($t, $status, $catatanMap[$t->id] ?? null);

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

    public function bulkApprove(array $ids): array
    {
        return $this->bulkChangeStatus(array_fill_keys($ids, null), 'APPROVED');
    }

    public function bulkReject(array $catatanMap): array
    {
        return $this->bulkChangeStatus($catatanMap, 'REJECTED');
    }

    public function bulkRevisi(array $catatanMap): array
    {
        return $this->bulkChangeStatus($catatanMap, 'REVISION');
    }
}
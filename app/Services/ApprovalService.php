<?php

namespace App\Services;

use App\Models\Transaksi;
use Illuminate\Support\Facades\DB;

class ApprovalService
{
    // ── Approval (Bendahara) ───────────────────────────────────

    public function getTransaksiPending(
        string $search  = '',
        string $sumber  = '',   // '' | 'kegiatan' | 'kencleng'
        string $dari    = '',
        string $sampai  = '',
        int    $perPage = 10
    ) {
        return Transaksi::with([
                'dompet',
                'kategoriTransaksi',
                'user',
                'kegiatan',
                'buktiTransaksi',
                'kencleng.detail',
            ])
            ->where('status_approval', 'PENDING')

            // Hanya tampilkan yang punya kegiatan atau kencleng
            ->where(function ($q) {
                $q->whereNotNull('kegiatan_id')
                  ->orWhereHas('kencleng');
            })

            // Filter sumber
            ->when($sumber === 'kegiatan', fn($q) =>
                $q->whereNotNull('kegiatan_id')->whereDoesntHave('kencleng')
            )
            ->when($sumber === 'kencleng', fn($q) =>
                $q->whereHas('kencleng')
            )

            // Filter tanggal
            ->when($dari, fn($q) =>
                $q->whereDate('tanggal_transaksi', '>=', $dari)
            )
            ->when($sampai, fn($q) =>
                $q->whereDate('tanggal_transaksi', '<=', $sampai)
            )

            // Filter search
            ->when($search, fn($q) =>
                $q->where(function ($inner) use ($search) {
                    $inner->whereHas('kegiatan', fn($k) =>
                              $k->where('nama_kegiatan', 'ilike', "%{$search}%")
                          )
                          ->orWhereHas('user', fn($u) =>
                              $u->where('name', 'ilike', "%{$search}%")
                          )
                          ->orWhere('deskripsi', 'ilike', "%{$search}%");
                })
            )
            ->orderBy('created_at', 'asc')
            ->paginate($perPage);
    }

    public function getTransaksiById(Transaksi $transaksi): Transaksi
    {
        return $transaksi->load('dompet', 'kategoriTransaksi', 'user', 'kegiatan', 'buktiTransaksi');
    }

    public function approve(Transaksi $transaksi): bool|string
    {
        if ($transaksi->status_approval !== 'PENDING') {
            return 'Transaksi tidak dalam status PENDING';
        }

        $transaksi->update([
            'status_approval' => 'APPROVED',
            'catatan_revisi'  => null,
        ]);

        return true;
    }

    public function reject(Transaksi $transaksi, string $catatan = ''): bool|string
    {
        if ($transaksi->status_approval !== 'PENDING') {
            return 'Transaksi tidak dalam status PENDING';
        }

        $transaksi->update([
            'status_approval' => 'REJECTED',
            'catatan_revisi'  => $catatan,
        ]);

        return true;
    }

    public function revision(Transaksi $transaksi, string $catatan): bool|string
    {
        if ($transaksi->status_approval !== 'PENDING') {
            return 'Transaksi tidak dalam status PENDING';
        }

        $transaksi->update([
            'status_approval' => 'REVISION',
            'catatan_revisi'  => $catatan,
        ]);

        return true;
    }

    // ── Bulk Approval ──────────────────────────────────────────

    /**
     * Approve banyak transaksi sekaligus.
     * Hanya transaksi berstatus PENDING yang akan diproses.
     *
     * @param  array<int> $ids
     * @return array{approved: int, skipped: int}
     */
    public function bulkApprove(array $ids): array
    {
        $approved = 0;
        $skipped  = 0;

        DB::transaction(function () use ($ids, &$approved, &$skipped) {
            Transaksi::whereIn('id', $ids)->get()->each(function ($t) use (&$approved, &$skipped) {
                if ($t->status_approval !== 'PENDING') { $skipped++; return; }
                $t->update(['status_approval' => 'APPROVED', 'catatan_revisi' => null]);
                $approved++;
            });
        });

        return compact('approved', 'skipped');
    }

    /**
     * Reject banyak transaksi sekaligus dengan catatan per transaksi.
     * Hanya transaksi berstatus PENDING yang akan diproses.
     *
     * @param  array<int, string> $catatanMap  key = transaksi id, value = catatan
     * @return array{rejected: int, skipped: int}
     */
    public function bulkReject(array $catatanMap): array
    {
        $rejected = 0;
        $skipped  = 0;

        DB::transaction(function () use ($catatanMap, &$rejected, &$skipped) {
            Transaksi::whereIn('id', array_keys($catatanMap))->get()->each(function ($t) use ($catatanMap, &$rejected, &$skipped) {
                if ($t->status_approval !== 'PENDING') { $skipped++; return; }
                $t->update([
                    'status_approval' => 'REJECTED',
                    'catatan_revisi'  => $catatanMap[$t->id] ?? null,
                ]);
                $rejected++;
            });
        });

        return compact('rejected', 'skipped');
    }
}
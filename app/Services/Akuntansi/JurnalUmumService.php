<?php

namespace App\Services\Akuntansi;

use App\Models\Jurnal;
use App\Models\DetailJurnal;
use Illuminate\Pagination\LengthAwarePaginator;

class JurnalUmumService extends JurnalService
{
    private const JENIS = 'UMUM';

    /** Daftar jurnal umum terfilter. */
    public function daftar(array $filter): LengthAwarePaginator
    {
        return Jurnal::with(['detailJurnal.akun', 'periode', 'transaksi'])
            ->where('jenis_jurnal', self::JENIS)
            ->when($filter['bulan'] ?? null, function ($q) use ($filter) {
                $q->whereYear('tanggal', substr($filter['bulan'], 0, 4))
                  ->whereMonth('tanggal', substr($filter['bulan'], 5, 2));
            })
            ->when($filter['status'] ?? null, fn($q) => $q->where('status', strtoupper($filter['status'])))
            ->when($filter['search'] ?? null, fn($q) =>
                $q->where('keterangan', 'like', "%{$filter['search']}%")
                ->orWhereHas('transaksi', fn($q2) =>
                    $q2->where('deskripsi', 'like', "%{$filter['search']}%")
                )
            )
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->paginate($filter['per_page'] ?? 10)
            ->withQueryString();
    }

    /** Total debit & kredit (hanya POSTED). */
    public function getRingkasan(array $filter): array
    {
        $base = Jurnal::where('jenis_jurnal', self::JENIS)
            ->where('status', 'POSTED')
            ->when($filter['bulan'] ?? null, function ($q) use ($filter) {
                $q->whereYear('tanggal', substr($filter['bulan'], 0, 4))
                  ->whereMonth('tanggal', substr($filter['bulan'], 5, 2));
            });

        $ids = (clone $base)->pluck('id');

        return [
            'totalDebit'  => DetailJurnal::whereIn('jurnal_id', $ids)->where('tipe', 'DEBIT')->sum('nominal'),
            'totalKredit' => DetailJurnal::whereIn('jurnal_id', $ids)->where('tipe', 'KREDIT')->sum('nominal'),
        ];
    }

    // Posting massal memakai postingMassalKeBukuBesar() dari induk (DRY).
    // (method bulkPosting() lama sudah dihapus.)
}

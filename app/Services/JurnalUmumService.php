<?php

namespace App\Services;

use App\Models\Jurnal;
use App\Models\DetailJurnal;
use Illuminate\Pagination\LengthAwarePaginator;

class JurnalUmumService extends JurnalService
{
    private const JENIS = 'UMUM';

    // untuk daftar jurnal umum terfilter
    public function daftar(array $filter): LengthAwarePaginator
    {
        return Jurnal::with(['detailJurnal.akun', 'periode'])
            ->where('jenis_jurnal', self::JENIS)
            ->when($filter['bulan'] ?? null, function ($q) use ($filter) {
                $q->whereYear('tanggal', substr($filter['bulan'], 0, 4))
                  ->whereMonth('tanggal', substr($filter['bulan'], 5, 2));
            })
            ->when($filter['status'] ?? null, fn($q) => $q->where('status', strtoupper($filter['status'])))
            ->when($filter['search'] ?? null, fn($q) =>
                $q->where('keterangan', 'like', "%{$filter['search']}%")
            )
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->paginate($filter['per_page'] ?? 10)
            ->withQueryString();
    }

    // untuk total debit & kredit (hanya POSTED)
    public function summary(array $filter): array
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

    // untuk posting massal jurnal DRAFT
    public function bulkPosting(array $ids): array
    {
        $jurnals = Jurnal::whereIn('id', $ids)
            ->where('jenis_jurnal', self::JENIS)
            ->where('status', 'DRAFT')
            ->with('detailJurnal')
            ->get();

        $posted = 0;
        $errors = [];
        foreach ($jurnals as $jurnal) {
            if (!$this->isBalanced($jurnal)) {
                $errors[] = "Jurnal #{$jurnal->id} tidak seimbang, dilewati.";
                continue;
            }
            $jurnal->update(['status' => 'POSTED']);
            $posted++;
        }

        $message = "{$posted} jurnal berhasil diposting.";
        if (!empty($errors)) {
            $message .= ' ' . implode(' ', $errors);
        }

        return ['success' => $posted > 0, 'message' => $message];
    }
}
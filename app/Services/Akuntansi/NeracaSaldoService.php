<?php

namespace App\Services\Akuntansi;

use App\Models\Akun;
use App\Models\DetailJurnal;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class NeracaSaldoService
{
    public function getAkunQuery(?string $akunFilter, string $sortBy): Builder
    {
        $query = Akun::with('kategoriAkun')
            ->whereNotNull('parent_id')
            ->when($akunFilter && $akunFilter !== 'semua', function ($q) use ($akunFilter) {
                $q->whereHas('kategoriAkun', fn($k) => $k->where('kode_kategori', $akunFilter));
            });

        match ($sortBy) {
            'kode_akun_desc' => $query->orderByDesc('kode_akun'),
            'nama_asc'       => $query->orderBy('nama_akun'),
            default          => $query->orderBy('kode_akun'),
        };

        return $query;
    }

    public function hitungSaldo(array $akunIds, ?string $periodeId): Collection
    {
        return DetailJurnal::whereIn('akun_id', $akunIds)
            ->whereHas('jurnal', function ($q) use ($periodeId) {
                $q->where('status', 'POSTED');
                if ($periodeId) $q->where('periode_id', $periodeId);
            })
            ->selectRaw('akun_id, tipe, SUM(nominal) as total')
            ->groupBy('akun_id', 'tipe')
            ->get()
            ->groupBy('akun_id');
    }
}

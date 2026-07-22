<?php

namespace App\Services\ManajemenAkses;

use App\Models\HakAkses;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class HakAksesService
{
    public function getDataHakAkses(
        ?string $search  = null,
        ?string $module  = null,
        ?string $action  = null,
        int     $perPage = 10
    ): LengthAwarePaginator {
        return HakAkses::with('peran')
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('nama_hak_akses', 'ilike', "%{$search}%")
                      ->orWhere('kode_hak_akses', 'ilike', "%{$search}%")
                      ->orWhere('deskripsi', 'ilike', "%{$search}%");
                });
            })
            ->when($module, fn($q) => $q->where('modul', $module))
            ->when($action, fn($q) => $q->where('aksi', $action))
            ->orderBy('modul')
            ->orderBy('nama_hak_akses')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function getDaftarModul(): Collection
    {
        return HakAkses::query()
            ->distinct()
            ->orderBy('modul')
            ->pluck('modul');
    }

    public function getDetailHakAkses(HakAkses $hak_akses): HakAkses
    {
        return $hak_akses->load('peran');
    }

    public function buatHakAkses(array $data): HakAkses
    {
        return DB::transaction(function () use ($data) {
            return HakAkses::create($data);
        });
    }

    public function perbaruiHakAkses(HakAkses $hak_akses, array $data): HakAkses
    {
        return DB::transaction(function () use ($hak_akses, $data) {
            $hak_akses->update($data);
            return $hak_akses->fresh()->load('peran');
        });
    }

    public function hapusHakAkses(HakAkses $hak_akses): bool|string
    {
        return DB::transaction(function () use ($hak_akses) {
            if ($hak_akses->peran()->exists()) {
                return 'HakAkses masih dipakai oleh peran, tidak bisa dihapus.';
            }

            $hak_akses->delete();

            return true;
        });
    }
}
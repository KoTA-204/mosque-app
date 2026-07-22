<?php

namespace App\Services\ManajemenAkses;

use App\Models\Peran;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PeranService
{
    public function getDataPeran(?string $search = null, int $perPage = 5)
    {
        return Peran::withCount('pengguna')
            ->with('hak_akses')
            ->when($search, function ($query) use ($search) {
                $query->where('nama_peran', 'ilike', "%{$search}%")
                    ->orWhere('deskripsi', 'ilike', "%{$search}%");
            })
            ->orderBy('nama_peran')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function getDetailPeran(Peran $peran): Peran
    {
        return $peran->load('hak_akses');
    }

    public function buatPeran(array $data): Peran
    {
        return DB::transaction(function () use ($data) {
            try {
                $hakAksesIds = $data['hak_akses_ids'] ?? null;
                unset($data['hak_akses_ids']);

                $peran = Peran::create($data);

                if (!empty($hakAksesIds)) {
                    $peran->hak_akses()->sync($hakAksesIds);
                }

                return $peran;
            } catch (\Exception $e) {
                Log::error('PeranService@create failed', [
                    'data'    => $data,
                    'message' => $e->getMessage(),
                    'trace'   => $e->getTraceAsString(),
                ]);

                throw $e;
            }
        });
    }

    public function perbaruiPeran(Peran $peran, array $data): Peran
    {
        return DB::transaction(function () use ($peran, $data) {
            try {
                $hakAksesIds = $data['hak_akses_ids'] ?? null;
                unset($data['hak_akses_ids']);

                $peran->update($data);

                if ($hakAksesIds !== null) {
                    $peran->hak_akses()->sync($hakAksesIds);
                }
                
                return $peran->fresh()->load('hak_akses');
            } catch (\Exception $e) {
                Log::error('PeranService@update failed', [
                    'peran_id' => $peran->id,
                    'data'    => $data,
                    'message' => $e->getMessage(),
                    'trace'   => $e->getTraceAsString(),
                ]);
                throw $e;
            }
        });
    }

    public function hapusPeran(Peran $peran): bool|string
    {
        if ($peran->pengguna()->exists()) {
            return 'Peran masih dipakai oleh pengguna, tidak bisa dihapus';
        }

        return DB::transaction(function () use ($peran) {
            try {
                $peran->hak_akses()->detach();
                $peran->delete();

                return true;
            } catch (\Exception $e) {
                Log::error('PeranService@delete failed', [
                    'peran_id' => $peran->id,
                    'message' => $e->getMessage(),
                    'trace'   => $e->getTraceAsString(),
                ]);

                throw $e;
            }
        });
    }

    public function tetapkanHakAksesPeran(Peran $peran, array $hakAksesIds): Peran
    {
        return DB::transaction(function () use ($peran, $hakAksesIds) {
            try {
                $peran->hak_akses()->sync($hakAksesIds);

                return $peran->fresh()->load('hak_akses');
            } catch (\Exception $e) {
                Log::error('PeranService@assignHakAkses failed', [
                    'peran_id'        => $peran->id,
                    'hak_akses_ids' => $hakAksesIds,
                    'message'        => $e->getMessage(),
                    'trace'          => $e->getTraceAsString(),
                ]);
                throw $e;
            }
        });
    }
}
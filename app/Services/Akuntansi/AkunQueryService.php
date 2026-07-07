<?php

namespace App\Services\Akuntansi;

use App\Models\Akun;

/**
 * Service query akun (Pure Fabrication).
 *
 * Menyatukan logika "daftar akun dikelompokkan per kategori" yang sebelumnya
 * digandakan di JurnalKoreksiService dan JurnalPenyesuaianService.
 */
class AkunQueryService
{
    /**
     * Daftar akun level detail, dikelompokkan berdasarkan kategori.
     *
     * @param array $excludeKode Kode akun yang dikecualikan (mis. Kas & Bank
     *                           untuk jurnal penyesuaian).
     */
    public function getGroupedAkun(array $excludeKode = []): array
    {
        return Akun::with('kategoriAkun')
            ->whereNotNull('parent_id')
            ->when(!empty($excludeKode), fn($q) => $q->whereNotIn('kode_akun', $excludeKode))
            ->orderBy('kode_akun')
            ->get()
            ->groupBy(fn($akun) => $akun->kategoriAkun->nama_kategori ?? 'Lainnya')
            ->map(fn($group, $kategori) => [
                'kategori' => $kategori,
                'akun'     => $group->map(fn($a) => [
                    'id'           => $a->id,
                    'kode_akun'    => $a->kode_akun,
                    'nama_akun'    => $a->nama_akun,
                    'saldo_normal' => $a->saldo_normal,
                    'label'        => $a->kode_akun . ' — ' . $a->nama_akun,
                ])->values(),
            ])
            ->values()
            ->toArray();
    }
}

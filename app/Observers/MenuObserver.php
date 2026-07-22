<?php

namespace App\Observers;

use App\Models\Menu;
use App\Models\HakAkses;

class MenuObserver
{
    public function saved(Menu $menu): void
    {
        if ($menu->route_name && ! $menu->hak_akses_id) {
            $this->attachViewHakAkses($menu);
        }
    }

    private function attachViewHakAkses(Menu $menu): void
    {
        $parts = explode('.', $menu->route_name);

        if (($parts[0] ?? null) === 'dashboard') {
            $module = ($parts[1] ?? null) === 'index'
                ? 'dashboard'
                : ($parts[1] ?? 'dashboard');
        } else {
            $module = $parts[0];
        }

        $hak_akses = HakAkses::firstOrCreate(
            [
                'modul' => $module,
                'aksi' => 'view',
            ],
            [
                'kode_hak_akses' => 'VIEW_' . strtoupper($module),
                'nama_hak_akses' => 'View ' . ucfirst($module),
                'aktif'       => true,
            ]
        );

        $menu->hak_akses()->associate($hak_akses);
        $menu->saveQuietly();
        }
}

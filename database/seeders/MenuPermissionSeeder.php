<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Tabel menu_permission tidak ada di schema.
 * Relasi permission sudah ditangani via kolom permission_id pada tabel menus
 * (di-set langsung oleh MenuSeeder). Seeder ini dikosongkan.
 */
class MenuPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // no-op
    }
}

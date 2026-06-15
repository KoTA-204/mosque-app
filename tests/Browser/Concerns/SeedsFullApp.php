<?php

namespace Tests\Browser\Concerns;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;

/**
 * Menyemai SELURUH data aplikasi nyata (Database\Seeders\DatabaseSeeder) untuk
 * test sistem yang bergantung pada data realistis: kegiatan, transaksi kegiatan,
 * kencleng, approval, CoA, dsb.
 *
 * Akun hasil seeder (password = 'password'):
 *   - admin@masjid.id       (Super Admin)
 *   - bendahara1@masjid.id  (Bendahara 1  - punya VIEW/MANAGE_APPROVAL)
 *   - bendahara2@masjid.id  (Bendahara 2)
 *   - phm@masjid.id         (PHM          - punya CREATE_KENCLENG)
 *   - panitia@masjid.id     (Panitia Khusus - pemilik semua kegiatan seeded)
 *
 * Catatan data penting yang ditemukan dari seeder:
 *   - Semua Kegiatan disemai berstatus AKTIF; jalankan command kegiatan:tutup-otomatis
 *     untuk menutup yang sudah lewat & semua APPROVED (mis. "Maulid Nabi 1446 H").
 *   - "Renovasi Serambi Masjid" TIDAK punya transaksi -> aman untuk uji hapus.
 *   - Terdapat banyak transaksi PENDING (Bakti Sosial, Qurban, dll) untuk uji approval.
 *   - Kencleng #1 dimiliki Administrator & transaksinya berstatus non-editable.
 */
trait SeedsFullApp
{
    protected function seedFullApp(): void
    {
        $this->seed(DatabaseSeeder::class);
    }

    protected function userByEmail(string $email): User
    {
        return User::where('email', $email)->firstOrFail();
    }
}

<?php

namespace Tests\Feature\Integration\Concerns;

use App\Models\Dompet;
use App\Models\Kegiatan;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Transaksi;
use App\Models\User;
use Illuminate\Support\Carbon;

/**
 * Helper RBAC + factory ringan untuk ITD Integration Test MosQue (KoTA-204).
 *
 * Disesuaikan dengan struktur kode ASLI:
 *  - permissions: permission_code (unik), permission_name, module, action, is_active
 *  - roles: role_name
 *  - pivot: permission_role (permission_id, role_id)
 *  - users: role_id (FK), status enum active|inactive
 *  - User::hasPermission($code) -> cek permission_code + is_active pada role
 *  - User::hasRole($slug)       -> bandingkan Str::slug(role_name)
 *
 * CATATAN: Role diasumsikan punya relasi belongsToMany Permission bernama
 * permissions() melalui tabel pivot permission_role (sesuai PermissionService
 * & RoleController pada kode batch 1). Sesuaikan bila nama relasi berbeda.
 */
trait InteractsWithRbac
{
    protected function buatPermission(string $code): Permission
    {
        $parts = explode('_', $code);

        return Permission::firstOrCreate(
            ['permission_code' => $code],
            [
                'permission_name' => $code,
                'module'          => $parts[1] ?? 'GENERAL',
                'action'          => $parts[0] ?? 'VIEW',
                'description'     => $code,
                'is_active'       => true,
            ]
        );
    }

    protected function buatRole(string $namaRole, array $permissionCodes = []): Role
    {
        $role = Role::firstOrCreate(['role_name' => $namaRole]);

        $ids = collect($permissionCodes)
            ->map(fn (string $code) => $this->buatPermission($code)->id)
            ->all();

        if ($ids !== []) {
            $role->permissions()->syncWithoutDetaching($ids);
        }

        return $role;
    }

    protected function buatUser(Role $role, array $attrs = []): User
    {
        return User::factory()->create(array_merge([
            'role_id' => $role->id,
            'status'  => 'active',
        ], $attrs));
    }

    protected function buatDompet(array $attrs = []): Dompet
    {
        return Dompet::factory()->create($attrs);
    }

    /**
     * Buat 1 baris transaksi dengan kolom-kolom wajib sesuai migration.
     * dompet_id WAJIB (restrictOnDelete), user_id WAJIB.
     */
    protected function buatTransaksi(array $attrs = []): Transaksi
    {
        $defaults = [
            'dompet_id'         => $attrs['dompet_id'] ?? $this->buatDompet()->id,
            'user_id'           => $attrs['user_id'] ?? $this->buatUser($this->buatRole('Bendahara'))->id,
            'kegiatan_id'       => null,
            'tanggal_transaksi' => Carbon::now()->toDateString(),
            'jenis_transaksi'   => 'PEMASUKAN',
            'jumlah'            => 100000,
            'deskripsi'         => 'Transaksi uji ITD',
            'status_approval'   => 'PENDING',
            'status_jurnal'     => 'UNMAPPED',
        ];

        return Transaksi::create(array_merge($defaults, $attrs));
    }

    /**
     * Buat kegiatan. Defaultnya AKTIF & masih berjalan (boleh input transaksi).
     */
    protected function buatKegiatan(int $panitiaId, array $attrs = []): Kegiatan
    {
        $defaults = [
            'nama_kegiatan'   => 'Qurban 1446H',
            'jenis_kegiatan'  => 'QURBAN',
            'tanggal_mulai'   => Carbon::now()->subDays(2)->toDateString(),
            'tanggal_selesai' => Carbon::now()->addDays(5)->toDateString(),
            'anggaran'        => 0,
            'status'          => 'AKTIF',
            'panitia_id'      => $panitiaId,
        ];

        return Kegiatan::create(array_merge($defaults, $attrs));
    }
}

<?php

namespace App\Services\ManajemenAkses;

use App\Models\Permission;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PermissionService
{
    public function getDataPermission(
        ?string $search  = null,
        ?string $module  = null,
        ?string $action  = null,
        int     $perPage = 10
    ): LengthAwarePaginator {
        return Permission::with('roles')
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('permission_name', 'ilike', "%{$search}%")
                      ->orWhere('permission_code', 'ilike', "%{$search}%")
                      ->orWhere('description', 'ilike', "%{$search}%");
                });
            })
            ->when($module, fn($q) => $q->where('module', $module))
            ->when($action, fn($q) => $q->where('action', $action))
            ->orderBy('module')
            ->orderBy('permission_name')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function getDaftarModul(): Collection
    {
        return Permission::query()
            ->distinct()
            ->orderBy('module')
            ->pluck('module');
    }

    public function getDetailPermission(Permission $permission): Permission
    {
        return $permission->load('roles');
    }

    public function buatPermission(array $data): Permission
    {
        return DB::transaction(function () use ($data) {
            return Permission::create($data);
        });
    }

    public function perbaruiPermission(Permission $permission, array $data): Permission
    {
        return DB::transaction(function () use ($permission, $data) {
            $permission->update($data);
            return $permission->fresh()->load('roles');
        });
    }

    public function hapusPermission(Permission $permission): bool|string
    {
        return DB::transaction(function () use ($permission) {
            if ($permission->roles()->exists()) {
                return 'Permission masih dipakai oleh role, tidak bisa dihapus.';
            }

            $permission->delete();

            return true;
        });
    }
}
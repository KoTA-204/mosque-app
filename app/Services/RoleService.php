<?php

namespace App\Services;

use App\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RoleService
{
    public function getAll(?string $search = null, int $perPage = 5)
    {
        return Role::withCount('users')
            ->with('permissions')
            ->when($search, function ($query) use ($search) {
                $query->where('role_name', 'ilike', "%{$search}%")
                    ->orWhere('description', 'ilike', "%{$search}%");
            })
            ->orderBy('role_name')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function getById(Role $role): Role
    {
        return $role->load('permissions');
    }

    public function create(array $data): Role
    {
        return DB::transaction(function () use ($data) {
            try {
                $permissionIds = $data['permission_ids'] ?? null;
                unset($data['permission_ids']);

                $role = Role::create($data);

                if (!empty($permissionIds)) {
                    $role->permissions()->sync($permissionIds);
                }

                return $role;
            } catch (\Exception $e) {
                Log::error('RoleService@create failed', [
                    'data'    => $data,
                    'message' => $e->getMessage(),
                    'trace'   => $e->getTraceAsString(),
                ]);

                throw $e;
            }
        });
    }

    public function update(Role $role, array $data): Role
    {
        return DB::transaction(function () use ($role, $data) {
            try {
                $permissionIds = $data['permission_ids'] ?? null;
                unset($data['permission_ids']);

                $role->update($data);

                if ($permissionIds !== null) {
                    $role->permissions()->sync($permissionIds);
                }

                return $role->fresh()->load('permissions');
            } catch (\Exception $e) {
                Log::error('RoleService@update failed', [
                    'role_id' => $role->id,
                    'data'    => $data,
                    'message' => $e->getMessage(),
                    'trace'   => $e->getTraceAsString(),
                ]);

                throw $e;
            }
        });
    }

    public function delete(Role $role): bool|string
    {
        if ($role->users()->exists()) {
            return 'Role masih dipakai oleh user, tidak bisa dihapus';
        }

        return DB::transaction(function () use ($role) {
            try {
                $role->permissions()->detach();
                $role->delete();

                return true;
            } catch (\Exception $e) {
                Log::error('RoleService@delete failed', [
                    'role_id' => $role->id,
                    'message' => $e->getMessage(),
                    'trace'   => $e->getTraceAsString(),
                ]);

                throw $e;
            }
        });
    }

    public function assignPermissions(Role $role, array $permissionIds): Role
    {
        return DB::transaction(function () use ($role, $permissionIds) {
            try {
                $role->permissions()->sync($permissionIds);

                return $role->fresh()->load('permissions');
            } catch (\Exception $e) {
                Log::error('RoleService@assignPermissions failed', [
                    'role_id'        => $role->id,
                    'permission_ids' => $permissionIds,
                    'message'        => $e->getMessage(),
                    'trace'          => $e->getTraceAsString(),
                ]);

                throw $e;
            }
        });
    }
}
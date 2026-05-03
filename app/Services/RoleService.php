<?php

namespace App\Services;

use App\Models\Role;
use Illuminate\Database\Eloquent\Collection;

class RoleService
{
    public function getAll(): Collection
    {
        return Role::with('permissions')->get();
    }

    public function getById(Role $role): Role
    {
        return $role->load('permissions');
    }

    public function create(array $data): Role
    {
        $permissionIds = $data['permission_ids'] ?? [];
        unset($data['permission_ids']);

        $role = Role::create($data);

        if (!empty($permissionIds)) {
            $role->permissions()->sync($permissionIds);
        }

        return $role;
    }

    public function update(Role $role, array $data): Role
    {
        $permissionIds = $data['permission_ids'] ?? [];
        unset($data['permission_ids']);

        $role->update($data);
        $role->permissions()->sync($permissionIds);

        return $role->fresh()->load('permissions');
    }

    public function delete(Role $role): bool|string
    {
        if ($role->users()->exists()) {
            return 'Role masih dipakai oleh user, tidak bisa dihapus';
        }

        $role->delete();

        return true;
    }

    public function assignPermissions(Role $role, array $permissionIds): Role
    {
        $role->permissions()->sync($permissionIds);

        return $role->fresh()->load('permissions');
    }
}
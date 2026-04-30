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
        return Role::create($data);
    }

    public function update(Role $role, array $data): Role
    {
        $role->update($data);

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
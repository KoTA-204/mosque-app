<?php

namespace App\Services;

use App\Models\Permission;
use Illuminate\Database\Eloquent\Collection;

class PermissionService
{
    public function getAll(): Collection
    {
        return Permission::with('roles')->get();
    }

    public function getById(Permission $permission): Permission
    {
        return $permission->load('roles');
    }

    public function create(array $data): Permission
    {
        return Permission::create($data);
    }

    public function update(Permission $permission, array $data): Permission
    {
        $permission->update($data);
        return $permission->fresh()->load('roles');
    }

    public function delete(Permission $permission): bool|string
    {
        if ($permission->roles()->exists()) {
            return 'Permission masih dipakai oleh role, tidak bisa dihapus';
        }

        $permission->delete();

        return true;
    }
}
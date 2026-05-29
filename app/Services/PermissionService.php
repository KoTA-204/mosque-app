<?php

namespace App\Services;

use App\Models\Permission;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PermissionService
{
    /**
     * Get paginated permissions with optional search and filters.
     */
    public function getAll(
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

    /**
     * Get list of distinct module values for the filter dropdown.
     */
    public function getDistinctModules(): Collection
    {
        return Permission::query()
            ->distinct()
            ->orderBy('module')
            ->pluck('module');
    }

    /**
     * Load a single permission with its related roles.
     */
    public function getById(Permission $permission): Permission
    {
        return $permission->load('roles');
    }

    /**
     * Create a new permission inside a transaction.
     */
    public function create(array $data): Permission
    {
        return DB::transaction(function () use ($data) {
            return Permission::create($data);
        });
    }

    /**
     * Update an existing permission inside a transaction.
     */
    public function update(Permission $permission, array $data): Permission
    {
        return DB::transaction(function () use ($permission, $data) {
            $permission->update($data);
            return $permission->fresh()->load('roles');
        });
    }

    /**
     * Delete a permission if it is not assigned to any role.
     * Runs inside a transaction to guarantee atomicity.
     *
     * @return true|string  Returns true on success, or an error message string.
     */
    public function delete(Permission $permission): bool|string
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
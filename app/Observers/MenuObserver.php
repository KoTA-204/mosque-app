<?php

namespace App\Observers;

use App\Models\Menu;
use App\Models\Permission;

class MenuObserver
{
    public function saved(Menu $menu): void
    {
        // Auto attach VIEW permission untuk child menu
        if ($menu->route_name) {
            $this->attachViewPermission($menu);
        }
    }

    public function deleted(Menu $menu): void
    {
        // Update permission parent setelah child dihapus
        if ($menu->parent_id && $menu->parent) {
            $this->syncParentPermissions($menu->parent);
        }
    }

    private function attachViewPermission(Menu $menu): void
    {
        $parts = explode('.', $menu->route_name);

        $module = $parts[1] ?? $parts[0];
        $permission = Permission::where('module', $module)
            ->where('action', 'view')
            ->where('is_active', true)
            ->first();

        if ($permission) {
            $menu->permission()->associate($permission);
            $menu->saveQuietly();
        }
    }

    private function syncParentPermissions(Menu $parent): void
    {
        // Kumpulkan semua permission dari child yang aktif
        $childPermissionIds = $parent->children()
            ->where('is_active', true)
            ->with('permissions')
            ->get()
            ->flatMap(fn($child) => $child->permissions->pluck('id'))
            ->unique()
            ->values()
            ->toArray();

        $parent->permissions()->sync($childPermissionIds);
    }
}
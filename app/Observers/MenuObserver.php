<?php

namespace App\Observers;

use App\Models\Menu;
use App\Models\Permission;

class MenuObserver
{
    public function saved(Menu $menu): void
    {
        if ($menu->route_name && ! $menu->permission_id) {
            $this->attachViewPermission($menu);
        }
    }

    private function attachViewPermission(Menu $menu): void
    {
        $parts  = explode('.', $menu->route_name);
        $module = $parts[1] ?? $parts[0];

        $permission = Permission::firstOrCreate(
            [
                'module' => $module,
                'action' => 'view',
            ],
            [
                'permission_code' => 'VIEW_' . strtoupper($module),
                'permission_name' => 'View ' . ucfirst($module),
                'is_active'       => true,
            ]
        );

        $menu->permissions()->associate($permission);
        $menu->saveQuietly();
        }
}

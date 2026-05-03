<?php

namespace App\Services;

use App\Models\Menu;
use Illuminate\Database\Eloquent\Collection;

class MenuService
{
    public function getAll(): Collection
    {
        return Menu::with('children', 'parent')
            ->orderBy('sort_order')
            ->get();
    }

    public function getAllFlat(): Collection
    {
        return Menu::orderBy('menu_name')->get();
    }

    public function getById(Menu $menu): Menu
    {
        return $menu->load('children', 'parent', 'permissions');
    }

    public function create(array $data): Menu
    {
        return Menu::create($data);
    }

    public function update(Menu $menu, array $data): Menu
    {
        $menu->update($data);
        return $menu->fresh()->load('children', 'parent');
    }

    public function delete(Menu $menu): bool|string
    {
        if ($menu->children()->exists()) {
            return 'Menu masih memiliki sub-menu, tidak bisa dihapus';
        }

        $menu->delete();
        return true;
    }
}
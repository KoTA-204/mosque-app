<?php

namespace App\Http\Controllers\ManajemenAkses;

use App\Http\Controllers\Controller;
use App\Helpers\MenuHelper;
use App\Http\Requests\StoreMenuRequest;
use App\Http\Requests\UpdateMenuRequest;
use App\Models\Menu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

class MenuController extends Controller
{
    public function tampilkanDaftarMenu(Request $request)
    {
        $perPage  = $request->input('per_page', 10);
        $search   = $request->input('search');
        $status   = $request->input('status');
        $parentId = $request->input('parent_menu');

        $menus = Menu::with(['children', 'parent', 'permissions'])
            ->when($search, fn($q) =>
                $q->where('menu_name', 'like', "%{$search}%")
                  ->orWhere('route_name', 'like', "%{$search}%")
            )
            ->when($status !== null && $status !== '', fn($q) =>
                $q->where('is_active', $status === 'aktif' ? 1 : 0)
            )
            ->when($parentId !== null && $parentId !== '', function ($q) use ($parentId) {
                if ($parentId === 'none') {
                    $q->whereNull('parent_id');
                } else {
                    $q->where('parent_id', $parentId)
                      ->orWhere('id', $parentId);
                }
            })
            ->orderByRaw('COALESCE(parent_id, id), CASE WHEN parent_id IS NULL THEN 0 ELSE 1 END, sort_order')
            ->paginate($perPage)
            ->withQueryString();

        $parentMenus     = Menu::whereNull('parent_id')->orderBy('menu_name')->get();
        $availableRoutes = $this->getRouteTersedia();
        $availableIcons  = MenuHelper::getAvailableIcons();

        return view('pages.manajemen-akses.menus.index', compact(
            'menus',
            'perPage',
            'parentMenus',
            'availableRoutes',
            'availableIcons'
        ));
    }

    public function simpanMenuBaru(StoreMenuRequest $request)
    {
        try {
            DB::transaction(function () use ($request) {
                Menu::create($request->validated());
            });

            return redirect()->route('dashboard.menus.index')
                ->with('success', 'Menu berhasil dibuat');

        } catch (\Throwable $e) {
            Log::error('Gagal membuat menu', [
                'error' => $e->getMessage(),
                'data'  => $request->validated(),
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal membuat menu. Silakan coba lagi atau hubungi administrator.');
        }
    }

    public function tampilkanDetailMenu(Menu $menu)
    {
        $menu->load(['children', 'parent', 'permissions']);

        return view('pages.manajemen-akses.menus.show', compact('menu'));
    }

    public function perbaruiMenu(UpdateMenuRequest $request, Menu $menu)
    {
        try {
            DB::transaction(function () use ($request, $menu) {
                $menu->update($request->validated());
            });

            return redirect()->route('dashboard.menus.index')
                ->with('success', 'Menu berhasil diupdate');

        } catch (\Throwable $e) {
            Log::error('Gagal mengupdate menu', [
                'error'   => $e->getMessage(),
                'menu_id' => $menu->id,
                'data'    => $request->validated(),
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal memperbarui menu. Silakan coba lagi atau hubungi administrator.');
        }
    }

    public function hapusMenu(Menu $menu)
    {
        if ($menu->children()->exists()) {
            return redirect()->back()
                ->with('error', 'Menu masih memiliki sub-menu, tidak bisa dihapus');
        }

        try {
            DB::transaction(function () use ($menu) {
                $menu->delete();
            });

            return redirect()->route('dashboard.menus.index')
                ->with('success', 'Menu berhasil dihapus');

        } catch (\Throwable $e) {
            Log::error('Gagal menghapus menu', [
                'error'   => $e->getMessage(),
                'menu_id' => $menu->id,
            ]);

            return redirect()->back()
                ->with('error', 'Gagal menghapus menu. Silakan coba lagi atau hubungi administrator.');
        }
    }

    /**
     * Daftar route GET dashboard yang bisa dipilih sebagai tujuan menu.
     */
    private function getRouteTersedia()
    {
        return collect(Route::getRoutes())
            ->filter(fn($route) =>
                $route->getName() &&
                in_array('GET', $route->methods()) &&
                str_starts_with($route->getName(), 'dashboard') &&
                !str_ends_with($route->getName(), '.create') &&
                !str_ends_with($route->getName(), '.edit') &&
                !str_ends_with($route->getName(), '.show')
            )
            ->map(fn($route) => $route->getName())
            ->unique()
            ->sort()
            ->values();
    }
}
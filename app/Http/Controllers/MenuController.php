<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Menu;
use App\Http\Requests\StoreMenuRequest;
use App\Http\Requests\UpdateMenuRequest;
use App\Services\MenuService;

class MenuController extends Controller
{
    public function __construct(
        protected MenuService $menuService
    ) {}
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $menus = $this->menuService->getAll();
        return view('pages.menus.index', compact('menus'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $parentMenus = $this->menuService->getAllFlat();
        return view('pages.menus.create', compact('parentMenus'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreMenuRequest $request)
    {
        $this->menuService->create($request->validated());
        return redirect()->route('menus.index')
            ->with('success', 'Menu berhasil dibuat');
    }

    /**
     * Display the specified resource.
     */
    public function show(Menu $menu)
    {
        $menu = $this->menuService->getById($menu);
        return view('pages.menus.show', compact('menu'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Menu $menu)
    {
        $parentMenus = $this->menuService->getAllFlat();
        return view('pages.menus.edit', compact('menu', 'parentMenus'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateMenuRequest $request, Menu $menu)
    {
        $this->menuService->update($menu, $request->validated());
        return redirect()->route('menus.index')
            ->with('success', 'Menu berhasil diupdate');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Menu $menu)
    {
        $result = $this->menuService->delete($menu);

        if ($result !== true) {
            return redirect()->back()->with('error', $result);
        }

        return redirect()->route('menus.index')
            ->with('success', 'Menu berhasil dihapus');
    }
}

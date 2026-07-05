<?php

namespace App\Http\Controllers\ManajemenAkses;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Role;
use App\Models\Menu;
use App\Models\Permission;
use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Services\ManajemenAkses\RoleService;

class RoleController extends Controller
{
    public function __construct(
        protected RoleService $roleService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function tampilkanDaftarRole(Request $request)
    {
        $search = $request->get('search', '');
        $roles  = $this->roleService->getDataRole($search);

        return view('pages.manajemen-akses.roles.index', compact('roles', 'search'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function tampilkanFormTambahRole()
    {
        $menus       = $this->getMenuBesertaPermission();
        $permissions = Permission::where('is_active', true)
            ->get()
            ->groupBy('module');

        $actions = ['view', 'create', 'update', 'delete', 'manage'];

        return view('pages.manajemen-akses.roles.create', compact(
            'menus',
            'permissions',
            'actions'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function simpanRoleBaru(StoreRoleRequest $request)
    {
        try {
            $this->roleService->buatRole($request->validated());

            return redirect()->route('dashboard.roles.index')
                ->with('success', 'Role berhasil dibuat');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal membuat role. Silakan coba lagi.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function tampilkanDetailRole(Role $role)
    {
        $role = $this->roleService->getDetailRole($role);

        return view('pages.manajemen-akses.roles.show', compact('role'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function tampilkanFormEditRole(Role $role)
    {
        $role        = $this->roleService->getDetailRole($role);
        $menus       = $this->getMenuBesertaPermission();
        $permissions = Permission::where('is_active', true)
            ->get()
            ->groupBy('module');

        $actions = ['view', 'create', 'update', 'delete', 'manage'];

        $assignedIds = $role->permissions
            ->pluck('id')
            ->toArray();

        return view('pages.manajemen-akses.roles.edit', compact(
            'role',
            'menus',
            'permissions',
            'actions',
            'assignedIds'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function perbaruiRole(UpdateRoleRequest $request, Role $role)
    {
        try {
            $this->roleService->perbaruiRole($role, $request->validated());

            return redirect()->route('dashboard.roles.index')
                ->with('success', 'Role berhasil diperbarui');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal memperbarui role. Silakan coba lagi.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function hapusRole(Role $role)
    {
        try {
            $result = $this->roleService->hapusRole($role);

            if ($result !== true) {
                return redirect()->back()->with('error', $result);
            }

            return redirect()->route('dashboard.roles.index')
                ->with('success', 'Role berhasil dihapus');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal menghapus role. Silakan coba lagi.');
        }
    }

    private function getMenuBesertaPermission()
    {
        return Menu::whereNull('parent_id')
            ->where('is_active', true)
            ->with(['children' => function ($query) {
                $query->where('is_active', true)
                    ->orderBy('sort_order');
            }])
            ->orderBy('sort_order')
            ->get();
    }
}
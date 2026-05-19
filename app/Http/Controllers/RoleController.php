<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Role;
use App\Models\Menu;
use App\Models\Permission;
use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Services\RoleService;

class RoleController extends Controller
{
    public function __construct(
        protected RoleService $roleService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->get('search', '');
        $roles  = $this->roleService->getAll($search);
        return view('pages.roles.index', compact('roles', 'search'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $menus       = $this->getMenusWithPermissions();
        $permissions = Permission::where('is_active', true)
            ->get()
            ->groupBy('module');

        $actions = ['view', 'create', 'update', 'delete', 'manage'];

        return view('pages.roles.create', compact(
            'menus',
            'permissions',
            'actions'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRoleRequest $request)
    {
        $this->roleService->create($request->validated());
        return redirect()->route('dashboard.roles.index')
            ->with('success', 'Role berhasil dibuat');
    }

    /**
     * Display the specified resource.
     */
    public function show(Role $role)
    {
        $role = $this->roleService->getById($role);
        return view('pages.roles.show', compact('role'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Role $role)
    {
        $role        = $this->roleService->getById($role);
        $menus       = $this->getMenusWithPermissions();
        $permissions = Permission::where('is_active', true)
            ->get()
            ->groupBy('module');

        $actions = ['view', 'create', 'update', 'delete', 'manage'];

        $assignedIds = $role->permissions
            ->pluck('id')
            ->toArray();

        return view('pages.roles.edit', compact(
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
    public function update(UpdateRoleRequest $request, Role $role)
    {
        $this->roleService->update($role, $request->validated());
        return redirect()->route('dashboard.roles.index')
            ->with('success', 'Role berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role)
    {
        $result = $this->roleService->delete($role);

        if ($result !== true) {
            return redirect()->back()->with('error', $result);
        }

        return redirect()->route('dashboard.roles.index')
            ->with('success', 'Role berhasil dihapus');
    }

    private function getMenusWithPermissions()
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

<?php

namespace App\Http\Controllers\ManajemenAkses;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Permission;
use App\Http\Requests\StorePermissionRequest;
use App\Http\Requests\UpdatePermissionRequest;
use App\Services\ManajemenAkses\PermissionService;
use Illuminate\Support\Facades\Log;

class PermissionController extends Controller
{
    public function __construct(
        protected PermissionService $permissionService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search  = $request->get('search', '');
        $module  = $request->get('module', '');
        $action  = $request->get('action', '');
        $perPage = (int) $request->get('per_page', 10);

        $permissions = $this->permissionService->getAll($search, $module, $action, $perPage);
        $modules     = $this->permissionService->getDistinctModules();

        return view('pages.manajemen-akses.permissions.index', compact('permissions', 'search', 'module', 'action', 'perPage', 'modules'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('pages.manajemen-akses.permissions.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePermissionRequest $request)
    {
        try {
            $this->permissionService->create($request->validated());

            return redirect()->route('dashboard.permissions.index')
                ->with('success', 'Permission berhasil dibuat.');
        } catch (\Throwable $e) {
            Log::error('PermissionController@store: ' . $e->getMessage());

            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat membuat permission. Silakan coba lagi.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Permission $permission)
    {
        try {
            $permission = $this->permissionService->getById($permission);

            return view('pages.manajemen-akses.permissions.show', compact('permission'));
        } catch (\Throwable $e) {
            Log::error('PermissionController@show: ' . $e->getMessage());

            return redirect()->route('dashboard.permissions.index')
                ->with('error', 'Permission tidak ditemukan.');
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Permission $permission)
    {
        return view('pages.manajemen-akses.permissions.edit', compact('permission'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePermissionRequest $request, Permission $permission)
    {
        try {
            $this->permissionService->update($permission, $request->validated());

            return redirect()->route('dashboard.permissions.index')
                ->with('success', 'Permission berhasil diperbarui.');
        } catch (\Throwable $e) {
            Log::error('PermissionController@update: ' . $e->getMessage());

            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat memperbarui permission. Silakan coba lagi.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Permission $permission)
    {
        try {
            $result = $this->permissionService->delete($permission);

            if ($result !== true) {
                return redirect()->back()->with('error', $result);
            }

            return redirect()->route('dashboard.permissions.index')
                ->with('success', 'Permission berhasil dihapus.');
        } catch (\Throwable $e) {
            Log::error('PermissionController@destroy: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat menghapus permission. Silakan coba lagi.');
        }
    }
}
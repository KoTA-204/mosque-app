<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(
        protected UserService $userService
    ) {}

    public function index(Request $request)
    {
        $search = $request->get('search', '');
        $role   = $request->get('role', '');
        $users  = $this->userService->getAll($search, $role);
        $roles  = $this->userService->getAllRoles();

        return view('pages.users.index', compact('users', 'roles', 'search', 'role'));
    }

    public function create()
    {
        $roles = $this->userService->getAllRoles();
        return view('pages.users.create', compact('roles'));
    }

    public function store(StoreUserRequest $request)
    {
        $this->userService->create($request->validated());
        return redirect()->route('dashboard.users.index')
            ->with('success', 'User berhasil ditambahkan');
    }

    public function edit(User $user)
    {
        $user  = $this->userService->getById($user);
        $roles = $this->userService->getAllRoles();
        return view('pages.users.edit', compact('user', 'roles'));
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $this->userService->update($user, $request->validated());
        return redirect()->route('dashboard.users.index')
            ->with('success', 'User berhasil diupdate');
    }

    public function destroy(User $user)
    {
        $result = $this->userService->delete($user);

        if ($result !== true) {
            return redirect()->back()->with('error', $result);
        }

        return redirect()->route('dashboard.users.index')
            ->with('success', 'User berhasil dihapus');
    }
}
<?php

namespace App\Services;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Pagination\LengthAwarePaginator;

class UserService
{
    public function getAll(string $search = '', ?string $role = '', int $perPage = 10): LengthAwarePaginator
    {
        return User::with('roles')
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($role, function ($query) use ($role) {
                $query->whereHas('roles', function ($q) use ($role) {
                    $q->where('role_id', $role);
                });
            })
            ->paginate($perPage);
    }

    public function getAllRoles()
    {
        return Role::where('is_active', true)->get();
    }

    public function getById(User $user): User
    {
        return $user->load('roles');
    }

    public function create(array $data): User
    {
        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
            'status'   => $data['status'],
        ]);

        $user->roles()->attach($data['role_id']);

        return $user;
    }

    public function update(User $user, array $data): User
    {
        $user->update([
            'name'   => $data['name'],
            'email'  => $data['email'],
            'status' => $data['status'],
        ]);

        $user->roles()->sync([$data['role_id']]);

        return $user->fresh()->load('roles');
    }

    public function delete(User $user): bool|string
    {
        if ($user->id === auth()->id()) {
            return 'Tidak bisa menghapus akun sendiri';
        }

        $user->delete();
        return true;
    }
}
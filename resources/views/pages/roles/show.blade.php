@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-screen-2xl p-4 md:p-6 2xl:p-10">

    <div class="mb-6 flex items-center justify-between">
        <h2 class="text-2xl font-bold text-dark dark:text-white">Detail Role</h2>
        <a href="{{ route('dashboard.roles.index') }}"
           class="rounded-lg border border-stroke px-4 py-2 text-sm text-black hover:bg-gray-100 dark:text-white">
            ← Kembali
        </a>
    </div>

    <div class="rounded-xl border border-stroke bg-white p-6 shadow-default dark:border-strokedark dark:bg-boxdark">

        <div class="mb-4">
            <p class="text-xs text-body dark:text-bodydark">Nama Role</p>
            <p class="text-sm font-medium text-black dark:text-white">{{ $role->role_name }}</p>
        </div>

        <div class="mb-4">
            <p class="text-xs text-body dark:text-bodydark">Slug</p>
            <p class="text-sm text-black dark:text-white">{{ $role->slug }}</p>
        </div>

        <div class="mb-4">
            <p class="text-xs text-body dark:text-bodydark">Deskripsi</p>
            <p class="text-sm text-black dark:text-white">{{ $role->description ?? '-' }}</p>
        </div>

        <div class="mb-6">
            <p class="text-xs text-body dark:text-bodydark">Status</p>
            @if($role->is_active)
                <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-medium text-green-800">Aktif</span>
            @else
                <span class="rounded-full bg-red-100 px-3 py-1 text-xs font-medium text-red-800">Nonaktif</span>
            @endif
        </div>

        {{-- Permissions --}}
        <div>
            <p class="mb-2 text-xs text-body dark:text-bodydark">Permissions</p>
            <div class="flex flex-wrap gap-2">
                @forelse($role->permissions as $permission)
                    <span class="rounded-full bg-blue-100 px-3 py-1 text-xs font-medium text-blue-800">
                        {{ $permission->permission_name }}
                    </span>
                @empty
                    <p class="text-sm text-body dark:text-bodydark">Belum ada permission</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
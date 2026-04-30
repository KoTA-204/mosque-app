@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-screen-2xl p-4 md:p-6 2xl:p-10">

    <div class="mb-6 flex items-center justify-between">
        <h2 class="text-2xl font-bold text-dark dark:text-white">Detail Permission</h2>
        <a href="{{ route('permissions.index') }}"
           class="rounded-lg border border-stroke px-4 py-2 text-sm text-black hover:bg-gray-100 dark:text-white">
            ← Kembali
        </a>
    </div>

    <div class="rounded-xl border border-stroke bg-white p-6 shadow-default dark:border-strokedark dark:bg-boxdark">

        {{-- Permission Code --}}
        <div class="mb-4">
            <p class="text-xs text-body dark:text-bodydark">Kode Permission</p>
            <p class="font-mono text-sm font-medium text-black dark:text-white">
                {{ $permission->permission_code }}
            </p>
        </div>

        {{-- Permission Name --}}
        <div class="mb-4">
            <p class="text-xs text-body dark:text-bodydark">Nama Permission</p>
            <p class="text-sm font-medium text-black dark:text-white">
                {{ $permission->permission_name }}
            </p>
        </div>

        {{-- Module --}}
        <div class="mb-4">
            <p class="text-xs text-body dark:text-bodydark">Module</p>
            <span class="rounded-full bg-purple-100 px-3 py-1 text-xs font-medium text-purple-800">
                {{ $permission->module }}
            </span>
        </div>

        {{-- Action --}}
        <div class="mb-4">
            <p class="text-xs text-body dark:text-bodydark">Action</p>
            @php
                $actionColors = [
                    'view'   => 'bg-blue-100 text-blue-800',
                    'create' => 'bg-green-100 text-green-800',
                    'update' => 'bg-yellow-100 text-yellow-800',
                    'delete' => 'bg-red-100 text-red-800',
                ];
                $color = $actionColors[$permission->action] ?? 'bg-gray-100 text-gray-800';
            @endphp
            <span class="rounded-full px-3 py-1 text-xs font-medium {{ $color }}">
                {{ $permission->action }}
            </span>
        </div>

        {{-- Description --}}
        <div class="mb-4">
            <p class="text-xs text-body dark:text-bodydark">Deskripsi</p>
            <p class="text-sm text-black dark:text-white">
                {{ $permission->description ?? '-' }}
            </p>
        </div>

        {{-- Status --}}
        <div class="mb-6">
            <p class="text-xs text-body dark:text-bodydark">Status</p>
            @if($permission->is_active)
                <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-medium text-green-800">Aktif</span>
            @else
                <span class="rounded-full bg-red-100 px-3 py-1 text-xs font-medium text-red-800">Nonaktif</span>
            @endif
        </div>

        {{-- Roles yang pakai permission ini --}}
        <div>
            <p class="mb-2 text-xs text-body dark:text-bodydark">Dipakai oleh Role</p>
            <div class="flex flex-wrap gap-2">
                @forelse($permission->roles as $role)
                    <span class="rounded-full bg-blue-100 px-3 py-1 text-xs font-medium text-blue-800">
                        {{ $role->role_name }}
                    </span>
                @empty
                    <p class="text-sm text-body dark:text-bodydark">Belum dipakai role manapun</p>
                @endforelse
            </div>
        </div>

    </div>
</div>
@endsection
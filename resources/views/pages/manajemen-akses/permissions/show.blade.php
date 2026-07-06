@extends('layouts.app')

@section('title', 'Detail Hak Akses')

@section('content')
<div class="p-6 space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 px-6 py-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('dashboard.permissions.index') }}"
               class="p-1.5 text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <h1 class="text-lg font-semibold text-gray-900 dark:text-white">Detail Hak Akses</h1>
        </div>
        <a href="{{ route('dashboard.permissions.edit', $permission) }}"
           class="inline-flex items-center gap-2 border border-green-600 text-green-700 dark:text-green-400 dark:border-green-600 text-sm font-medium px-4 py-2 rounded-lg hover:bg-green-50 dark:hover:bg-green-900/20 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
            Edit
        </a>
    </div>

    <div class="space-y-4">

        {{-- Main Info Card --}}
        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-6 space-y-5">
            <h2 class="text-base font-semibold text-gray-900 dark:text-white">Informasi Hak Akses</h2>

            {{-- Kode --}}
            <div>
                <p class="text-xs font-medium text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-1">Kode Permission</p>
                <p class="font-mono text-sm text-gray-800 dark:text-gray-200">{{ $permission->permission_code }}</p>
            </div>

            {{-- Nama --}}
            <div>
                <p class="text-xs font-medium text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-1">Nama Permission</p>
                <p class="text-sm text-gray-800 dark:text-gray-200">{{ $permission->permission_name }}</p>
            </div>

            {{-- Module + Action side by side --}}
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-xs font-medium text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-1.5">Module</p>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-50 dark:bg-purple-900/20 text-purple-600 dark:text-purple-400">
                        {{ $permission->module }}
                    </span>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-1.5">Action</p>
                    @php
                        $actionStyles = [
                            'view'   => 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400',
                            'create' => 'bg-green-50 dark:bg-green-900/20 text-green-600 dark:text-green-400',
                            'update' => 'bg-yellow-50 dark:bg-yellow-900/20 text-yellow-600 dark:text-yellow-400',
                            'delete' => 'bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400',
                        ];
                        $style = $actionStyles[$permission->action] ?? 'bg-gray-50 dark:bg-gray-800 text-gray-600 dark:text-gray-400';
                    @endphp
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $style }}">
                        {{ ucfirst($permission->action) }}
                    </span>
                </div>
            </div>

            {{-- Status --}}
            <div>
                <p class="text-xs font-medium text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-1.5">Status</p>
                <span class="text-sm font-medium {{ $permission->is_active ? 'text-green-600 dark:text-green-400' : 'text-red-500 dark:text-red-400' }}">
                    {{ $permission->is_active ? 'Aktif' : 'Nonaktif' }}
                </span>
            </div>

            {{-- Deskripsi --}}
            <div>
                <p class="text-xs font-medium text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-1">Deskripsi</p>
                <p class="text-sm text-gray-600 dark:text-gray-400">{{ $permission->description ?? '—' }}</p>
            </div>
        </div>

        {{-- Roles Card --}}
        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-6">
            <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-4">Dipakai oleh Peran</h2>
            <div class="flex flex-wrap gap-2">
                @forelse($permission->roles as $role)
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400">
                        {{ $role->role_name }}
                    </span>
                @empty
                    <p class="text-sm text-gray-400 dark:text-gray-600">Belum dipakai role manapun.</p>
                @endforelse
            </div>
        </div>

    </div>
</div>
@endsection
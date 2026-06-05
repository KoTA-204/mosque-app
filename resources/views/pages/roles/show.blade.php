@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-screen-2xl p-4 md:p-6 2xl:p-10">

    {{-- Header --}}
    <div class="flex items-center justify-between bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 px-6 py-4 mb-6">
        <div class="flex items-center gap-3">
            <a href="{{ route('dashboard.roles.index') }}"
               class="p-1.5 text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <h1 class="text-lg font-semibold text-gray-900 dark:text-white">Detail Role</h1>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">

        {{-- Left: Info Role --}}
        <div class="xl:col-span-1">
            <div class="rounded-xl border border-stroke bg-white shadow-default dark:border-strokedark dark:bg-boxdark">

                {{-- Card Header --}}
                <div class="border-b border-stroke px-6 py-4 dark:border-strokedark">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-brand-500/10 text-brand-500">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
                                 stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                                <circle cx="9" cy="7" r="4"/>
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-base font-semibold text-black dark:text-white">
                                {{ $role->role_name }}
                            </h3>
                            <p class="text-xs text-body dark:text-bodydark">{{ $role->slug }}</p>
                        </div>
                    </div>
                </div>

                {{-- Card Body --}}
                <div class="p-6">
                    <dl class="space-y-4">
                        <div>
                            <dt class="mb-1 text-xs font-medium uppercase tracking-wide text-body dark:text-bodydark">
                                Deskripsi
                            </dt>
                            <dd class="text-sm text-black dark:text-white">
                                {{ $role->description ?? '—' }}
                            </dd>
                        </div>

                        <div>
                            <dt class="mb-1 text-xs font-medium uppercase tracking-wide text-body dark:text-bodydark">
                                Status
                            </dt>
                            <dd>
                                @if($role->is_active)
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-green-100 px-3 py-1 text-xs font-medium text-green-700 dark:bg-green-900/30 dark:text-green-400">
                                        <span class="h-1.5 w-1.5 rounded-full bg-green-500"></span>
                                        Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-red-100 px-3 py-1 text-xs font-medium text-red-700 dark:bg-red-900/30 dark:text-red-400">
                                        <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>
                                        Nonaktif
                                    </span>
                                @endif
                            </dd>
                        </div>

                        <div>
                            <dt class="mb-1 text-xs font-medium uppercase tracking-wide text-body dark:text-bodydark">
                                Jumlah User
                            </dt>
                            <dd class="flex items-center gap-2 text-sm font-medium text-black dark:text-white">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                     class="text-body dark:text-bodydark">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                    <circle cx="12" cy="7" r="4"/>
                                </svg>
                                {{ $role->users->count() ?? 0 }} User
                            </dd>
                        </div>

                        <div>
                            <dt class="mb-1 text-xs font-medium uppercase tracking-wide text-body dark:text-bodydark">
                                Jumlah Permission
                            </dt>
                            <dd class="flex items-center gap-2 text-sm font-medium text-black dark:text-white">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                     class="text-body dark:text-bodydark">
                                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                                    <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                                </svg>
                                {{ $role->permissions->count() }} Permission
                            </dd>
                        </div>
                    </dl>
                </div>

                {{-- Card Footer --}}
                <div class="flex items-center gap-2 border-t border-stroke px-6 py-4 dark:border-strokedark">
                    <a href="{{ route('dashboard.roles.edit', $role) }}"
                       class="flex flex-1 items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600 transition-colors duration-150">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                             stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                        </svg>
                        Edit Role
                    </a>
                </div>
            </div>
        </div>

        {{-- Right: Permissions --}}
        <div class="xl:col-span-2">
            <div class="rounded-xl border border-stroke bg-white shadow-default dark:border-strokedark dark:bg-boxdark">

                {{-- Card Header --}}
                <div class="border-b border-stroke px-6 py-4 dark:border-strokedark">
                    <div class="flex items-center justify-between">
                        <h3 class="text-base font-semibold text-black dark:text-white">Permissions</h3>
                        <span class="rounded-full border border-stroke px-3 py-1 text-xs text-body dark:border-strokedark dark:text-bodydark">
                            {{ $role->permissions->count() }} total
                        </span>
                    </div>
                </div>

                {{-- Card Body --}}
                <div class="p-6">
                    @if($role->permissions->isEmpty())
                        <div class="flex flex-col items-center justify-center py-10 text-center">
                            <div class="mb-3 flex h-14 w-14 items-center justify-center rounded-full bg-gray-100 dark:bg-meta-4">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                     stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"
                                     class="text-gray-400">
                                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                                    <path d="M7 11V7a5 5 0 0 1 9.9-1"/>
                                </svg>
                            </div>
                            <p class="text-sm font-medium text-black dark:text-white">Belum ada permission</p>
                            <p class="mt-1 text-xs text-body dark:text-bodydark">
                                Edit role untuk menambahkan permission
                            </p>
                        </div>
                    @else
                        {{-- Group by module --}}
                        @php
                            $grouped = $role->permissions->groupBy('module');
                        @endphp

                        <div class="space-y-5">
                            @foreach($grouped as $module => $perms)
                            <div>
                                <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-body dark:text-bodydark">
                                    {{ $module }}
                                </p>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($perms as $permission)
                                        @php
                                            $colorMap = [
                                                'view'   => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
                                                'create' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
                                                'update' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
                                                'delete' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
                                                'manage' => 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400',
                                            ];
                                            $color = $colorMap[$permission->action ?? ''] ?? 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300';
                                        @endphp
                                        <span class="rounded-full px-3 py-1 text-xs font-medium {{ $color }}">
                                            {{ $permission->permission_name }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
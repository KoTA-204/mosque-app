@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-screen-2xl p-4 md:p-6 2xl:p-10">

    <div class="mb-6 flex items-center justify-between">
        <h2 class="text-2xl font-bold text-dark dark:text-white">Manajemen Permission</h2>
        <a href="{{ route('dashboard.permissions.create') }}"
           class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-medium text-white hover:bg-opacity-90">
            + Tambah Permission
        </a>
    </div>

    @if(session('success'))
    <div class="mb-4 rounded-lg bg-green-100 px-4 py-3 text-green-800">
        {{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div class="mb-4 rounded-lg bg-red-100 px-4 py-3 text-red-800">
        {{ session('error') }}
    </div>
    @endif

    <div class="rounded-xl border border-stroke bg-white shadow-default dark:border-strokedark dark:bg-boxdark">
        <div class="overflow-x-auto">
            <table class="w-full table-auto">
                <thead>
                    <tr class="bg-gray-2 text-left dark:bg-meta-4">
                        <th class="px-4 py-4 font-medium text-black dark:text-white">#</th>
                        <th class="px-4 py-4 font-medium text-black dark:text-white">Kode</th>
                        <th class="px-4 py-4 font-medium text-black dark:text-white">Nama</th>
                        <th class="px-4 py-4 font-medium text-black dark:text-white">Module</th>
                        <th class="px-4 py-4 font-medium text-black dark:text-white">Action</th>
                        <th class="px-4 py-4 font-medium text-black dark:text-white">Status</th>
                        <th class="px-4 py-4 font-medium text-black dark:text-white">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($permissions as $permission)
                    <tr class="border-t border-stroke dark:border-strokedark">
                        <td class="px-4 py-4 text-sm">{{ $loop->iteration }}</td>
                        <td class="px-4 py-4 text-sm font-mono text-black dark:text-white">
                            {{ $permission->permission_code }}
                        </td>
                        <td class="px-4 py-4 text-sm text-black dark:text-white">
                            {{ $permission->permission_name }}
                        </td>
                        <td class="px-4 py-4 text-sm">
                            <span class="rounded-full bg-purple-100 px-3 py-1 text-xs font-medium text-purple-800">
                                {{ $permission->module }}
                            </span>
                        </td>
                        <td class="px-4 py-4 text-sm">
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
                        </td>
                        <td class="px-4 py-4 text-sm">
                            @if($permission->is_active)
                                <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-medium text-green-800">Aktif</span>
                            @else
                                <span class="rounded-full bg-red-100 px-3 py-1 text-xs font-medium text-red-800">Nonaktif</span>
                            @endif
                        </td>
                        <td class="px-4 py-4 text-sm">
                            <div class="flex items-center gap-2">
                                <a href="{{ route('dashboard.permissions.show', $permission) }}"
                                   class="rounded bg-blue-100 px-3 py-1 text-xs text-blue-700 hover:bg-blue-200">
                                    Detail
                                </a>
                                <a href="{{ route('dashboard.permissions.edit', $permission) }}"
                                   class="rounded bg-yellow-100 px-3 py-1 text-xs text-yellow-700 hover:bg-yellow-200">
                                    Edit
                                </a>
                                <form action="{{ route('dashboard.permissions.destroy', $permission) }}" method="POST"
                                      onsubmit="return confirm('Yakin hapus permission ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="rounded bg-red-100 px-3 py-1 text-xs text-red-700 hover:bg-red-200">
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-sm text-body dark:text-bodydark">
                            Belum ada permission
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
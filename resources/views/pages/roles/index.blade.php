@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-screen-2xl p-4 md:p-6 2xl:p-10">

    {{-- Header --}}
    <div class="mb-6 flex items-center justify-between">
        <h2 class="text-2xl font-bold text-dark dark:text-white">
            Manajemen Role
        </h2>
        <a href="{{ route('dashboard.roles.create') }}"
           class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-medium text-white hover:bg-opacity-90">
            + Tambah Role
        </a>
    </div>

    {{-- Alert Success --}}
    @if(session('success'))
    <div class="mb-4 rounded-lg bg-green-100 px-4 py-3 text-green-800">
        {{ session('success') }}
    </div>
    @endif

    {{-- Alert Error --}}
    @if(session('error'))
    <div class="mb-4 rounded-lg bg-red-100 px-4 py-3 text-red-800">
        {{ session('error') }}
    </div>
    @endif

    {{-- Table --}}
    <div class="rounded-xl border border-stroke bg-white shadow-default dark:border-strokedark dark:bg-boxdark">
        <div class="overflow-x-auto">
            <table class="w-full table-auto">
                <thead>
                    <tr class="bg-gray-2 text-left dark:bg-meta-4">
                        <th class="px-4 py-4 font-medium text-black dark:text-white">#</th>
                        <th class="px-4 py-4 font-medium text-black dark:text-white">Nama Role</th>
                        <th class="px-4 py-4 font-medium text-black dark:text-white">Slug</th>
                        <th class="px-4 py-4 font-medium text-black dark:text-white">Deskripsi</th>
                        <th class="px-4 py-4 font-medium text-black dark:text-white">Status</th>
                        <th class="px-4 py-4 font-medium text-black dark:text-white">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($roles as $role)
                    <tr class="border-t border-stroke dark:border-strokedark">
                        <td class="px-4 py-4 text-sm text-black dark:text-white">{{ $loop->iteration }}</td>
                        <td class="px-4 py-4 text-sm font-medium text-black dark:text-white">{{ $role->role_name }}</td>
                        <td class="px-4 py-4 text-sm text-body dark:text-bodydark">{{ $role->slug }}</td>
                        <td class="px-4 py-4 text-sm text-body dark:text-bodydark">{{ $role->description ?? '-' }}</td>
                        <td class="px-4 py-4 text-sm">
                            @if($role->is_active)
                                <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-medium text-green-800">Aktif</span>
                            @else
                                <span class="rounded-full bg-red-100 px-3 py-1 text-xs font-medium text-red-800">Nonaktif</span>
                            @endif
                        </td>
                        <td class="px-4 py-4 text-sm">
                            <div class="flex items-center gap-2">
                                <a href="{{ route('dashboard.roles.show', $role) }}"
                                   class="rounded bg-blue-100 px-3 py-1 text-xs text-blue-700 hover:bg-blue-200">
                                    Detail
                                </a>
                                <a href="{{ route('dashboard.roles.edit', $role) }}"
                                   class="rounded bg-yellow-100 px-3 py-1 text-xs text-yellow-700 hover:bg-yellow-200">
                                    Edit
                                </a>
                                <form action="{{ route('dashboard.roles.destroy', $role) }}" method="POST"
                                      onsubmit="return confirm('Yakin hapus role ini?')">
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
                        <td colspan="6" class="px-4 py-8 text-center text-sm text-body dark:text-bodydark">
                            Belum ada role
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
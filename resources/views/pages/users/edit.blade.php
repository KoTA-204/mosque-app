@extends('layouts.app')

@section('content')
    <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <!-- Header -->
        <div class="border-b border-gray-200 px-6 py-5 dark:border-gray-800">
            <h4 class="text-lg font-semibold text-gray-900 dark:text-white">Edit User</h4>
        </div>

        <div class="p-6">
            <div class="mx-auto max-w-2xl">
                <!-- Icon -->
                <div class="mb-8 flex justify-center">
                    <div
                        class="flex h-20 w-20 items-center justify-center rounded-full bg-[#c8d300] text-white dark:bg-[#c8d300]">
                        <svg class="h-10 w-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                    </div>
                </div>

                <form action="{{ route('dashboard.users.update', $user) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="space-y-5">
                        <!-- Nama Lengkap -->
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Nama Lengkap<span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="name" value="{{ old('name', $user->name) }}"
                                placeholder="Masukkan nama lengkap"
                                class="h-11 w-full rounded-full border border-green-500 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:border-green-500 focus:outline-hidden focus:ring-3 focus:ring-green-500/10 dark:border-green-500 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 @error('name') border-red-500 @enderror" />
                            @error('name')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Username -->
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Username
                            </label>
                            <input type="text" value="{{ explode('@', $user->email)[0] }}" readonly
                                class="h-11 w-full rounded-full border border-green-500 bg-gray-50 px-4 py-2.5 text-sm text-gray-800 dark:border-green-500 dark:bg-gray-800 dark:text-white/90" />
                        </div>

                        <!-- Email -->
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Email<span class="text-red-500">*</span>
                            </label>
                            <input type="email" name="email" value="{{ old('email', $user->email) }}"
                                placeholder="Masukkan email"
                                class="h-11 w-full rounded-full border border-green-500 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:border-green-500 focus:outline-hidden focus:ring-3 focus:ring-green-500/10 dark:border-green-500 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 @error('email') border-red-500 @enderror" />
                            @error('email')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Role & Status -->
                        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                            <!-- Role -->
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                    Role<span class="text-red-500">*</span>
                                </label>
                                <select name="role_id"
                                    class="h-11 w-full rounded-full border border-green-500 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:border-green-500 focus:outline-hidden focus:ring-3 focus:ring-green-500/10 dark:border-green-500 dark:bg-gray-900 dark:text-white/90 @error('role_id') border-red-500 @enderror">
                                    @foreach ($roles as $role)
                                        <option value="{{ $role->id }}"
                                            {{ $user->roles->contains($role->id) ? 'selected' : '' }}>
                                            {{ $role->role_name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('role_id')
                                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Status -->
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                    Status<span class="text-red-500">*</span>
                                </label>
                                <select name="status"
                                    class="h-11 w-full rounded-full border border-green-500 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:border-green-500 focus:outline-hidden focus:ring-3 focus:ring-green-500/10 dark:border-green-500 dark:bg-gray-900 dark:text-white/90 @error('status') border-red-500 @enderror">
                                    <option value="active" {{ $user->status == 'active' ? 'selected' : '' }}>Aktif</option>
                                    <option value="inactive" {{ $user->status == 'inactive' ? 'selected' : '' }}>Tidak Aktif
                                    </option>
                                </select>
                                @error('status')
                                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Button -->
                        <div class="pt-3 text-center">
                            <button type="submit"
                                class="inline-flex items-center justify-center rounded-full bg-[#c8d300] px-8 py-3 text-sm font-medium text-gray-900 transition hover:bg-[#b3bd00]">
                                Simpan Perubahan
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@extends('layouts.app')

@section('content')
    <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <!-- Header -->
        <div class="border-b border-gray-200 px-6 py-5 dark:border-gray-800">
            <h4 class="text-lg font-semibold text-gray-900 dark:text-white">Tambah User</h4>
        </div>

        <div class="p-6">
            <div class="mx-auto max-w-2xl">
                <!-- Icon -->
                <div class="mb-8 flex justify-center">
                    <div
                        class="flex h-20 w-20 items-center justify-center rounded-full bg-[#c8d300] text-white dark:bg-[#c8d300]">
                        <svg class="h-10 w-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                        </svg>
                    </div>
                </div>

                <form action="{{ route('users.store') }}" method="POST">
                    @csrf
                    <div class="space-y-5">
                        <!-- Nama Lengkap -->
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Nama Lengkap<span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="name" value="{{ old('name') }}" placeholder="Masukkan nama lengkap"
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
                            <input type="text" name="username" value="{{ old('username') }}"
                                placeholder="Masukkan username"
                                class="h-11 w-full rounded-full border border-green-500 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:border-green-500 focus:outline-hidden focus:ring-3 focus:ring-green-500/10 dark:border-green-500 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" />
                        </div>

                        <!-- Email -->
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Email<span class="text-red-500">*</span>
                            </label>
                            <input type="email" name="email" value="{{ old('email') }}"
                                placeholder="Masukkan email"
                                class="h-11 w-full rounded-full border border-green-500 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:border-green-500 focus:outline-hidden focus:ring-3 focus:ring-green-500/10 dark:border-green-500 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 @error('email') border-red-500 @enderror" />
                            @error('email')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Password -->
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Password<span class="text-red-500">*</span>
                            </label>
                            <input type="password" name="password" placeholder="Masukkan password"
                                class="h-11 w-full rounded-full border border-green-500 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:border-green-500 focus:outline-hidden focus:ring-3 focus:ring-green-500/10 dark:border-green-500 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 @error('password') border-red-500 @enderror" />
                            @error('password')
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
                                    <option value="">Pilih Role</option>
                                    @foreach ($roles as $role)
                                        <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>
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
                                    <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Aktif</option>
                                    <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Tidak Aktif
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
                                Simpan
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
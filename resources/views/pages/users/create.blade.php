<x-modal id="createUserModal" title="Tambah User">

    <form id="createUserForm">
        @csrf

        <div class="px-6 py-5 max-h-[70vh] overflow-y-auto space-y-4">

            <div>
                <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1.5">Nama Lengkap <span class="text-red-500">*</span></label>
                <input type="text" name="name" placeholder="Masukkan nama lengkap"
                    class="w-full border border-gray-200 dark:border-gray-700 rounded-xl px-3.5 py-2.5 text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 outline-none focus:border-green-400 transition-colors">
                <p id="err-name" class="text-xs text-red-500 mt-1"></p>
            </div>

            <div>
                <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1.5">Email <span class="text-red-500">*</span></label>
                <input type="email" name="email" placeholder="Masukkan email"
                    class="w-full border border-gray-200 dark:border-gray-700 rounded-xl px-3.5 py-2.5 text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 outline-none focus:border-green-400 transition-colors">
                <p id="err-email" class="text-xs text-red-500 mt-1"></p>
            </div>

            <div>
                <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1.5">Password <span class="text-red-500">*</span></label>
                <input type="password" name="password" placeholder="Masukkan password"
                    class="w-full border border-gray-200 dark:border-gray-700 rounded-xl px-3.5 py-2.5 text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 outline-none focus:border-green-400 transition-colors">
                <p id="err-password" class="text-xs text-red-500 mt-1"></p>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1.5">Role <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <select name="role_id"
                            class="w-full appearance-none border border-gray-200 dark:border-gray-700 rounded-xl px-3.5 py-2.5 text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-white outline-none focus:border-green-400 transition-colors">
                            <option value="">Pilih Role</option>
                            @foreach($roles as $role)
                            <option value="{{ $role->id }}">{{ $role->role_name }}</option>
                            @endforeach
                        </select>
                        <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                    <p id="err-role_id" class="text-xs text-red-500 mt-1"></p>
                </div>
                <div>
                    <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1.5">Status <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <select name="status"
                            class="w-full appearance-none border border-gray-200 dark:border-gray-700 rounded-xl px-3.5 py-2.5 text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-white outline-none focus:border-green-400 transition-colors">
                            <option value="active">Aktif</option>
                            <option value="inactive">Tidak Aktif</option>
                        </select>
                        <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                </div>
            </div>

        </div>

        {{-- Footer --}}
        <div class="flex justify-end gap-3 px-6 py-4 border-t border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/50">
            <button type="button" onclick="closeModal('createUserModal')"
                class="px-5 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                Batal
            </button>
            <button type="button"
                onclick="submitUserForm('createUserForm', 'POST', '{{ route('dashboard.users.store') }}')"
                class="px-5 py-2 text-sm font-medium text-white bg-green-600 rounded-xl hover:bg-green-700 transition-colors">
                Simpan
            </button>
        </div>
    </form>

</x-modal>
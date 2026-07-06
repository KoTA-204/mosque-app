<x-modal id="createUserModal" title="Tambah Pengguna">

    <form id="createUserForm">
        @csrf

        <div class="px-6 py-5 space-y-4">

            <div>
                <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1.5">
                    Nama Lengkap <span class="text-red-500">*</span>
                </label>
                <input type="text" name="name" placeholder="Masukkan nama lengkap"
                    class="w-full border border-gray-200 dark:border-gray-700 rounded-xl px-3.5 py-2.5 text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 outline-none focus:border-green-400 transition-colors">
                <p id="err-name" class="text-xs text-red-500 mt-1"></p>
            </div>

            <div>
                <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1.5">
                    Email <span class="text-red-500">*</span>
                </label>
                <input type="email" name="email" placeholder="Masukkan email"
                    class="w-full border border-gray-200 dark:border-gray-700 rounded-xl px-3.5 py-2.5 text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 outline-none focus:border-green-400 transition-colors">
                <p id="err-email" class="text-xs text-red-500 mt-1"></p>
                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                    Kredensial (email &amp; password) dapat dikirim ke alamat ini lewat ikon email setelah user dibuat.
                </p>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1.5">
                        Password Awal <span class="text-red-500">*</span>
                    </label>
                    <input type="password" name="password" placeholder="Minimal 8 karakter"
                        class="w-full border border-gray-200 dark:border-gray-700 rounded-xl px-3.5 py-2.5 text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 outline-none focus:border-green-400 transition-colors">
                    <p id="err-password" class="text-xs text-red-500 mt-1"></p>
                </div>
                <div>
                    <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1.5">
                        Konfirmasi Password <span class="text-red-500">*</span>
                    </label>
                    <input type="password" name="password_confirmation" placeholder="Ulangi password"
                        class="w-full border border-gray-200 dark:border-gray-700 rounded-xl px-3.5 py-2.5 text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 outline-none focus:border-green-400 transition-colors">
                </div>
            </div>
            <p class="text-xs text-gray-400 dark:text-gray-500 -mt-2">
                Password ini disimpan aman &amp; tetap dapat Anda lihat lagi di menu Edit. Kirim ke user lewat ikon email setelah verifikasi permission.
            </p>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1.5">
                        Role <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <select name="role_id"
                            class="w-full appearance-none border border-gray-200 dark:border-gray-700 rounded-xl px-3.5 py-2.5 text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-white outline-none focus:border-green-400 transition-colors">
                            <option value="">Pilih Role</option>
                            @foreach($roles as $role)
                            <option value="{{ $role->id }}">{{ $role->role_name }}</option>
                            @endforeach
                        </select>
                        <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
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
                        <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
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
            <button type="button" id="btnSimpanUser"
                onclick="submitCreateUser()"
                class="inline-flex items-center gap-2 px-5 py-2 text-sm font-medium text-white bg-green-600 rounded-xl hover:bg-green-700 transition-colors disabled:opacity-60 disabled:cursor-not-allowed">
                <svg id="btnSimpanSpinner" class="hidden animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                </svg>
                <span id="btnSimpanLabel">Simpan User</span>
            </button>
        </div>
    </form>

</x-modal>

<script>
function submitCreateUser() {
    const form    = document.getElementById('createUserForm');
    const btn     = document.getElementById('btnSimpanUser');
    const spinner = document.getElementById('btnSimpanSpinner');
    const label   = document.getElementById('btnSimpanLabel');

    // Reset error
    form.querySelectorAll('[id^="err-"]').forEach(el => el.textContent = '');
    form.querySelectorAll('input,select').forEach(el => el.classList.remove('border-red-400'));

    // Loading state
    btn.disabled  = true;
    spinner.classList.remove('hidden');
    label.textContent = 'Menyimpan...';

    const data = new FormData(form);
    const url  = '{{ route('dashboard.users.store') }}';

    fetch(url, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'X-Requested-With': 'XMLHttpRequest',
        },
        body: data,
    })
    .then(r => r.json())
    .then(res => {
        // Reset loading state
        btn.disabled = false;
        spinner.classList.add('hidden');
        label.textContent = 'Simpan User';

        if (res.success) {
            closeModal('createUserModal');

            // Tampilkan alert berbeda tergantung status email
            showAlert(res.message, 'success');
            applyFilters();
        } else if (res.errors) {
            Object.entries(res.errors).forEach(([field, messages]) => {
                const el    = form.querySelector(`[name="${field}"]`);
                const errEl = document.getElementById(`err-${field}`);
                if (el)    el.classList.add('border-red-400');
                if (errEl) errEl.textContent = messages[0];
            });
        }
    })
    .catch(() => {
        btn.disabled = false;
        spinner.classList.add('hidden');
        label.textContent = 'Simpan User';
        showAlert('Terjadi kesalahan koneksi.', 'error');
    });
}
</script>
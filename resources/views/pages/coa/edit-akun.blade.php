<x-modal
    id="editAkunModal{{ $akun->id }}"
    title="Edit Akun"
>

<form method="POST" 
        action="{{ route('dashboard.coa.akun.update', $akun->id) }}" 
        class="space-y-5">
    @csrf
    @method('PUT')

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                Nomor Akun <span class="text-red-500">*</span>
            </label>

            <input type="text" name="kode_akun" value="{{ old('kode_akun', $akun->kode_akun) }}" 
                class="w-full px-4 py-2.5 text-sm border rounded-xl outline-none transition-colors
                {{ $errors->has('kode_akun') 
                    ? 'border-red-400' 
                    : 'border-gray-200 dark:border-gray-700 focus:border-green-400' 
                }}
                bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400"
            >

            @error('kode_akun')
                <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                Nama Akun <span class="text-red-500">*</span>
            </label>

            <input type="text" name="nama_akun" value="{{ old('nama_akun', $akun->nama_akun) }}" 
                class="w-full px-4 py-2.5 text-sm border rounded-xl outline-none transition-colors
                {{ $errors->has('nama_akun') 
                    ? 'border-red-400' 
                    : 'border-gray-200 dark:border-gray-700 focus:border-green-400' 
                }}
                bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400"
            >

            @error('nama_akun')
                <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                Sub Kategori <span class="text-red-500">*</span>
            </label>

            <div class="relative">
                <select name="parent_id" 
                    class="w-full px-4 py-2.5 text-sm border rounded-xl outline-none appearance-none transition-colors
                    {{ $errors->has('parent_id') 
                        ? 'border-red-400' 
                        : 'border-gray-200 dark:border-gray-700 focus:border-green-400' 
                    }}
                    bg-white dark:bg-gray-800 text-gray-900 dark:text-white">

                    <option value="">Pilih sub kategori</option>

                    @foreach($subKategoriList as $sub)
                        <option value="{{ $sub->id }}"
                            {{ old('parent_id', $akun->parent_id) == $sub->id ? 'selected' : '' }}>
                            {{ $sub->kode_akun }} – {{ $sub->nama_akun }}
                        </option>
                    @endforeach
                </select>

                <svg class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </div>

            @error('parent_id')
                <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                Saldo Normal <span class="text-red-500">*</span>
            </label>

            <div class="relative">
                <select name="saldo_normal"
                    class="w-full px-4 py-2.5 text-sm border rounded-xl outline-none appearance-none transition-colors
                    {{ $errors->has('saldo_normal')
                        ? 'border-red-400'
                        : 'border-gray-200 dark:border-gray-700 focus:border-green-400'
                    }}
                    bg-white dark:bg-gray-800 text-gray-900 dark:text-white">

                    <option value="DEBIT"
                        {{ old('saldo_normal', $akun->saldo_normal) == 'DEBIT' ? 'selected' : '' }}>
                        Debit
                    </option>

                    <option value="KREDIT"
                        {{ old('saldo_normal', $akun->saldo_normal) == 'KREDIT' ? 'selected' : '' }}>
                        Kredit
                    </option>
                </select>

                <svg class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>

            </div>

            @error('saldo_normal')
                <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
            Deskripsi
        </label>

        <textarea name="deskripsi" rows="4"
            class="w-full px-4 py-2.5 text-sm border border-gray-200 dark:border-gray-700 focus:border-green-400 rounded-xl outline-none resize-none transition-colors
            bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400">{{ old('deskripsi', $akun->deskripsi) }}</textarea>
    </div>

    <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-800/50 rounded-xl border border-gray-200 dark:border-gray-700">
        <div>
            <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Status Akun</p>
            <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">Akun aktif dapat digunakan dalam transaksi</p>
        </div>
        <label class="relative inline-flex items-center cursor-pointer">
            <input type="hidden" name="status" value="tidak_aktif">
            <input type="checkbox" name="status" value="aktif"
                {{ old('status', $akun->status) === 'aktif' ? 'checked' : '' }}
                class="sr-only peer">
            <div class="w-11 h-6 bg-gray-200 dark:bg-gray-600 rounded-full peer
                peer-checked:bg-green-500
                after:content-[''] after:absolute after:top-0.5 after:left-0.5
                after:bg-white after:rounded-full after:h-5 after:w-5
                after:transition-all peer-checked:after:translate-x-5">
            </div>
        </label>
    </div>

    <div class="flex items-center gap-3 pt-2">
        <button type="submit"
                class="flex-1 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold px-6 py-2.5 rounded-xl transition-colors">
            Simpan Perubahan
        </button>
    </div>
</form>
</x-modal>
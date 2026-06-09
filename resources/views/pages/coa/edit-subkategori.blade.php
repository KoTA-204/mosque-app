<x-modal
    id="editSubKategoriModal{{ $subKat->id }}"
    title="Edit Sub Kategori Akun"
>

<form method="POST" 
        action="{{ route('dashboard.coa.sub-kategori.update', $subKat->id) }}" 
        class="space-y-5"
        onsubmit="handleFormSubmit(this)">
    @csrf
    @method('PUT')

    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
            Kategori <span class="text-red-500">*</span>
        </label>

        <div class="relative">
            <select name="kategori_akun_id" 
                class="w-full px-4 py-2.5 text-sm border rounded-xl outline-none appearance-none transition-colors
                {{ $errors->has('kategori_akun_id') 
                    ? 'border-red-400' 
                    : 'border-gray-200 dark:border-gray-700 focus:border-green-400' 
                }}
                bg-white dark:bg-gray-800 text-gray-900 dark:text-white">

                <option value="">Pilih kategori</option>

                @foreach($allKategori as $kat)
                    <option value="{{ $kat->id }}"
                        {{ old('kategori_akun_id', $subKat->kategori_akun_id) == $kat->id ? 'selected' : '' }}>
                        ({{ $kat->kode_kategori }}) {{ $kat->nama_kategori }}
                    </option>
                @endforeach
            </select>

            <svg class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </div>

        @error('kategori_akun_id')
            <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
            Kode Sub Kategori <span class="text-red-500">*</span>
        </label>

        <input type="text" name="kode_akun" value="{{ old('kode_akun', $subKat->kode_akun) }}" class="w-full px-4 py-2.5 text-sm border rounded-xl outline-none transition-colors
            {{ $errors->has('kode_akun') 
                ? 'border-red-400' 
                : 'border-gray-200 dark:border-gray-700 focus:border-green-400' 
            }}
            bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400">

        @error('kode_akun')
            <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
            Nama Sub Kategori <span class="text-red-500">*</span>
        </label>

        <input type="text" name="nama_akun" value="{{ old('nama_akun', $subKat->nama_akun) }}" class="w-full px-4 py-2.5 text-sm border rounded-xl outline-none transition-colors
            {{ $errors->has('nama_akun') 
                ? 'border-red-400' 
                : 'border-gray-200 dark:border-gray-700 focus:border-green-400' }}
            bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400">

        @error('nama_akun')
            <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
            Saldo Normal <span class="text-red-500">*</span>
        </label>

        <div class="relative">
            <select
                name="saldo_normal"
                class="w-full px-4 py-2.5 text-sm border rounded-xl outline-none appearance-none transition-colors
                {{ $errors->has('saldo_normal')
                    ? 'border-red-400'
                    : 'border-gray-200 dark:border-gray-700 focus:border-green-400' }}
                bg-white dark:bg-gray-800 text-gray-900 dark:text-white">

                <option value="DEBIT"
                    {{ old('saldo_normal', $subKat->saldo_normal) == 'DEBIT' ? 'selected' : '' }}>
                    Debit
                </option>

                <option value="KREDIT"
                    {{ old('saldo_normal', $subKat->saldo_normal) == 'KREDIT' ? 'selected' : '' }}>
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

    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
            Deskripsi
        </label>

        <textarea name="deskripsi" rows="4" class="w-full px-4 py-2.5 text-sm border border-gray-200 dark:border-gray-700 focus:border-green-400 rounded-xl outline-none resize-none transition-colors
            bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400">{{ old('deskripsi', $subKat->deskripsi) }}</textarea>
    </div>

    <div class="flex items-center gap-3 pt-2">
        <button type="submit"
                class="flex-1 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold px-6 py-2.5 rounded-xl transition-colors">
            Simpan Perubahan
        </button>
    </div>
</form>
</x-modal>
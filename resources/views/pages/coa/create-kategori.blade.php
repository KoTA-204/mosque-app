<x-modal
    id="createKategoriModal"
    title="Tambah Kategori Akun"
>

<form method="POST"
        action="{{ route('dashboard.coa.kategori.store') }}"
        class="space-y-5"
        onsubmit="handleFormSubmit(this)">
    @csrf

    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
            Kode Kategori <span class="text-red-500">*</span>
        </label>

        <input type="text" name="kode_kategori" value="{{ old('kode_kategori') }}" placeholder="Contoh: 1, 2, 3" 
            class="w-full px-4 py-2.5 text-sm border rounded-xl outline-none transition-colors
            {{ $errors->has('kode_kategori') 
                ? 'border-red-400 focus:border-red-400'
                : 'border-gray-200 dark:border-gray-700 focus:border-green-400'
            }}
            bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400">

        @error('kode_kategori')
            <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
            Nama Kategori <span class="text-red-500">*</span>
        </label>

        <input type="text" name="nama_kategori" value="{{ old('nama_kategori') }}" placeholder="Masukan nama kategori akun" class="w-full px-4 py-2.5 text-sm border rounded-xl outline-none transition-colors
            {{ $errors->has('kode_kategori')
                ? 'border-red-400 focus:border-red-400'
                : 'border-gray-200 dark:border-gray-700 focus:border-green-400'
            }}
            bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400"
        >

        @error('nama_kategori')
            <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
        @enderror
    </div>

    <div class="flex items-center gap-3 pt-2">
        <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white text-sm font-semibold px-6 py-2.5 rounded-xl transition-colors">Simpan</button>
    </div>
</form>
</x-modal>
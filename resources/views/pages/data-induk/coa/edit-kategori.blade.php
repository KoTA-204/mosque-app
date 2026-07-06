<x-modal
    id="editKategoriModal{{ $kat->id }}"
    title="Edit Kategori Akun"
>

<form method="POST" 
        action="{{ route('dashboard.coa.kategori.update', $kat->id) }}" class="space-y-5" onsubmit="handleFormSubmit(this)">
    @csrf
    @method('PUT')
    @if(($terkunci ?? false))
        <div class="p-3 rounded-xl bg-red-50 border border-red-200 text-red-800 text-xs">
            Kategori ini sudah memiliki akun turunan, sehingga seluruh datanya dikunci dan tidak dapat diubah.
        </div>
    @elseif(($terpakai ?? false))
        <div class="p-3 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-xs">
            Data ini sudah terpakai. Kode dikunci demi menjaga integritas; hanya nama yang dapat diubah.
        </div>
    @endif
    <input type="hidden" name="_form" value="edit-kategori">
    <input type="hidden" name="_id" value="<?php echo e($kat->id); ?>">

    @php
        $isTarget = old('_form') === 'edit-kategori' && (int) old('_id') === (int) $kat->id;
        $errors = $isTarget ? $errors : new \Illuminate\Support\ViewErrorBag;
    @endphp

    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
            Kode Kategori <span class="text-red-500">*</span>
        </label>

        <input type="text" name="kode_kategori" <?php echo (($terpakai ?? false) || ($terkunci ?? false)) ? 'readonly' : ''; ?> value="{{ ($isTarget ? old('kode_kategori', $kat->kode_kategori) : $kat->kode_kategori) }}" class="w-full px-4 py-2.5 text-sm border rounded-xl outline-none transition-colors  
            {{ $errors->has('kode_kategori')
                ? 'border-red-400 focus:border-red-400'
                : 'border-gray-200 dark:border-gray-700 focus:border-green-400'
            }}
            bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400"
        >

        @error('kode_kategori')
            <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5 gap-3">
            Nama Kategori <span class="text-red-500">*</span>
        </label>

        <input type="text" name="nama_kategori" <?php echo ($terkunci ?? false) ? 'readonly' : ''; ?> value="{{ ($isTarget ? old('nama_kategori', $kat->nama_kategori) : $kat->nama_kategori) }}" class="w-full px-4 py-2.5 text-sm border rounded-xl outline-none transition-colors
            {{ $errors->has('nama_kategori')
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
        <button type="submit" <?php echo ($terkunci ?? false) ? 'disabled' : ''; ?> class="w-full bg-green-600 hover:bg-green-700 text-white text-sm font-semibold px-6 py-2.5 rounded-xl transition-colors">Simpan Perubahan</button>
    </div>
</form>
</x-modal>
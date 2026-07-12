<x-modal
    id="createSubKategoriModal"
    title="Tambah Sub Kategori Akun"
>
<form id="formCreateSubKategori" method="POST" 
        action="{{ route('dashboard.coa.sub-kategori.store') }}" 
        class="space-y-5">
    @csrf
    <input type="hidden" name="_form" value="subkategori">

    @php
        $isTarget = old('_form') === 'subkategori';
        $errors = $isTarget ? $errors : new \Illuminate\Support\ViewErrorBag;
    @endphp

    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
            Kategori <span class="text-red-500">*</span>
        </label>

        <div class="relative">
            <select name="kategori_akun_id" class="w-full px-4 py-2.5 text-sm border rounded-xl outline-none appearance-none transition-colors
                {{ $errors->has('kategori_akun_id')
                    ? 'border-red-400'
                    : 'border-gray-200 dark:border-gray-700 focus:border-green-400' }}
                bg-white dark:bg-gray-800 text-gray-900 dark:text-white">

                <option value="">Pilih kategori</option>

                @foreach($allKategori as $kat)
                <option
                    value="{{ $kat->id }}"
                    {{ ($isTarget ? old('kategori_akun_id') : '') == $kat->id ? 'selected' : '' }}>
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
            Kode Sub Kategori
        </label>

        <input type="text" id="kodeSubKategoriPreview" value="" readonly disabled
            placeholder="Pilih kategori terlebih dahulu"
            class="w-full px-4 py-2.5 text-sm border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-800 text-gray-500 dark:text-gray-400 cursor-not-allowed">

        <p class="mt-1.5 text-xs text-gray-400">Kode ini akan digunakan otomatis oleh sistem saat disimpan.</p>
    </div>

    {{-- Nama Sub Kategori --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
            Nama Sub Kategori <span class="text-red-500">*</span>
        </label>

        <input type="text" name="nama_akun" value="{{ ($isTarget ? old('nama_akun') : '') }}" placeholder="Aset Lancar" class="w-full px-4 py-2.5 text-sm border rounded-xl outline-none transition-colors
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
            <select name="saldo_normal" class="w-full px-4 py-2.5 text-sm border rounded-xl outline-none appearance-none transition-colors
                {{ $errors->has('saldo_normal')
                    ? 'border-red-400'
                    : 'border-gray-200 dark:border-gray-700 focus:border-green-400' }}
                bg-white dark:bg-gray-800 text-gray-900 dark:text-white">

                <option value="DEBIT"
                    {{ ($isTarget ? old('saldo_normal','DEBIT') : 'DEBIT') === 'DEBIT' ? 'selected' : '' }}>
                    Debit
                </option>

                <option value="KREDIT"
                    {{ ($isTarget ? old('saldo_normal') : '') === 'KREDIT' ? 'selected' : '' }}>
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

    {{-- Actions --}}
    <button
        type="submit"
        class="w-full bg-green-600 hover:bg-green-700 text-white text-sm font-semibold px-6 py-2.5 rounded-xl transition-colors">
        Simpan
    </button>
</form>

<script>
    (function () {
        const nextKodeSubKategori = @json($nextKodeSubKategori);
        const kategoriSelect = document.querySelector('#createSubKategoriModal select[name="kategori_akun_id"]');
        const preview = document.getElementById('kodeSubKategoriPreview');

        function updatePreview() {
            preview.value = nextKodeSubKategori[kategoriSelect.value] || '';
        }

        if (kategoriSelect) {
            kategoriSelect.addEventListener('change', updatePreview);
            updatePreview();
        }
    })();
</script>
</x-modal>
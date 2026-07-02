{{-- Partial: _form.blade.php --}}
{{-- Dipakai di create.blade.php dan edit.blade.php --}}

<div class="grid grid-cols-1 gap-5 md:grid-cols-2">

    {{-- Nama Kegiatan --}}
    <div class="md:col-span-2">
        <label class="mb-1.5 block text-sm font-medium text-black dark:text-white">
            Nama Kegiatan <span class="text-red-500">*</span>
        </label>
        <input type="text" name="nama_kegiatan"
               value="{{ old('nama_kegiatan', $kegiatan->nama_kegiatan ?? '') }}"
               placeholder="cth. Qurban 1446 H"
               class="w-full rounded-lg border px-4 py-2.5 text-sm focus:outline-none focus:border-primary
                      {{ $errors->has('nama_kegiatan') ? 'border-red-400 bg-red-50' : 'border-stroke dark:border-strokedark dark:bg-boxdark dark:text-white' }}">
        @error('nama_kegiatan')
            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
        @enderror
    </div>

    {{-- Jenis Kegiatan --}}
    <div>
        <label class="mb-1.5 block text-sm font-medium text-black dark:text-white">
            Jenis Kegiatan <span class="text-red-500">*</span>
        </label>
        <select name="jenis_kegiatan"
                class="w-full rounded-lg border px-4 py-2.5 text-sm focus:outline-none focus:border-primary
                       {{ $errors->has('jenis_kegiatan') ? 'border-red-400 bg-red-50' : 'border-stroke dark:border-strokedark dark:bg-boxdark dark:text-white' }}">
            <option value="">-- Pilih Jenis --</option>
            @foreach(['QURBAN','ZAKAT','KAJIAN','SOSIAL','LAINNYA'] as $j)
                <option value="{{ $j }}" @selected(old('jenis_kegiatan', $kegiatan->jenis_kegiatan ?? '') === $j)>
                    {{ ucfirst(strtolower($j)) }}
                </option>
            @endforeach
        </select>
        @error('jenis_kegiatan')
            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
        @enderror
    </div>

    {{-- Status --}}
    <div>
        <label class="mb-1.5 block text-sm font-medium text-black dark:text-white">
            Status <span class="text-red-500">*</span>
        </label>
        <select name="status"
                class="w-full rounded-lg border px-4 py-2.5 text-sm focus:outline-none focus:border-primary
                       {{ $errors->has('status') ? 'border-red-400 bg-red-50' : 'border-stroke dark:border-strokedark dark:bg-boxdark dark:text-white' }}">
            @foreach(['DRAFT','BERJALAN','SELESAI','DIBATALKAN'] as $s)
                <option value="{{ $s }}" @selected(old('status', $kegiatan->status ?? 'DRAFT') === $s)>
                    {{ ucfirst(strtolower($s)) }}
                </option>
            @endforeach
        </select>
        @error('status')
            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
        @enderror
    </div>

    {{-- Tanggal Mulai --}}
    <div>
        <label class="mb-1.5 block text-sm font-medium text-black dark:text-white">
            Tanggal Mulai <span class="text-red-500">*</span>
        </label>
        <input type="date" name="tanggal_mulai"
               value="{{ old('tanggal_mulai', isset($kegiatan) ? $kegiatan->tanggal_mulai?->format('Y-m-d') : '') }}"
               class="w-full rounded-lg border px-4 py-2.5 text-sm focus:outline-none focus:border-primary
                      {{ $errors->has('tanggal_mulai') ? 'border-red-400 bg-red-50' : 'border-stroke dark:border-strokedark dark:bg-boxdark dark:text-white' }}">
        @error('tanggal_mulai')
            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
        @enderror
    </div>

    {{-- Tanggal Selesai --}}
    <div>
        <label class="mb-1.5 block text-sm font-medium text-black dark:text-white">
            Tanggal Selesai
            <span class="text-xs text-gray-400 font-normal">(opsional)</span>
        </label>
        <input type="date" name="tanggal_selesai"
               value="{{ old('tanggal_selesai', isset($kegiatan) ? $kegiatan->tanggal_selesai?->format('Y-m-d') : '') }}"
               class="w-full rounded-lg border px-4 py-2.5 text-sm focus:outline-none focus:border-primary
                      {{ $errors->has('tanggal_selesai') ? 'border-red-400 bg-red-50' : 'border-stroke dark:border-strokedark dark:bg-boxdark dark:text-white' }}">
        @error('tanggal_selesai')
            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
        @enderror
    </div>

    {{-- Anggaran --}}
    <div>
        <label class="mb-1.5 block text-sm font-medium text-black dark:text-white">
            Anggaran (Rp)
            <span class="text-xs text-gray-400 font-normal">(opsional)</span>
        </label>
        <input type="number" name="anggaran" min="0" step="1000"
               value="{{ old('anggaran', $kegiatan->anggaran ?? 0) }}"
               placeholder="0"
               class="w-full rounded-lg border px-4 py-2.5 text-sm focus:outline-none focus:border-primary
                      {{ $errors->has('anggaran') ? 'border-red-400 bg-red-50' : 'border-stroke dark:border-strokedark dark:bg-boxdark dark:text-white' }}">
        @error('anggaran')
            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
        @enderror
    </div>

    {{-- Panitia --}}
    <div>
        <label class="mb-1.5 block text-sm font-medium text-black dark:text-white">
            Panitia <span class="text-red-500">*</span>
        </label>
        <select name="panitia_id"
                class="w-full rounded-lg border px-4 py-2.5 text-sm focus:outline-none focus:border-primary
                       {{ $errors->has('panitia_id') ? 'border-red-400 bg-red-50' : 'border-stroke dark:border-strokedark dark:bg-boxdark dark:text-white' }}">
            <option value="">-- Pilih Panitia --</option>
            @foreach($panitiaList as $panitia)
                <option value="{{ $panitia->id }}" @selected(old('panitia_id', $kegiatan->panitia_id ?? '') == $panitia->id)>
                    {{ $panitia->name }}
                </option>
            @endforeach
        </select>
        @error('panitia_id')
            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
        @enderror
    </div>

</div>
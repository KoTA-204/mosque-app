@foreach($allKategori as $kategori)
<x-modal
    id="editKategoriModal{{ $kategori->id }}"
    title="Edit Kategori Akun"
>

<div class="p-6">
    <form method="POST" action="{{ route('dashboard.coa.kategori.update', $kategori->id) }}" class="space-y-5">
        @csrf
        @method('PUT')

        <div>
            <label class="block text-sm font-medium mb-1.5">
                Kode Kategori
            </label>
            <input type="text" name="kode_kategori" value="{{ old('kode_kategori', $kategori->kode_kategori) }}" class="w-full px-4 py-2.5 border rounded-xl">
        </div>

        <div>
            <label class="block text-sm font-medium mb-1.5">
                Nama Kategori
            </label>

            <input type="text" name="nama_kategori" value="{{ old('nama_kategori', $kategori->nama_kategori) }}" class="w-full px-4 py-2.5 border rounded-xl">
        </div>

        <button type="submit"
                class="w-full bg-green-600 hover:bg-green-700 text-white py-2.5 rounded-xl">
            Simpan Perubahan
        </button>
    </form>
</div>

</x-modal>
@endforeach

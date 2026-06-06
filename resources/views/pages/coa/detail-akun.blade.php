<x-modal
    id="detailAkunModal{{ $akun->id }}"
    title="Detail Akun">

    <div class="space-y-4">

        <div>
            <p class="text-sm text-gray-500">Kode Akun</p>
            <p class="font-medium">{{ $akun->kode_akun }}</p>
        </div>

        <div>
            <p class="text-sm text-gray-500">Nama Akun</p>
            <p class="font-medium">{{ $akun->nama_akun }}</p>
        </div>

        <div>
            <p class="text-sm text-gray-500">Deskripsi</p>
            <p class="text-gray-700 dark:text-gray-300">
                {{ $akun->deskripsi ?? '-' }}
            </p>
        </div>

    </div>
</x-modal>
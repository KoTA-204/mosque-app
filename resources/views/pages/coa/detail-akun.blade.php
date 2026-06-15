<x-modal id="detailAkunModal{{ $akun->id }}" title="Detail Akun">
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
            <p class="text-sm text-gray-500">Saldo Normal</p>
            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium
                {{ $akun->saldo_normal === 'debit'
                    ? 'bg-blue-50 text-blue-600'
                    : 'bg-purple-50 text-purple-600' }}">
                {{ ucfirst($akun->saldo_normal) }}
            </span>
        </div>

        <div>
            <p class="text-sm text-gray-500">Status</p>
            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium
                {{ $akun->status === 'aktif'
                    ? 'bg-green-50 text-green-600'
                    : 'bg-red-50 text-red-600' }}">
                {{ $akun->status === 'aktif' ? 'Aktif' : 'Tidak Aktif' }}
            </span>
        </div>

        <div>
            <p class="text-sm text-gray-500">Deskripsi</p>
            <p class="text-gray-700 dark:text-gray-300">{{ $akun->deskripsi ?? '-' }}</p>
        </div>
    </div>
</x-modal>
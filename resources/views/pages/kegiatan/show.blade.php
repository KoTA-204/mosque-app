<x-modal id="showKegiatanModal" title="Detail Kegiatan">
​
    <div class="px-6 py-5 space-y-4">
        <div>
            <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Nama Kegiatan</p>
            <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $kegiatan->nama_kegiatan }}</p>
        </div>
        <div>
            <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Deskripsi</p>
            <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-line break-words">{{ $kegiatan->deskripsi ?: '-' }}</p>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Jenis</p>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400">
                    {{ $kegiatan->jenis_kegiatan }}
                </span>
            </div>
            <div>
                <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Status</p>
                @if($kegiatan->status === 'AKTIF')
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-50 dark:bg-green-900/20 text-green-600 dark:text-green-400">Aktif</span>
                @else
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400">Ditutup</span>
                @endif
            </div>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Tanggal Mulai</p>
                <p class="text-sm text-gray-700 dark:text-gray-300">
                    {{ $kegiatan->tanggal_mulai?->isoFormat('D MMMM Y') ?? '-' }}
                </p>
            </div>
            <div>
                <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Tanggal Selesai</p>
                <p class="text-sm text-gray-700 dark:text-gray-300">
                    {{ $kegiatan->tanggal_selesai?->isoFormat('D MMMM Y') ?? '-' }}
                </p>
            </div>
        </div>
        <div>
            <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Anggaran</p>
            <p class="text-sm font-semibold text-gray-900 dark:text-white">
                Rp {{ number_format($kegiatan->anggaran, 0, ',', '.') }}
            </p>
        </div>
        <div>
            <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Penanggung Jawab</p>
            <p class="text-sm text-gray-700 dark:text-gray-300">{{ $kegiatan->panitia->name }}</p>
            <p class="text-xs text-gray-400 mt-0.5">{{ $kegiatan->panitia->email }}</p>
        </div>
        <div>
            <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Jumlah Transaksi</p>
            <p class="text-sm text-gray-700 dark:text-gray-300">{{ $kegiatan->transaksi_count }} transaksi</p>
        </div>
    </div>
​
    <div class="flex justify-end px-6 py-4 border-t border-gray-100 dark:border-gray-800">
        <button type="button" onclick="closeModal('showKegiatanModal')"
            class="px-5 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
            Tutup
        </button>
    </div>
​
</x-modal>
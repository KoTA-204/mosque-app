@extends('layouts.app')

@section('content')
<div class="p-6 space-y-6">
    <x-approval.detail-header title="Detail Transaksi" />

    <x-approval.action-panel
        :transaksi="$transaksi"
        confirm-text="Yakin menyetujui transaksi ini?"
        revisi-title="Catatan revisi untuk panitia"
        revisi-placeholder="Tuliskan catatan yang perlu diperbaiki panitia..." />

    {{-- Informasi Transaksi --}}
    @php $jenis = $transaksi->jenis_transaksi; @endphp
    @php $kg = $transaksi->kegiatan; @endphp
    @if($transaksi->jenis_transaksi === 'PENGELUARAN' && $kg && $kg->anggaran > 0 && $kg->selisihLebihAnggaran() > 0)
    <div class="flex items-start gap-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-2xl px-5 py-4">
        <svg class="w-5 h-5 shrink-0 text-amber-500 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
        </svg>
        <div class="text-sm text-amber-700 dark:text-amber-300">
            <p class="font-semibold">Melebihi anggaran kegiatan</p>
            <p class="mt-0.5">
                Total pengeluaran berjalan <strong>Rp <?php echo number_format($kg->totalPengeluaranBerjalan(), 0, ',', '.'); ?></strong>
                dari anggaran <strong>Rp <?php echo number_format($kg->anggaran, 0, ',', '.'); ?></strong>
                — lebih <strong>Rp <?php echo number_format($kg->selisihLebihAnggaran(), 0, ',', '.'); ?></strong>.
                Mohon tinjau sebelum menyetujui.
            </p>
        </div>
    </div>
    @endif
    <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-6">
        <div class="mb-5 flex items-center justify-between">
            <h2 class="text-base font-semibold text-gray-900 dark:text-white">Informasi transaksi</h2>
            <x-approval.status-badge :status="$transaksi->status_approval" />
        </div>
        <div class="grid grid-cols-2 gap-x-8 gap-y-5 md:grid-cols-3">
            <div>
                <p class="mb-1 text-xs text-gray-500 dark:text-gray-400">Kode transaksi</p>
                <p class="font-mono text-sm font-medium text-gray-800 dark:text-gray-200">
                    TRX-{{ now()->year }}-{{ str_pad($transaksi->id, 3, '0', STR_PAD_LEFT) }}
                </p>
            </div>
            <div>
                <p class="mb-1 text-xs text-gray-500 dark:text-gray-400">Tanggal</p>
                <p class="text-sm text-gray-800 dark:text-gray-200">{{ $transaksi->tanggal_transaksi->format('j F Y') }}</p>
            </div>
            <div>
                <p class="mb-1 text-xs text-gray-500 dark:text-gray-400">Jenis</p>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $jenis === 'PEMASUKAN' ? 'bg-purple-50 dark:bg-purple-900/20 text-purple-600 dark:text-purple-400' : 'bg-pink-50 dark:bg-pink-900/20 text-pink-600 dark:text-pink-400' }}">
                    {{ ucfirst(strtolower($jenis)) }}
                </span>
            </div>
            <div>
                <p class="mb-1 text-xs text-gray-500 dark:text-gray-400">Jumlah</p>
                <p class="text-sm font-semibold {{ $jenis === 'PEMASUKAN' ? 'text-green-600 dark:text-green-400' : 'text-red-500 dark:text-red-400' }}">
                    Rp {{ number_format($transaksi->jumlah, 0, ',', '.') }}
                </p>
            </div>
            <div>
                <p class="mb-1 text-xs text-gray-500 dark:text-gray-400">Kategori</p>
                <p class="text-sm text-gray-800 dark:text-gray-200">{{ $transaksi->kategoriTransaksi->nama_kategori }}</p>
            </div>
            <div>
                <p class="mb-1 text-xs text-gray-500 dark:text-gray-400">Dompet</p>
                <p class="text-sm text-gray-800 dark:text-gray-200">{{ $transaksi->dompet->nama_dompet }}</p>
            </div>
            <div>
                <p class="mb-1 text-xs text-gray-500 dark:text-gray-400">Kegiatan</p>
                <p class="text-sm text-gray-800 dark:text-gray-200">{{ $transaksi->kegiatan->nama_kegiatan ?? '-' }}</p>
            </div>
            <div>
                <p class="mb-1 text-xs text-gray-500 dark:text-gray-400">Dicatat oleh</p>
                <p class="text-sm text-gray-800 dark:text-gray-200">
                    {{ $transaksi->user->name }} · {{ $transaksi->created_at->format('j M Y H.i') }}
                </p>
            </div>
        </div>

        @if($transaksi->deskripsi)
        <div class="mt-5 border-t border-gray-100 dark:border-gray-800 pt-5">
            <p class="mb-1 text-xs text-gray-500 dark:text-gray-400">Deskripsi</p>
            <p class="text-sm text-gray-800 dark:text-gray-200">{{ $transaksi->deskripsi }}</p>
        </div>
        @endif

        @if(in_array($transaksi->status_approval, ['REJECTED', 'REVISION']) && $transaksi->catatan)
        <div class="mt-5 border-t border-gray-100 dark:border-gray-800 pt-5">
            <p class="mb-1 text-xs text-gray-500 dark:text-gray-400">
                {{ $transaksi->status_approval === 'REJECTED' ? 'Alasan penolakan' : 'Catatan revisi' }}
            </p>
            <p class="text-sm text-gray-800 dark:text-gray-200">{{ $transaksi->catatan }}</p>
        </div>
        @endif

        @if($transaksi->buktiTransaksi->count() > 0)
        <div class="mt-5 border-t border-gray-100 dark:border-gray-800 pt-5">
            <p class="mb-3 text-xs text-gray-500 dark:text-gray-400">Bukti transaksi</p>
            <div class="flex flex-col gap-2">
                @foreach($transaksi->buktiTransaksi as $bukti)
                    <x-approval.file-link :url="Storage::url($bukti->path_file)" :name="$bukti->nama_file" />
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

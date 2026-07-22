@extends('layouts.app')

@section('content')
<div class="p-6 space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 px-6 py-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('dashboard.transaksi-kegiatan.show', $kegiatan) }}" class="p-1.5 text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <h1 class="text-lg font-semibold text-gray-900 dark:text-white">Detail Transaksi</h1>
        </div>
        @php
            $statusLabel = match($transaksi->status_persetujuan) {
                'APPROVED' => 'Disetujui',
                'PENDING'  => 'Menunggu',
                'REVISION' => 'Revisi',
                'REJECTED' => 'Ditolak',
                'DRAFT'    => 'Draf',
                default    => $transaksi->status_persetujuan,
            };

            $statusClass = match($transaksi->status_persetujuan) {
                'APPROVED' => 'bg-green-50 dark:bg-green-900/20 text-green-600 dark:text-green-400',
                'PENDING'  => 'bg-orange-50 dark:bg-orange-900/20 text-orange-600 dark:text-orange-400',
                'REVISION' => 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400',
                'REJECTED' => 'bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400',
                'DRAFT'    => 'bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400',
                default    => 'bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400',
            };
        @endphp
        <span class="rounded-full px-3 py-1 text-sm font-medium {{ $statusClass }}">{{ $statusLabel }}</span>
    </div>

    {{-- Catatan Revisi --}}
    @if($transaksi->catatan && $transaksi->status_persetujuan === 'REVISION')
    <div class="mb-4 rounded-xl border border-blue-200 bg-blue-50 p-5 dark:border-blue-900/50 dark:bg-blue-900/20">
        <p class="mb-1 text-sm font-medium text-blue-800 dark:text-blue-200">Catatan revisi dari Bendahara:</p>
        <p class="text-sm text-blue-700 dark:text-blue-300">{{ $transaksi->catatan }}</p>
        <div class="mt-3">
            <a href="{{ route('dashboard.kencleng.edit', $kencleng) }}"
               class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                Perbaiki & Ajukan Ulang
            </a>
        </div>
    </div>
    @endif

    {{-- Detail Card --}}
    <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-6">
        <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-5">Informasi Transaksi</h3>
        <div class="grid grid-cols-2 gap-x-8 gap-y-5">
            <div>
                <p class="mb-1 text-xs text-gray-500 dark:text-gray-400">Kode transaksi</p>
                {{-- pakai tahun dari created_at transaksi (bukan now()) --}}
                <p class="font-mono text-sm font-medium text-gray-900 dark:text-white">TRX-{{ $transaksi->created_at->year }}-{{ str_pad($transaksi->id, 3, '0', STR_PAD_LEFT) }}</p>
            </div>
            <div>
                <p class="mb-1 text-xs text-gray-500 dark:text-gray-400">Tanggal</p>
                <p class="text-sm text-gray-900 dark:text-white">{{ $transaksi->tanggal_transaksi->format('j F Y') }}</p>
            </div>
            @php $jenis = $transaksi->jenis_transaksi; @endphp
            <div>
                <p class="mb-1 text-xs text-gray-500 dark:text-gray-400">Jenis</p>
                <span class="rounded-full px-2.5 py-1 text-xs font-medium {{ $jenis === 'PEMASUKAN' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">{{ ucfirst(strtolower($jenis)) }}</span>
            </div>
            <div>
                <p class="mb-1 text-xs text-gray-500 dark:text-gray-400">Jumlah</p>
                <p class="text-sm font-semibold {{ $jenis === 'PEMASUKAN' ? 'text-green-600' : 'text-red-600' }}">Rp {{ number_format($transaksi->jumlah, 0, ',', '.') }}</p>
            </div>
            <div>
                <p class="mb-1 text-xs text-gray-500 dark:text-gray-400">Kategori</p>
                <p class="text-sm text-gray-900 dark:text-white">{{ $transaksi->kategoriTransaksi->nama_kategori }}</p>
            </div>
            <div>
                <p class="mb-1 text-xs text-gray-500 dark:text-gray-400">Dompet</p>
                <p class="text-sm text-gray-900 dark:text-white">{{ $transaksi->dompet->nama_dompet }}</p>
            </div>
            <div>
                <p class="mb-1 text-xs text-gray-500 dark:text-gray-400">Kegiatan</p>
                <p class="text-sm text-gray-900 dark:text-white">{{ $transaksi->kegiatan->nama_kegiatan }}</p>
            </div>
            <div>
                <p class="mb-1 text-xs text-gray-500 dark:text-gray-400">Dicatat oleh</p>
                <p class="text-sm text-gray-900 dark:text-white">{{ $transaksi->user->name }} <span class="text-gray-400">·</span> {{ $transaksi->created_at->format('j M Y H.i') }}</p>
            </div>
        </div>

        @if($transaksi->deskripsi)
        <div class="mt-5 pt-5 border-t border-gray-100 dark:border-gray-800">
            <p class="mb-1.5 text-xs text-gray-500 dark:text-gray-400">Deskripsi</p>
            <p class="text-sm text-gray-900 dark:text-white">{{ $transaksi->deskripsi }}</p>
        </div>
        @endif

        @if($transaksi->buktiTransaksi->count() > 0)
        <div class="mt-5 pt-5 border-t border-gray-100 dark:border-gray-800">
            <p class="mb-2.5 text-xs text-gray-500 dark:text-gray-400">Bukti transaksi</p>
            <div class="flex flex-col gap-2">
                @foreach($transaksi->buktiTransaksi as $bukti)
                @php $ext = strtoupper(pathinfo($bukti->nama_file, PATHINFO_EXTENSION)); @endphp
                <a href="{{ Storage::url($bukti->path_file) }}" target="_blank" class="flex items-center gap-3 rounded-xl border border-gray-200 dark:border-gray-700 px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                    <span class="text-sm text-gray-900 dark:text-white flex-1">{{ $bukti->nama_file }}</span>
                    <span class="rounded-lg px-2 py-0.5 text-xs font-medium {{ $ext === 'PDF' ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-700' }}">{{ $ext }}</span>
                </a>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@extends('layouts.app')

@section('content')
<div class="p-6 space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 px-6 py-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('dashboard.transaksi-kegiatan.show', $kegiatan) }}"
               class="p-1.5 text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <h1 class="text-lg font-semibold text-gray-900 dark:text-white">Detail Transaksi</h1>
        </div>

        {{-- Status Badge --}}
        @php
            $statusColor = match($transaksi->status_approval) {
                'PENDING'  => 'bg-yellow-100 text-yellow-800',
                'APPROVED' => 'bg-green-100 text-green-800',
                'REJECTED' => 'bg-red-100 text-red-800',
                'REVISION' => 'bg-blue-100 text-blue-800',
                default    => 'bg-gray-100 text-gray-800',
            };
        @endphp
        <span class="rounded-full px-3 py-1 text-sm font-medium {{ $statusColor }}">
            {{ ucfirst(strtolower($transaksi->status_approval)) }}
        </span>
    </div>

    {{-- Detail Card --}}
    <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-6">

        <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-5">Informasi Transaksi</h3>

        <div class="grid grid-cols-2 gap-x-8 gap-y-5">
            <div>
                <p class="mb-1 text-xs text-gray-500 dark:text-gray-400">Kode transaksi</p>
                <p class="font-mono text-sm font-medium text-gray-900 dark:text-white">
                    TRX-{{ now()->year }}-{{ str_pad($transaksi->id, 3, '0', STR_PAD_LEFT) }}
                </p>
            </div>
            <div>
                <p class="mb-1 text-xs text-gray-500 dark:text-gray-400">Tanggal</p>
                <p class="text-sm text-gray-900 dark:text-white">
                    {{ $transaksi->tanggal_transaksi->format('j F Y') }}
                </p>
            </div>
            <div>
                <p class="mb-1 text-xs text-gray-500 dark:text-gray-400">Jenis</p>
                @php $jenis = $transaksi->jenis_transaksi; @endphp
                <span class="rounded-full px-2.5 py-1 text-xs font-medium
                    {{ $jenis === 'PEMASUKAN' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                    {{ ucfirst(strtolower($jenis)) }}
                </span>
            </div>
            <div>
                <p class="mb-1 text-xs text-gray-500 dark:text-gray-400">Jumlah</p>
                <p class="text-sm font-semibold {{ $jenis === 'PEMASUKAN' ? 'text-green-600' : 'text-red-600' }}">
                    Rp {{ number_format($transaksi->jumlah, 0, ',', '.') }}
                </p>
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
                <p class="text-sm text-gray-900 dark:text-white">
                    {{ $transaksi->user->name }}
                    <span class="text-gray-400">·</span>
                    {{ $transaksi->created_at->format('j M Y H.i') }}
                </p>
            </div>
        </div>

        {{-- Deskripsi --}}
        @if($transaksi->deskripsi)
        <div class="mt-5 pt-5 border-t border-gray-100 dark:border-gray-800">
            <p class="mb-1.5 text-xs text-gray-500 dark:text-gray-400">Deskripsi</p>
            <p class="text-sm text-gray-900 dark:text-white">{{ $transaksi->deskripsi }}</p>
        </div>
        @endif

        {{-- Bukti Transaksi --}}
        @if($transaksi->buktiTransaksi->count() > 0)
        <div class="mt-5 pt-5 border-t border-gray-100 dark:border-gray-800">
            <p class="mb-2.5 text-xs text-gray-500 dark:text-gray-400">Bukti transaksi</p>
            <div class="flex flex-col gap-2">
                @foreach($transaksi->buktiTransaksi as $bukti)
                @php
                    $ext = strtoupper(pathinfo($bukti->nama_file, PATHINFO_EXTENSION));
                @endphp
                <a href="{{ Storage::url($bukti->path_file) }}" target="_blank"
                   class="flex items-center gap-3 rounded-xl border border-gray-200 dark:border-gray-700 px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                    <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                    </svg>
                    <span class="text-sm text-gray-900 dark:text-white flex-1">{{ $bukti->nama_file }}</span>
                    <span class="rounded-lg px-2 py-0.5 text-xs font-medium
                        {{ $ext === 'PDF' ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-700' }}">
                        {{ $ext }}
                    </span>
                </a>
                @endforeach
            </div>
        </div>
        @endif

    </div>
</div>
@endsection
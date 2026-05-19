@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-screen-2xl p-4 md:p-6 2xl:p-10">

    {{-- Header --}}
    <div class="mb-6 rounded-xl border border-stroke bg-white p-6 shadow-default dark:border-strokedark dark:bg-boxdark">
        <h2 class="text-2xl font-bold text-dark dark:text-white">Detail Transaksi</h2>
    </div>

    <div class="rounded-xl border border-stroke bg-white p-6 shadow-default dark:border-strokedark dark:bg-boxdark">

        {{-- Kembali + Status --}}
        <div class="mb-6 flex items-center justify-between">
            <a href="{{ route('dashboard.kegiatan-panitia.show', $kegiatan) }}"
               class="text-sm text-body hover:text-primary dark:text-bodydark">
                Kembali
            </a>
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

        <h3 class="mb-4 text-lg font-semibold text-black dark:text-white">Informasi transaksi</h3>

        <div class="grid grid-cols-2 gap-6">
            <div>
                <p class="mb-1 text-xs text-body dark:text-bodydark">Kode transaksi</p>
                <p class="font-mono text-sm font-medium text-black dark:text-white">
                    TRX-{{ now()->year }}-{{ str_pad($transaksi->id, 3, '0', STR_PAD_LEFT) }}
                </p>
            </div>
            <div>
                <p class="mb-1 text-xs text-body dark:text-bodydark">Tanggal</p>
                <p class="text-sm text-black dark:text-white">
                    {{ $transaksi->tanggal_transaksi->format('j F Y') }}
                </p>
            </div>
            <div>
                <p class="mb-1 text-xs text-body dark:text-bodydark">Jenis</p>
                @php $jenis = $transaksi->kategoriTransaksi->jenis_transaksi; @endphp
                <span class="rounded-full px-2 py-0.5 text-xs font-medium
                    {{ $jenis === 'PEMASUKAN' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                    {{ ucfirst(strtolower($jenis)) }}
                </span>
            </div>
            <div>
                <p class="mb-1 text-xs text-body dark:text-bodydark">Jumlah</p>
                <p class="text-sm font-semibold {{ $jenis === 'PEMASUKAN' ? 'text-green-600' : 'text-red-600' }}">
                    Rp {{ number_format($transaksi->jumlah, 0, ',', '.') }}
                </p>
            </div>
            <div>
                <p class="mb-1 text-xs text-body dark:text-bodydark">Kategori</p>
                <p class="text-sm text-black dark:text-white">{{ $transaksi->kategoriTransaksi->nama_kategori }}</p>
            </div>
            <div>
                <p class="mb-1 text-xs text-body dark:text-bodydark">Dompet</p>
                <p class="text-sm text-black dark:text-white">{{ $transaksi->dompet->nama_dompet }}</p>
            </div>
            <div>
                <p class="mb-1 text-xs text-body dark:text-bodydark">Kegiatan</p>
                <p class="text-sm text-black dark:text-white">{{ $transaksi->kegiatan->nama_kegiatan }}</p>
            </div>
            <div>
                <p class="mb-1 text-xs text-body dark:text-bodydark">Dicatat oleh</p>
                <p class="text-sm text-black dark:text-white">
                    {{ $transaksi->user->name }}
                    · {{ $transaksi->created_at->format('j M Y H.i') }}
                </p>
            </div>
        </div>

        {{-- Deskripsi --}}
        @if($transaksi->deskripsi)
        <div class="mt-4">
            <p class="mb-1 text-xs text-body dark:text-bodydark">Deskripsi</p>
            <p class="text-sm text-black dark:text-white">{{ $transaksi->deskripsi }}</p>
        </div>
        @endif

        {{-- Bukti Transaksi --}}
        @if($transaksi->buktiTransaksi->count() > 0)
        <div class="mt-4">
            <p class="mb-2 text-xs text-body dark:text-bodydark">Bukti transaksi</p>
            <div class="flex flex-col gap-2">
                @foreach($transaksi->buktiTransaksi as $bukti)
                @php
                    $ext = strtoupper(pathinfo($bukti->nama_file, PATHINFO_EXTENSION));
                @endphp
                <a href="{{ Storage::url($bukti->path_file) }}" target="_blank"
                   class="flex items-center gap-3 rounded-lg border border-stroke px-4 py-3 hover:bg-gray-50 dark:border-strokedark">
                    <span class="text-sm text-black dark:text-white">{{ $bukti->nama_file }}</span>
                    <span class="rounded px-2 py-0.5 text-xs font-medium
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
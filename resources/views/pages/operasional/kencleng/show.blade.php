@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-screen-2xl p-4 md:p-6 2xl:p-10">

    {{-- Header --}}
    <div class="mb-6 flex items-center justify-between bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 px-6 py-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('dashboard.kencleng.index') }}"
               class="p-1.5 text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <h1 class="text-lg font-semibold text-gray-900 dark:text-white">Detail Kencleng</h1>
        </div>
    </div>

    {{-- Catatan Revisi --}}
    @if($kencleng->transaksi->catatan && $kencleng->transaksi->status_persetujuan === 'REVISION')
    <div class="mb-4 rounded-xl border border-blue-200 bg-blue-50 p-5 dark:border-blue-900/50 dark:bg-blue-900/20">
        <p class="mb-1 text-sm font-medium text-blue-800 dark:text-blue-200">Catatan revisi dari Bendahara:</p>
        <p class="text-sm text-blue-700 dark:text-blue-300">{{ $kencleng->transaksi->catatan }}</p>
        <div class="mt-3">
            <a href="{{ route('dashboard.kencleng.edit', $kencleng) }}"
               class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                Perbaiki & Ajukan Ulang
            </a>
        </div>
    </div>
    @endif

    {{-- Informasi Transaksi --}}
    <div class="mb-6 rounded-xl border border-stroke bg-white p-6 shadow-default dark:border-strokedark dark:bg-boxdark">
        @php
            $status      = $kencleng->transaksi->status_persetujuan;
            $statusLabel = match($status) {
                'APPROVED' => 'Disetujui',
                'PENDING'  => 'Pending',
                'REVISION' => 'Perlu Revisi',
                'REJECTED' => 'Ditolak',
                'DRAFT'    => 'Draf',
                default    => $status,
            };
            $statusStyle = match($status) {
                'APPROVED' => 'bg-green-100 text-green-700',
                'PENDING'  => 'bg-yellow-100 text-yellow-700',
                'REVISION' => 'bg-blue-100 text-blue-700',
                'REJECTED' => 'bg-red-100 text-red-700',
                'DRAFT'    => 'bg-gray-100 text-gray-700',
                default    => 'bg-gray-100 text-gray-700',
            };
        @endphp

        <div class="mb-5 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-black dark:text-white">Informasi transaksi</h3>
            <span class="rounded-full px-3 py-1 text-sm font-medium {{ $statusStyle }}">
                {{ $statusLabel }}
            </span>
        </div>

        <div class="grid grid-cols-2 gap-x-8 gap-y-5 md:grid-cols-3">
            <div>
                <p class="mb-1 text-xs text-body dark:text-bodydark">Kode transaksi</p>
                <p class="font-mono text-sm font-medium text-black dark:text-white">
                    TRX-{{ now()->year }}-{{ str_pad($kencleng->transaksi->id, 3, '0', STR_PAD_LEFT) }}
                </p>
            </div>
            <div>
                <p class="mb-1 text-xs text-body dark:text-bodydark">Tanggal hitung</p>
                <p class="text-sm text-black dark:text-white">
                    {{ $kencleng->transaksi->tanggal_transaksi->format('j F Y') }}
                </p>
            </div>
            @if($kencleng->nomor_kwitansi)
            <div>
                <p class="mb-1 text-xs text-body dark:text-bodydark">Nomor kwitansi</p>
                <p class="font-mono text-sm font-medium text-black dark:text-white">
                    {{ $kencleng->nomor_kwitansi }}
                </p>
            </div>
            @endif
            <div>
                <p class="mb-1 text-xs text-body dark:text-bodydark">Dompet</p>
                <p class="text-sm text-black dark:text-white">{{ $kencleng->transaksi->dompet->nama_dompet }}</p>
            </div>
            <div>
                <p class="mb-1 text-xs text-body dark:text-bodydark">Dicatat oleh</p>
                <p class="text-sm text-black dark:text-white">
                    {{ $kencleng->transaksi->user->name }}
                    · {{ $kencleng->transaksi->created_at->format('j M Y H.i') }}
                </p>
            </div>
            <div>
                <p class="mb-1 text-xs text-body dark:text-bodydark">Jumlah disetor</p>
                <p class="text-sm font-semibold text-green-600">
                    Rp {{ number_format($kencleng->transaksi->jumlah, 0, ',', '.') }}
                </p>
            </div>
        </div>
    </div>

    {{-- Rincian Pecahan --}}
    @if($kencleng->detail->count() > 0)
    @php
        $jumlahSetor = (float) $kencleng->transaksi->jumlah;
        $selisih     = $totalFisik - $jumlahSetor;
        $sorted      = $kencleng->detail->sortBy('pecahan');
    @endphp
    <div class="mb-6 rounded-xl border border-stroke bg-white p-6 shadow-default dark:border-strokedark dark:bg-boxdark">
        <h3 class="mb-5 text-lg font-semibold text-black dark:text-white">Rincian pecahan uang</h3>

        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
            @foreach($sorted as $detail)
            @php $subtotal = $detail->pecahan * $detail->jumlah_pecahan; @endphp
            <div class="rounded-lg border border-stroke p-4 dark:border-strokedark">
                <p class="text-xs text-body dark:text-bodydark">
                    Rp {{ number_format($detail->pecahan, 0, ',', '.') }}
                </p>
                <p class="text-sm font-medium text-green-600">
                    Rp {{ number_format($subtotal, 0, ',', '.') }}
                </p>
                <p class="mt-1 text-right text-sm font-semibold text-black dark:text-white">
                    {{ number_format($detail->jumlah_pecahan, 0, ',', '.') }}
                    {{ $detail->pecahan < 1000 ? 'keping' : 'lembar' }}
                </p>
            </div>
            @endforeach
        </div>

        <div class="mt-5 rounded-lg border border-green-200 bg-green-50 px-5 py-4 dark:border-green-900/50 dark:bg-green-900/20">
            <div class="flex items-center justify-between">
                <p class="text-sm font-medium text-green-700 dark:text-green-400">Total fisik terhitung</p>
                <p class="text-lg font-bold text-green-700 dark:text-green-400">
                    Rp {{ number_format($totalFisik, 0, ',', '.') }}
                </p>
            </div>
        </div>

        <div class="mt-3 space-y-2 border-t border-stroke pt-4 dark:border-strokedark">
            <div class="flex items-center justify-between text-sm">
                <p class="text-body dark:text-bodydark">Jumlah disetor ke kas</p>
                <p class="font-medium text-black dark:text-white">
                    Rp {{ number_format($jumlahSetor, 0, ',', '.') }}
                </p>
            </div>
            @if($selisih != 0)
            <div class="flex items-center justify-between text-xs">
                <p class="text-body dark:text-bodydark">Selisih</p>
                <p class="{{ $selisih > 0 ? 'text-blue-600' : 'text-red-600' }}">
                    Rp {{ number_format(abs($selisih), 0, ',', '.') }}
                    — {{ $selisih > 0 ? 'dicatat sebagai transfer ke rekening' : 'kekurangan' }}
                </p>
            </div>
            @endif
        </div>
    </div>
    @endif

    {{-- Berita Acara --}}
    @if($kencleng->berita_acara)
    <div class="mb-6 rounded-xl border border-stroke bg-white p-6 shadow-default dark:border-strokedark dark:bg-boxdark">
        <h3 class="mb-4 text-lg font-semibold text-black dark:text-white">Berita acara</h3>
        @php $ext = strtoupper(pathinfo($kencleng->berita_acara, PATHINFO_EXTENSION)); @endphp
        <a href="{{ Storage::url($kencleng->berita_acara) }}" target="_blank"
           class="flex items-center gap-3 rounded-lg border border-stroke px-4 py-3 hover:bg-gray-50 dark:border-strokedark dark:hover:bg-meta-4 transition-colors">
            <svg class="h-4 w-4 shrink-0 text-body dark:text-bodydark" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                <polyline points="14 2 14 8 20 8"/>
            </svg>
            <span class="text-sm text-black dark:text-white">{{ basename($kencleng->berita_acara) }}</span>
            <span class="ml-auto rounded px-2 py-0.5 text-xs font-medium
                {{ $ext === 'PDF' ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-700' }}">
                {{ $ext }}
            </span>
        </a>
    </div>
    @endif

    {{-- Keterangan --}}
    @if($kencleng->transaksi->deskripsi)
    <div class="rounded-xl border border-stroke bg-white p-6 shadow-default dark:border-strokedark dark:bg-boxdark">
        <h3 class="mb-3 text-lg font-semibold text-black dark:text-white">Keterangan tambahan</h3>
        <p class="text-sm text-body dark:text-bodydark">{{ $kencleng->transaksi->deskripsi }}</p>
    </div>
    @endif

</div>
@endsection
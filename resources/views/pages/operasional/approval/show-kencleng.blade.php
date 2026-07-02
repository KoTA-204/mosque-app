@extends('layouts.app')

@section('content')
<div class="p-6 space-y-6">
    <x-approval.detail-header title="Detail Kencleng" />

    <x-approval.action-panel
        :transaksi="$transaksi"
        confirm-text="Yakin menyetujui transaksi kencleng ini?" />

    {{-- Informasi Transaksi --}}
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
                <p class="mb-1 text-xs text-gray-500 dark:text-gray-400">Tanggal hitung</p>
                <p class="text-sm text-gray-800 dark:text-gray-200">{{ $transaksi->tanggal_transaksi->format('j F Y') }}</p>
            </div>
            @if($transaksi->kencleng->nomor_kwitansi ?? null)
            <div>
                <p class="mb-1 text-xs text-gray-500 dark:text-gray-400">Nomor kwitansi</p>
                <p class="font-mono text-sm font-medium text-gray-800 dark:text-gray-200">{{ $transaksi->kencleng->nomor_kwitansi }}</p>
            </div>
            @endif
            <div>
                <p class="mb-1 text-xs text-gray-500 dark:text-gray-400">Dompet</p>
                <p class="text-sm text-gray-800 dark:text-gray-200">{{ $transaksi->dompet->nama_dompet }}</p>
            </div>
            <div>
                <p class="mb-1 text-xs text-gray-500 dark:text-gray-400">Dicatat oleh</p>
                <p class="text-sm text-gray-800 dark:text-gray-200">
                    {{ $transaksi->user->name }} · {{ $transaksi->created_at->format('j M Y H.i') }}
                </p>
            </div>
            <div>
                <p class="mb-1 text-xs text-gray-500 dark:text-gray-400">Jumlah disetor</p>
                <p class="text-sm font-semibold text-green-600 dark:text-green-400">
                    Rp {{ number_format($transaksi->jumlah, 0, ',', '.') }}
                </p>
            </div>
        </div>

        @if(in_array($transaksi->status_approval, ['REJECTED', 'REVISION']) && $transaksi->catatan)
        <div class="mt-5 border-t border-gray-100 dark:border-gray-800 pt-5">
            <p class="mb-1 text-xs text-gray-500 dark:text-gray-400">
                {{ $transaksi->status_approval === 'REJECTED' ? 'Alasan penolakan' : 'Catatan revisi' }}
            </p>
            <p class="text-sm text-gray-800 dark:text-gray-200">{{ $transaksi->catatan }}</p>
        </div>
        @endif
    </div>

    {{-- Rincian Pecahan --}}
    @if($transaksi->kencleng && $transaksi->kencleng->detail->count() > 0)
    @php
        $details     = $transaksi->kencleng->detail;
        $totalFisik  = $details->sum(fn($d) => $d->pecahan * $d->jumlah_pecahan);
        $jumlahSetor = (float) $transaksi->jumlah;
        $selisih     = $totalFisik - $jumlahSetor;
        $sorted      = $details->sortBy('pecahan');
    @endphp
    <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-6">
        <h2 class="mb-5 text-base font-semibold text-gray-900 dark:text-white">Rincian pecahan uang</h2>
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
            @foreach($sorted as $detail)
            @php $subtotal = $detail->pecahan * $detail->jumlah_pecahan; @endphp
            <div class="rounded-xl border border-gray-200 dark:border-gray-700 p-4">
                <p class="text-xs text-gray-500 dark:text-gray-400">Rp {{ number_format($detail->pecahan, 0, ',', '.') }}</p>
                <p class="text-sm font-medium text-green-600 dark:text-green-400">Rp {{ number_format($subtotal, 0, ',', '.') }}</p>
                <p class="mt-1 text-right text-sm font-semibold text-gray-800 dark:text-gray-200">
                    {{ number_format($detail->jumlah_pecahan, 0, ',', '.') }}
                    {{ $detail->pecahan < 1000 ? 'keping' : 'lembar' }}
                </p>
            </div>
            @endforeach
        </div>
        <div class="mt-5 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl px-5 py-4">
            <div class="flex items-center justify-between">
                <p class="text-sm font-medium text-green-700 dark:text-green-400">Total fisik terhitung</p>
                <p class="text-lg font-bold text-green-700 dark:text-green-400">Rp {{ number_format($totalFisik, 0, ',', '.') }}</p>
            </div>
        </div>
        <div class="mt-4 space-y-2 border-t border-gray-100 dark:border-gray-800 pt-4">
            <div class="flex items-center justify-between text-sm">
                <p class="text-gray-500 dark:text-gray-400">Jumlah disetor ke kas</p>
                <p class="font-medium text-gray-800 dark:text-gray-200">Rp {{ number_format($jumlahSetor, 0, ',', '.') }}</p>
            </div>
            @if($selisih != 0)
            <div class="flex items-center justify-between text-xs">
                <p class="text-gray-500 dark:text-gray-400">Selisih</p>
                <p class="{{ $selisih > 0 ? 'text-blue-600 dark:text-blue-400' : 'text-red-500 dark:text-red-400' }}">
                    Rp {{ number_format(abs($selisih), 0, ',', '.') }}
                    — {{ $selisih > 0 ? 'dicatat sebagai transfer ke rekening' : 'kekurangan' }}
                </p>
            </div>
            @endif
        </div>
    </div>
    @endif

    {{-- Berita Acara --}}
    @if($transaksi->kencleng && $transaksi->kencleng->berita_acara)
    <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-6">
        <h2 class="mb-4 text-base font-semibold text-gray-900 dark:text-white">Berita acara</h2>
        <x-approval.file-link
            :url="Storage::url($transaksi->kencleng->berita_acara)"
            :name="basename($transaksi->kencleng->berita_acara)" />
    </div>
    @endif
</div>
@endsection

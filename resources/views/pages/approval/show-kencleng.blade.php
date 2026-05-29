@extends('layouts.app')

@section('content')
<div class="p-6 space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 px-6 py-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('dashboard.approval.index') }}"
               class="p-1.5 text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <h1 class="text-lg font-semibold text-gray-900 dark:text-white">Detail Kencleng</h1>
        </div>
    </div>

    {{-- Action Bar --}}
    <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl px-6 py-4 flex items-center justify-between flex-wrap gap-4">
        <div>
            <p class="font-medium text-gray-900 dark:text-white">Transaksi ini menunggu persetujuan kamu</p>
            <p class="text-sm text-gray-500 dark:text-gray-400">Periksa detail di bawah sebelum approve, revisi, atau reject</p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" onclick="togglePanel('panel-revision')"
                    class="inline-flex items-center gap-1.5 text-sm font-medium px-4 py-2 rounded-lg border border-blue-500 text-blue-600 dark:text-blue-400 dark:border-blue-500 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors">
                Minta Revisi
            </button>
            <button type="button" onclick="togglePanel('panel-reject')"
                    class="inline-flex items-center gap-1.5 text-sm font-medium px-4 py-2 rounded-lg border border-red-500 text-red-600 dark:text-red-400 dark:border-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                Reject
            </button>
            <form action="{{ route('dashboard.approval.approve', $transaksi) }}" method="POST"
                  onsubmit="return confirm('Yakin menyetujui transaksi kencleng ini?')">
                @csrf
                <button type="submit"
                        class="inline-flex items-center gap-1.5 text-sm font-medium px-4 py-2 rounded-lg border border-green-600 text-green-700 dark:text-green-400 dark:border-green-600 hover:bg-green-50 dark:hover:bg-green-900/20 transition-colors">
                    Approve
                </button>
            </form>
        </div>
    </div>

    {{-- Inline Panel: Reject --}}
    <div id="panel-reject"
         class="hidden bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-2xl p-5">
        <p class="mb-3 font-medium text-red-700 dark:text-red-400">Alasan penolakan</p>
        <form action="{{ route('dashboard.approval.reject', $transaksi) }}" method="POST">
            @csrf
            <textarea name="catatan" rows="3"
                      placeholder="Tuliskan alasan reject transaksi ini..."
                      class="mb-3 w-full rounded-xl border border-red-200 dark:border-red-800 bg-white dark:bg-gray-800 px-3 py-2 text-sm text-gray-700 dark:text-gray-300 focus:border-red-400 focus:outline-none placeholder-gray-400"></textarea>
            <div class="flex justify-end gap-2">
                <button type="button" onclick="togglePanel('panel-reject')"
                        class="px-4 py-2 text-sm text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                    Batal
                </button>
                <button type="submit"
                        class="px-4 py-2 text-sm font-medium border border-red-500 text-red-600 dark:text-red-400 dark:border-red-500 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                    Konfirmasi Reject
                </button>
            </div>
        </form>
    </div>

    {{-- Inline Panel: Revisi --}}
    <div id="panel-revision"
         class="hidden bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-2xl p-5">
        <p class="mb-3 font-medium text-blue-700 dark:text-blue-400">Catatan revisi</p>
        <form action="{{ route('dashboard.approval.revision', $transaksi) }}" method="POST">
            @csrf
            <textarea name="catatan" rows="3" required
                      placeholder="Tuliskan catatan yang perlu diperbaiki..."
                      class="mb-3 w-full rounded-xl border border-blue-200 dark:border-blue-800 bg-white dark:bg-gray-800 px-3 py-2 text-sm text-gray-700 dark:text-gray-300 focus:border-blue-400 focus:outline-none placeholder-gray-400"></textarea>
            <div class="flex justify-end gap-2">
                <button type="button" onclick="togglePanel('panel-revision')"
                        class="px-4 py-2 text-sm text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                    Batal
                </button>
                <button type="submit"
                        class="px-4 py-2 text-sm font-medium border border-blue-500 text-blue-600 dark:text-blue-400 dark:border-blue-500 rounded-lg hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors">
                    Kirim Permintaan Revisi
                </button>
            </div>
        </form>
    </div>

    {{-- Informasi Transaksi --}}
    <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-6">
        <div class="mb-5 flex items-center justify-between">
            <h2 class="text-base font-semibold text-gray-900 dark:text-white">Informasi transaksi</h2>
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-50 dark:bg-yellow-900/20 text-yellow-600 dark:text-yellow-400">
                Pending
            </span>
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
                <p class="text-sm text-gray-800 dark:text-gray-200">
                    {{ $transaksi->tanggal_transaksi->format('j F Y') }}
                </p>
            </div>
            @if($transaksi->kencleng->nomor_kwitansi ?? null)
            <div>
                <p class="mb-1 text-xs text-gray-500 dark:text-gray-400">Nomor kwitansi</p>
                <p class="font-mono text-sm font-medium text-gray-800 dark:text-gray-200">
                    {{ $transaksi->kencleng->nomor_kwitansi }}
                </p>
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
    </div>

    {{-- Rincian Pecahan --}}
    @if($transaksi->kencleng && $transaksi->kencleng->detail->count() > 0)
    @php
        $details    = $transaksi->kencleng->detail;
        $totalFisik = $details->sum(fn($d) => $d->pecahan * $d->jumlah_pecahan);
        $jumlahSetor= (float) $transaksi->jumlah;
        $selisih    = $totalFisik - $jumlahSetor;
        $sorted     = $details->sortBy('pecahan');
    @endphp
    <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-6">
        <h2 class="mb-5 text-base font-semibold text-gray-900 dark:text-white">Rincian pecahan uang</h2>

        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
            @foreach($sorted as $detail)
            @php $subtotal = $detail->pecahan * $detail->jumlah_pecahan; @endphp
            <div class="rounded-xl border border-gray-200 dark:border-gray-700 p-4">
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    Rp {{ number_format($detail->pecahan, 0, ',', '.') }}
                </p>
                <p class="text-sm font-medium text-green-600 dark:text-green-400">
                    Rp {{ number_format($subtotal, 0, ',', '.') }}
                </p>
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
                <p class="text-lg font-bold text-green-700 dark:text-green-400">
                    Rp {{ number_format($totalFisik, 0, ',', '.') }}
                </p>
            </div>
        </div>

        <div class="mt-4 space-y-2 border-t border-gray-100 dark:border-gray-800 pt-4">
            <div class="flex items-center justify-between text-sm">
                <p class="text-gray-500 dark:text-gray-400">Jumlah disetor ke kas</p>
                <p class="font-medium text-gray-800 dark:text-gray-200">
                    Rp {{ number_format($jumlahSetor, 0, ',', '.') }}
                </p>
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
        @php
            $ba  = $transaksi->kencleng->berita_acara;
            $ext = strtoupper(pathinfo($ba, PATHINFO_EXTENSION));
        @endphp
        <a href="{{ Storage::url($ba) }}" target="_blank"
           class="flex items-center gap-3 rounded-xl border border-gray-200 dark:border-gray-700 px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
            <svg class="h-4 w-4 shrink-0 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                <polyline points="14 2 14 8 20 8"/>
            </svg>
            <span class="text-sm text-gray-700 dark:text-gray-300">{{ basename($ba) }}</span>
            <span class="ml-auto inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                {{ $ext === 'PDF' ? 'bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400' : 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400' }}">
                {{ $ext }}
            </span>
        </a>
    </div>
    @endif

</div>

@push('scripts')
<script>
    function togglePanel(id) {
        const panels = ['panel-reject', 'panel-revision'];
        panels.forEach(p => {
            if (p === id) {
                document.getElementById(p).classList.toggle('hidden');
            } else {
                document.getElementById(p).classList.add('hidden');
            }
        });
        if (!document.getElementById(id).classList.contains('hidden')) {
            document.getElementById(id).scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
    }
</script>
@endpush
@endsection
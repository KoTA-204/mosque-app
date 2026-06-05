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
            <h1 class="text-lg font-semibold text-gray-900 dark:text-white">Detail Transaksi</h1>
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
                  onsubmit="return confirm('Yakin menyetujui transaksi ini?')">
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
        <p class="mb-3 font-medium text-blue-700 dark:text-blue-400">Catatan revisi untuk panitia</p>
        <form action="{{ route('dashboard.approval.revision', $transaksi) }}" method="POST">
            @csrf
            <textarea name="catatan" rows="3" required
                      placeholder="Tuliskan catatan yang perlu diperbaiki panitia..."
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

        @php $jenis = $transaksi->kategoriTransaksi->jenis_transaksi; @endphp

        <div class="grid grid-cols-2 gap-x-8 gap-y-5 md:grid-cols-3">
            <div>
                <p class="mb-1 text-xs text-gray-500 dark:text-gray-400">Kode transaksi</p>
                <p class="font-mono text-sm font-medium text-gray-800 dark:text-gray-200">
                    TRX-{{ now()->year }}-{{ str_pad($transaksi->id, 3, '0', STR_PAD_LEFT) }}
                </p>
            </div>
            <div>
                <p class="mb-1 text-xs text-gray-500 dark:text-gray-400">Tanggal</p>
                <p class="text-sm text-gray-800 dark:text-gray-200">
                    {{ $transaksi->tanggal_transaksi->format('j F Y') }}
                </p>
            </div>
            <div>
                <p class="mb-1 text-xs text-gray-500 dark:text-gray-400">Jenis</p>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                    {{ $jenis === 'PEMASUKAN'
                        ? 'bg-purple-50 dark:bg-purple-900/20 text-purple-600 dark:text-purple-400'
                        : 'bg-pink-50 dark:bg-pink-900/20 text-pink-600 dark:text-pink-400' }}">
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

        {{-- Bukti Transaksi --}}
        @if($transaksi->buktiTransaksi->count() > 0)
        <div class="mt-5 border-t border-gray-100 dark:border-gray-800 pt-5">
            <p class="mb-3 text-xs text-gray-500 dark:text-gray-400">Bukti transaksi</p>
            <div class="flex flex-col gap-2">
                @foreach($transaksi->buktiTransaksi as $bukti)
                @php $ext = strtoupper(pathinfo($bukti->nama_file, PATHINFO_EXTENSION)); @endphp
                <a href="{{ Storage::url($bukti->path_file) }}" target="_blank"
                   class="flex items-center gap-3 rounded-xl border border-gray-200 dark:border-gray-700 px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                    <svg class="h-4 w-4 shrink-0 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <polyline points="14 2 14 8 20 8"/>
                    </svg>
                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ $bukti->nama_file }}</span>
                    <span class="ml-auto inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                        {{ $ext === 'PDF' ? 'bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400' : 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400' }}">
                        {{ $ext }}
                    </span>
                </a>
                @endforeach
            </div>
        </div>
        @endif
    </div>

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
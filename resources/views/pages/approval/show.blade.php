@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-screen-2xl p-4 md:p-6 2xl:p-10">

    {{-- Header --}}
    <div class="mb-6 rounded-xl border border-stroke bg-white p-6 shadow-default dark:border-strokedark dark:bg-boxdark">
        <h2 class="text-2xl font-bold text-dark dark:text-white">Detail Transaksi</h2>
    </div>

    {{-- Kembali --}}
    <a href="{{ route('dashboard.approval.index') }}"
       class="mb-4 inline-block text-sm text-body hover:text-primary dark:text-bodydark">
        Kembali
    </a>

    {{-- Action Bar --}}
    <div class="mb-4 flex items-center justify-between rounded-xl border border-stroke bg-white px-5 py-4 shadow-default dark:border-strokedark dark:bg-boxdark">
        <div>
            <p class="font-medium text-black dark:text-white">Transaksi ini menunggu persetujuan kamu</p>
            <p class="text-sm text-body dark:text-bodydark">Periksa detail di bawah sebelum approve, revisi, atau reject</p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" onclick="togglePanel('panel-revision')"
                    id="btn-revision"
                    class="rounded-lg border border-blue-500 px-4 py-2 text-sm font-medium text-blue-600 hover:bg-blue-50 transition-colors dark:hover:bg-meta-4">
                Minta Revisi
            </button>
            <button type="button" onclick="togglePanel('panel-reject')"
                    id="btn-reject"
                    class="rounded-lg border border-red-500 px-4 py-2 text-sm font-medium text-red-600 hover:bg-red-50 transition-colors dark:hover:bg-meta-4">
                Reject
            </button>
            <form action="{{ route('dashboard.approval.approve', $transaksi) }}" method="POST"
                  onsubmit="return confirm('Yakin menyetujui transaksi ini?')">
                @csrf
                <button type="submit"
                        class="rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700 transition-colors">
                    Approve
                </button>
            </form>
        </div>
    </div>

    {{-- Inline Panel: Reject --}}
    <div id="panel-reject"
         class="mb-4 hidden rounded-xl border border-red-200 bg-red-50 p-5 dark:border-red-900/50 dark:bg-red-900/20">
        <p class="mb-3 font-medium text-red-700 dark:text-red-400">Alasan penolakan</p>
        <form action="{{ route('dashboard.approval.reject', $transaksi) }}" method="POST">
            @csrf
            <textarea name="catatan" rows="3"
                      placeholder="Tuliskan alasan reject transaksi ini..."
                      class="mb-3 w-full rounded-lg border border-red-200 bg-white px-3 py-2 text-sm focus:border-red-400 focus:outline-none dark:border-red-900/50 dark:bg-boxdark dark:text-white"></textarea>
            <div class="flex justify-end gap-2">
                <button type="button" onclick="togglePanel('panel-reject')"
                        class="rounded-lg border border-stroke px-4 py-2 text-sm hover:bg-gray-100 dark:border-strokedark dark:hover:bg-meta-4">
                    Batal
                </button>
                <button type="submit"
                        class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700">
                    Konfirmasi Reject
                </button>
            </div>
        </form>
    </div>

    {{-- Inline Panel: Revisi --}}
    <div id="panel-revision"
         class="mb-4 hidden rounded-xl border border-blue-200 bg-blue-50 p-5 dark:border-blue-900/50 dark:bg-blue-900/20">
        <p class="mb-3 font-medium text-blue-700 dark:text-blue-400">Catatan revisi untuk panitia</p>
        <form action="{{ route('dashboard.approval.revision', $transaksi) }}" method="POST">
            @csrf
            <textarea name="catatan" rows="3" required
                      placeholder="Tuliskan catatan yang perlu diperbaiki panitia..."
                      class="mb-3 w-full rounded-lg border border-blue-200 bg-white px-3 py-2 text-sm focus:border-blue-400 focus:outline-none dark:border-blue-900/50 dark:bg-boxdark dark:text-white"></textarea>
            <div class="flex justify-end gap-2">
                <button type="button" onclick="togglePanel('panel-revision')"
                        class="rounded-lg border border-stroke px-4 py-2 text-sm hover:bg-gray-100 dark:border-strokedark dark:hover:bg-meta-4">
                    Batal
                </button>
                <button type="submit"
                        class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                    Kirim Permintaan Revisi
                </button>
            </div>
        </form>
    </div>

    {{-- Informasi Transaksi --}}
    <div class="rounded-xl border border-stroke bg-white p-6 shadow-default dark:border-strokedark dark:bg-boxdark">
        <div class="mb-5 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-black dark:text-white">Informasi transaksi</h3>
            <span class="rounded-full bg-yellow-100 px-3 py-1 text-sm font-medium text-yellow-700">Pending</span>
        </div>

        @php $jenis = $transaksi->kategoriTransaksi->jenis_transaksi; @endphp

        <div class="grid grid-cols-2 gap-x-8 gap-y-5 md:grid-cols-3">
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
                <p class="text-sm text-black dark:text-white">{{ $transaksi->kegiatan->nama_kegiatan ?? '-' }}</p>
            </div>
            <div>
                <p class="mb-1 text-xs text-body dark:text-bodydark">Dicatat oleh</p>
                <p class="text-sm text-black dark:text-white">
                    {{ $transaksi->user->name }} · {{ $transaksi->created_at->format('j M Y H.i') }}
                </p>
            </div>
        </div>

        @if($transaksi->deskripsi)
        <div class="mt-5 border-t border-stroke pt-5 dark:border-strokedark">
            <p class="mb-1 text-xs text-body dark:text-bodydark">Deskripsi</p>
            <p class="text-sm text-black dark:text-white">{{ $transaksi->deskripsi }}</p>
        </div>
        @endif

        {{-- Bukti Transaksi --}}
        @if($transaksi->buktiTransaksi->count() > 0)
        <div class="mt-5 border-t border-stroke pt-5 dark:border-strokedark">
            <p class="mb-3 text-xs text-body dark:text-bodydark">Bukti transaksi</p>
            <div class="flex flex-col gap-2">
                @foreach($transaksi->buktiTransaksi as $bukti)
                @php $ext = strtoupper(pathinfo($bukti->nama_file, PATHINFO_EXTENSION)); @endphp
                <a href="{{ Storage::url($bukti->path_file) }}" target="_blank"
                   class="flex items-center gap-3 rounded-lg border border-stroke px-4 py-3 hover:bg-gray-50 dark:border-strokedark dark:hover:bg-meta-4 transition-colors">
                    <svg class="h-4 w-4 shrink-0 text-body dark:text-bodydark" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <polyline points="14 2 14 8 20 8"/>
                    </svg>
                    <span class="text-sm text-black dark:text-white">{{ $bukti->nama_file }}</span>
                    <span class="ml-auto rounded px-2 py-0.5 text-xs font-medium
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

        // Scroll ke panel yang terbuka
        if (!document.getElementById(id).classList.contains('hidden')) {
            document.getElementById(id).scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
    }
</script>
@endpush
@endsection
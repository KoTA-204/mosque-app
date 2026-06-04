@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-screen-2xl p-4 md:p-6 2xl:p-10">

    {{-- Header --}}
    <div class="mb-6 rounded-xl border border-stroke bg-white p-6 shadow-default dark:border-strokedark dark:bg-boxdark">
        <h2 class="text-2xl font-bold text-dark dark:text-white">Detail Kencleng</h2>
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
                    class="rounded-lg border border-blue-500 px-4 py-2 text-sm font-medium text-blue-600 hover:bg-blue-50 transition-colors dark:hover:bg-meta-4">
                Minta Revisi
            </button>
            <button type="button" onclick="togglePanel('panel-reject')"
                    class="rounded-lg border border-red-500 px-4 py-2 text-sm font-medium text-red-600 hover:bg-red-50 transition-colors dark:hover:bg-meta-4">
                Reject
            </button>
            <form action="{{ route('dashboard.approval.approve', $transaksi) }}" method="POST"
                  onsubmit="return confirm('Yakin menyetujui transaksi kencleng ini?')">
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
        <p class="mb-3 font-medium text-blue-700 dark:text-blue-400">Catatan revisi</p>
        <form action="{{ route('dashboard.approval.revision', $transaksi) }}" method="POST">
            @csrf
            <textarea name="catatan" rows="3" required
                      placeholder="Tuliskan catatan yang perlu diperbaiki..."
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
    <div class="mb-6 rounded-xl border border-stroke bg-white p-6 shadow-default dark:border-strokedark dark:bg-boxdark">
        <div class="mb-5 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-black dark:text-white">Informasi transaksi</h3>
            <span class="rounded-full bg-yellow-100 px-3 py-1 text-sm font-medium text-yellow-700">Pending</span>
        </div>

        <div class="grid grid-cols-2 gap-x-8 gap-y-5 md:grid-cols-3">
            <div>
                <p class="mb-1 text-xs text-body dark:text-bodydark">Kode transaksi</p>
                <p class="font-mono text-sm font-medium text-black dark:text-white">
                    TRX-{{ now()->year }}-{{ str_pad($transaksi->id, 3, '0', STR_PAD_LEFT) }}
                </p>
            </div>
            <div>
                <p class="mb-1 text-xs text-body dark:text-bodydark">Tanggal hitung</p>
                <p class="text-sm text-black dark:text-white">
                    {{ $transaksi->tanggal_transaksi->format('j F Y') }}
                </p>
            </div>
            @if($transaksi->kencleng->nomor_kwitansi ?? null)
            <div>
                <p class="mb-1 text-xs text-body dark:text-bodydark">Nomor kwitansi</p>
                <p class="font-mono text-sm font-medium text-black dark:text-white">
                    {{ $transaksi->kencleng->nomor_kwitansi }}
                </p>
            </div>
            @endif
            <div>
                <p class="mb-1 text-xs text-body dark:text-bodydark">Dompet</p>
                <p class="text-sm text-black dark:text-white">{{ $transaksi->dompet->nama_dompet }}</p>
            </div>
            <div>
                <p class="mb-1 text-xs text-body dark:text-bodydark">Dicatat oleh</p>
                <p class="text-sm text-black dark:text-white">
                    {{ $transaksi->user->name }} · {{ $transaksi->created_at->format('j M Y H.i') }}
                </p>
            </div>
            <div>
                <p class="mb-1 text-xs text-body dark:text-bodydark">Jumlah disetor</p>
                <p class="text-sm font-semibold text-green-600">
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

        // urut pecahan dari terkecil
        $sorted = $details->sortBy('pecahan');
    @endphp
    <div class="mb-6 rounded-xl border border-stroke bg-white p-6 shadow-default dark:border-strokedark dark:bg-boxdark">
        <h3 class="mb-5 text-lg font-semibold text-black dark:text-white">Rincian pecahan uang</h3>

        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
            @foreach($sorted as $detail)
            @php
                $subtotal = $detail->pecahan * $detail->jumlah_pecahan;
            @endphp
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

        {{-- Ringkasan total --}}
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
    @if($transaksi->kencleng && $transaksi->kencleng->berita_acara)
    <div class="rounded-xl border border-stroke bg-white p-6 shadow-default dark:border-strokedark dark:bg-boxdark">
        <h3 class="mb-4 text-lg font-semibold text-black dark:text-white">Berita acara</h3>
        @php
            $ba  = $transaksi->kencleng->berita_acara;
            $ext = strtoupper(pathinfo($ba, PATHINFO_EXTENSION));
        @endphp
        <a href="{{ Storage::url($ba) }}" target="_blank"
           class="flex items-center gap-3 rounded-lg border border-stroke px-4 py-3 hover:bg-gray-50 dark:border-strokedark dark:hover:bg-meta-4 transition-colors">
            <svg class="h-4 w-4 shrink-0 text-body dark:text-bodydark" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                <polyline points="14 2 14 8 20 8"/>
            </svg>
            <span class="text-sm text-black dark:text-white">{{ basename($ba) }}</span>
            <span class="ml-auto rounded px-2 py-0.5 text-xs font-medium
                {{ $ext === 'PDF' ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-700' }}">
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
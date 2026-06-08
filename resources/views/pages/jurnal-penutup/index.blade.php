@extends('layouts.app')

@section('title', 'Jurnal Penutup')

@section('content')
<div class="p-6 space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 px-6 py-4">
        <div>
            <h1 class="text-lg font-semibold text-gray-900 dark:text-white">Jurnal Penutup</h1>
            @if($periodeAktif)
            <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">
                Periode aktif: {{ $periodeAktif->nama_periode }}
            </p>
            @endif
        </div>
        <a href="{{ route('dashboard.jurnal-penutup.create') }}"
           class="inline-flex items-center gap-2 border border-green-600 text-green-700 dark:text-green-400 dark:border-green-600 text-sm font-medium px-4 py-2 rounded-lg hover:bg-green-50 dark:hover:bg-green-900/20 transition-colors">
            Mulai Penutupan
        </a>
    </div>

    {{-- Alert --}}
    @if(session('success'))
    <div class="flex items-center gap-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl px-4 py-3 text-sm text-green-700 dark:text-green-400">
        <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
        </svg>
        {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="flex items-center gap-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl px-4 py-3 text-sm text-red-700 dark:text-red-400">
        <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
        </svg>
        {{ session('error') }}
    </div>
    @endif

    {{-- Status Penutupan Periode Aktif --}}
    @if($periodeAktif)
    @php
        $tipes = ['TUTUP_PENDAPATAN', 'TUTUP_BEBAN', 'IKHTISAR_LR', 'TUTUP_SALDO_DANA'];
        $labelsTahap = [
            'TUTUP_PENDAPATAN' => 'Tutup Pendapatan',
            'TUTUP_BEBAN'      => 'Tutup Beban',
            'IKHTISAR_LR'      => 'Ikhtisar Laba/Rugi',
            'TUTUP_SALDO_DANA' => 'Tutup ke Saldo Dana',
        ];
        $sisaTahap = 4 - $tahapSelesai;
        $pct = ($tahapSelesai / 4) * 100;
        $statusLabel = $tahapSelesai === 4
            ? 'Selesai — semua tahap telah diposting'
            : 'Belum selesai — ' . $tahapSelesai . ' dari 4 tahap selesai';
    @endphp
    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 px-6 py-5">
        <div class="flex items-start justify-between mb-4">
            <div>
                <p class="text-xs text-gray-400 uppercase tracking-wider mb-0.5">Status Penutupan — {{ $periodeAktif->nama_periode }}</p>
                <p class="text-sm font-semibold text-gray-800 dark:text-gray-200">{{ $statusLabel }}</p>
            </div>
            <div class="flex items-center gap-2">
                <div class="text-center px-3 py-1.5 rounded-xl bg-green-50 dark:bg-green-900/20 border border-green-100 dark:border-green-800">
                    <p class="text-lg font-bold text-green-600">{{ $tahapSelesai }}</p>
                    <p class="text-xs text-gray-400">Selesai</p>
                </div>
                @if($sisaTahap > 0)
                <div class="text-center px-3 py-1.5 rounded-xl bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-100 dark:border-yellow-800">
                    <p class="text-lg font-bold text-yellow-600">{{ $sisaTahap }}</p>
                    <p class="text-xs text-gray-400">Tersisa</p>
                </div>
                @endif
            </div>
        </div>

        {{-- Progress bar --}}
        <div class="w-full bg-gray-100 dark:bg-gray-800 rounded-full h-2 mb-4">
            <div class="bg-green-600 h-2 rounded-full transition-all duration-500"
                 style="width: {{ $pct }}%"></div>
        </div>

        {{-- Tahap cards --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
            @foreach($tipes as $i => $tipe)
            @php $st = $statusTahap[$tipe] ?? ['selesai' => false, 'ada' => false]; @endphp
            <div class="rounded-xl border px-3 py-2.5 {{ $st['selesai'] ? 'border-green-200 dark:border-green-800 bg-green-50 dark:bg-green-900/10' : 'border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/30' }}">
                <div class="flex items-center gap-1.5 mb-1">
                    @if($st['selesai'])
                        <svg class="w-3.5 h-3.5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                    @else
                        <span class="flex h-3.5 w-3.5 items-center justify-center rounded-full bg-gray-200 dark:bg-gray-700 text-xs font-bold text-gray-500">{{ $i + 1 }}</span>
                    @endif
                    <span class="text-xs font-medium {{ $st['selesai'] ? 'text-green-700 dark:text-green-400' : 'text-gray-500 dark:text-gray-400' }}">
                        Tahap {{ $i + 1 }}
                    </span>
                </div>
                <p class="text-xs {{ $st['selesai'] ? 'text-green-800 dark:text-green-300' : 'text-gray-600 dark:text-gray-400' }} font-medium">
                    {{ $labelsTahap[$tipe] }}
                </p>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Table --}}
    <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl overflow-hidden">

        {{-- Toolbar --}}
        <div class="flex items-center justify-between gap-3 px-5 py-4 border-b border-gray-100 dark:border-gray-800 flex-wrap">

            <form method="GET" action="{{ route('dashboard.jurnal-penutup.index') }}" id="perPageForm"
                class="flex items-center gap-2">
                <input type="hidden" name="search"     value="{{ $search }}">
                <input type="hidden" name="periode_id" value="{{ $periodeId }}">
                <input type="hidden" name="status"     value="{{ $status }}">
                <span class="text-sm text-gray-500 dark:text-gray-400">Show</span>
                <select name="per_page" onchange="document.getElementById('perPageForm').submit()"
                        class="text-sm border border-gray-200 dark:border-gray-700 rounded-lg px-2 py-1.5 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 outline-none focus:border-green-400">
                    @foreach([10, 25, 50] as $val)
                        <option value="{{ $val }}" {{ $perPage == $val ? 'selected' : '' }}>{{ $val }}</option>
                    @endforeach
                </select>
                <span class="text-sm text-gray-500 dark:text-gray-400">entries</span>
            </form>

            <form method="GET" action="{{ route('dashboard.jurnal-penutup.index') }}"
                class="flex items-center gap-2 flex-wrap" id="filterForm">
                <input type="hidden" name="per_page" value="{{ $perPage }}">

                <select name="periode_id" onchange="document.getElementById('filterForm').submit()"
                        class="text-sm border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-1.5 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 outline-none focus:border-green-400">
                    <option value="">Pilih Periode</option>
                    @foreach($periodeList as $periode)
                        <option value="{{ $periode->id }}" {{ $periodeId == $periode->id ? 'selected' : '' }}>
                            {{ $periode->nama_periode }}
                        </option>
                    @endforeach
                </select>

                <select name="status" onchange="document.getElementById('filterForm').submit()"
                        class="text-sm border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-1.5 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 outline-none focus:border-green-400">
                    <option value="">Pilih Status</option>
                    <option value="DRAFT"  {{ $status === 'DRAFT'  ? 'selected' : '' }}>Draft</option>
                    <option value="POSTED" {{ $status === 'POSTED' ? 'selected' : '' }}>Posted</option>
                </select>

                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text" name="search" value="{{ $search }}"
                        placeholder="Search..."
                        class="pl-9 pr-4 py-1.5 text-sm border border-gray-200 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 outline-none focus:border-green-400 w-48 placeholder-gray-400">
                </div>
            </form>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-800">
                        <th class="text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider px-5 py-3">Nomor</th>
                        <th class="text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider px-4 py-3">Tanggal</th>
                        <th class="text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider px-4 py-3">Periode</th>
                        <th class="text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider px-4 py-3">Tipe Penutupan</th>
                        <th class="text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider px-4 py-3">Debit</th>
                        <th class="text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider px-4 py-3">Kredit</th>
                        <th class="text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider px-4 py-3">Status</th>
                        <th class="text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider px-5 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
                    @forelse($jurnal as $item)
                    @php
                        $totalDebit  = $item->detailJurnal->where('tipe', 'DEBIT')->sum('nominal');
                        $totalKredit = $item->detailJurnal->where('tipe', 'KREDIT')->sum('nominal');
                        $isPosted    = $item->status === 'POSTED';
                        $nomorJurnal = 'JPT-'
                            . $item->periode->tanggal_awal->format('Y') . '-'
                            . $item->periode->tanggal_awal->format('m') . '-'
                            . str_pad($jurnal->firstItem() + $loop->index, 3, '0', STR_PAD_LEFT);
                        $tipeLabels = [
                            'TUTUP_PENDAPATAN' => 'Tutup Pendapatan',
                            'TUTUP_BEBAN'      => 'Tutup Beban',
                            'IKHTISAR_LR'      => 'Ikhtisar Laba/Rugi',
                            'TUTUP_SALDO_DANA' => 'Tutup ke Saldo Dana',
                        ];
                    @endphp
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/40 transition-colors" id="row-{{ $item->id }}">
                        <td class="px-5 py-3.5 font-mono text-sm font-medium text-green-600 dark:text-green-400 cursor-pointer"
                            onclick="showDrawer({{ $item->id }})">
                            {{ $nomorJurnal }}
                        </td>
                        <td class="px-4 py-3.5 text-gray-500 dark:text-gray-400 cursor-pointer"
                            onclick="showDrawer({{ $item->id }})">
                            {{ $item->tanggal->format('j M Y') }}
                        </td>
                        <td class="px-4 py-3.5 text-gray-700 dark:text-gray-300 cursor-pointer"
                            onclick="showDrawer({{ $item->id }})">
                            {{ $item->periode->nama_periode ?? '—' }}
                        </td>
                        <td class="px-4 py-3.5 cursor-pointer"
                            onclick="showDrawer({{ $item->id }})">
                            <span class="text-sm font-medium text-gray-800 dark:text-gray-200">
                                {{ $tipeLabels[$item->tipe_penyesuaian] ?? $item->tipe_penyesuaian ?? '—' }}
                            </span>
                        </td>
                        <td class="px-4 py-3.5 text-right font-medium text-gray-800 dark:text-gray-200 cursor-pointer"
                            onclick="showDrawer({{ $item->id }})">
                            Rp {{ number_format($totalDebit, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3.5 text-right font-medium text-gray-800 dark:text-gray-200 cursor-pointer"
                            onclick="showDrawer({{ $item->id }})">
                            Rp {{ number_format($totalKredit, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3.5 text-center cursor-pointer"
                            onclick="showDrawer({{ $item->id }})">
                            @if($isPosted)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-50 dark:bg-green-900/20 text-green-600 dark:text-green-400">
                                    Posted
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-50 dark:bg-yellow-900/20 text-yellow-600 dark:text-yellow-400">
                                    Draft
                                </span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5" onclick="event.stopPropagation()">
                            <div class="flex items-center justify-center gap-1">
                                @if(!$isPosted)
                                <form action="{{ route('dashboard.jurnal-penutup.destroy', $item) }}" method="POST"
                                      onsubmit="return confirm('Yakin hapus jurnal penutup ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="p-1.5 text-gray-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors"
                                            title="Hapus">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                                @endif
                                <button type="button" onclick="showDrawer({{ $item->id }})"
                                        class="p-1.5 text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors"
                                        title="Detail">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-5 py-12 text-center text-sm text-gray-400 dark:text-gray-600">
                            Belum ada jurnal penutup.
                            <a href="{{ route('dashboard.jurnal-penutup.create') }}" class="text-green-600 hover:underline ml-1">Mulai penutupan</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($jurnal->hasPages())
        <div class="flex items-center justify-between px-5 py-4 border-t border-gray-100 dark:border-gray-800 flex-wrap gap-3">
            <div class="flex items-center gap-1">
                @if($jurnal->onFirstPage())
                    <span class="px-3 py-1.5 text-sm text-gray-300 dark:text-gray-600 border border-gray-200 dark:border-gray-700 rounded-lg">Previous</span>
                @else
                    <a href="{{ $jurnal->previousPageUrl() }}&search={{ $search }}&periode_id={{ $periodeId }}&status={{ $status }}&per_page={{ $perPage }}"
                       class="px-3 py-1.5 text-sm text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">Previous</a>
                @endif

                @foreach($jurnal->getUrlRange(1, $jurnal->lastPage()) as $page => $url)
                    <a href="{{ $url }}&search={{ $search }}&periode_id={{ $periodeId }}&status={{ $status }}&per_page={{ $perPage }}"
                       class="w-8 h-8 flex items-center justify-center text-sm rounded-lg transition-colors
                           {{ $page === $jurnal->currentPage() ? 'bg-green-600 text-white font-medium' : 'text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800' }}">
                        {{ $page }}
                    </a>
                @endforeach

                @if($jurnal->hasMorePages())
                    <a href="{{ $jurnal->nextPageUrl() }}&search={{ $search }}&periode_id={{ $periodeId }}&status={{ $status }}&per_page={{ $perPage }}"
                       class="px-3 py-1.5 text-sm text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">Next</a>
                @else
                    <span class="px-3 py-1.5 text-sm text-gray-300 dark:text-gray-600 border border-gray-200 dark:border-gray-700 rounded-lg">Next</span>
                @endif
            </div>
            <span class="text-xs text-gray-400">
                @if($jurnal->total() > 0)
                    Showing {{ $jurnal->firstItem() }} to {{ $jurnal->lastItem() }} of {{ $jurnal->total() }} entries
                @else
                    No entries
                @endif
            </span>
        </div>
        @endif
    </div>
</div>

{{-- Drawer Overlay --}}
<div id="drawerOverlay" class="fixed inset-0 z-40 hidden bg-black/30" onclick="closeDrawer()"></div>

{{-- Drawer --}}
<div id="drawer"
     class="fixed right-0 top-0 z-50 h-full w-full max-w-md translate-x-full transform overflow-y-auto bg-white dark:bg-gray-900 border-l border-gray-200 dark:border-gray-800 shadow-xl transition-transform duration-300">
    <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-800 px-5 py-4">
        <h3 class="text-base font-semibold text-gray-900 dark:text-white">Detail Jurnal Penutup</h3>
        <button onclick="closeDrawer()" class="p-1.5 text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>
    <div id="drawerContent" class="p-5">
        <div class="flex items-center justify-center py-10 text-gray-400 gap-2">
            <svg class="animate-spin w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 1 1-6.219-8.56"/>
            </svg>
            <span class="text-sm">Memuat...</span>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function showDrawer(id) {
    document.getElementById('drawerOverlay').classList.remove('hidden');
    document.getElementById('drawer').classList.remove('translate-x-full');
    document.getElementById('drawer').classList.add('translate-x-0');
    document.getElementById('drawerContent').innerHTML = `
        <div class="flex items-center justify-center py-10 text-gray-400 gap-2">
            <svg class="animate-spin w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 1 1-6.219-8.56"/>
            </svg>
            <span class="text-sm">Memuat...</span>
        </div>`;

    fetch(`/dashboard/jurnal-penutup/${id}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    })
    .then(r => { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
    .then(data => renderDrawer(data))
    .catch(err => {
        document.getElementById('drawerContent').innerHTML =
            `<p class="text-center text-sm text-red-500 py-10">Gagal memuat data (${err.message})</p>`;
    });
}

function closeDrawer() {
    document.getElementById('drawerOverlay').classList.add('hidden');
    document.getElementById('drawer').classList.remove('translate-x-0');
    document.getElementById('drawer').classList.add('translate-x-full');
}

function renderDrawer(data) {
    const j        = data.jurnal;
    const details  = j.detail_jurnal ?? [];
    const isPosted = j.status === 'POSTED';
    const formatRp = n => 'Rp ' + parseFloat(n || 0).toLocaleString('id-ID');

    const totalDebit  = details.filter(d => d.tipe === 'DEBIT').reduce((s, d) => s + d.nominal, 0);
    const totalKredit = details.filter(d => d.tipe === 'KREDIT').reduce((s, d) => s + d.nominal, 0);

    const statusBadge = isPosted
        ? `<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-50 text-green-600"><span class="w-1.5 h-1.5 rounded-full bg-green-500 inline-block"></span>Posted</span>`
        : `<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-50 text-yellow-600"><span class="w-1.5 h-1.5 rounded-full bg-yellow-500 inline-block"></span>Draft</span>`;

    const detailRows = details.map(d => `
        <tr class="border-b border-gray-50 dark:border-gray-800">
            <td class="py-2.5 text-sm text-gray-800 dark:text-gray-200">
                ${d.akun?.kode_akun ? `<span class="text-xs text-gray-400 mr-1">${d.akun.kode_akun}</span>` : ''}
                ${d.akun?.nama_akun ?? '—'}
            </td>
            <td class="py-2.5 text-center">
                <span class="inline-flex items-center justify-center w-5 h-5 rounded-full text-xs font-bold
                    ${d.tipe === 'DEBIT' ? 'bg-red-50 text-red-600' : 'bg-blue-50 text-blue-600'}">
                    ${d.tipe === 'DEBIT' ? 'D' : 'K'}
                </span>
            </td>
            <td class="py-2.5 text-right text-sm ${d.tipe === 'DEBIT' ? 'text-red-600 font-medium' : 'text-gray-300'}">
                ${d.tipe === 'DEBIT' ? formatRp(d.nominal) : '—'}
            </td>
            <td class="py-2.5 text-right text-sm ${d.tipe === 'KREDIT' ? 'text-green-600 font-medium' : 'text-gray-300'}">
                ${d.tipe === 'KREDIT' ? formatRp(d.nominal) : '—'}
            </td>
        </tr>`).join('');

    document.getElementById('drawerContent').innerHTML = `
        <div class="mb-5">
            <p class="font-mono text-xl font-bold text-green-600 dark:text-green-400 mb-1">${j.nomor_jurnal ?? '—'}</p>
            <div class="flex items-center gap-2">
                <span class="text-sm text-gray-400">${j.tanggal ?? '—'}</span>
                ${statusBadge}
            </div>
        </div>

        <div class="mb-5">
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 mb-3">Informasi Jurnal</p>
            <div class="bg-gray-50 dark:bg-gray-800/50 rounded-xl px-4 py-3 space-y-2.5">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-400">Periode</span>
                    <span class="text-sm font-medium text-gray-800 dark:text-gray-200">${j.periode?.nama_periode ?? '—'}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-400">Tipe Penutupan</span>
                    <span class="text-sm font-medium text-gray-800 dark:text-gray-200">${j.label_penutupan ?? '—'}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-400">Tanggal</span>
                    <span class="text-sm font-medium text-gray-800 dark:text-gray-200">${j.tanggal ?? '—'}</span>
                </div>
            </div>
        </div>

        <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 mb-3">Entri Jurnal</p>
            <table class="w-full border-collapse">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-800">
                        <th class="pb-2 text-left text-xs font-semibold text-gray-400">Akun</th>
                        <th class="pb-2 text-center text-xs font-semibold text-gray-400">Pos.</th>
                        <th class="pb-2 text-right text-xs font-semibold text-gray-400">Debit</th>
                        <th class="pb-2 text-right text-xs font-semibold text-gray-400">Kredit</th>
                    </tr>
                </thead>
                <tbody>${detailRows}</tbody>
                <tfoot>
                    <tr class="border-t-2 border-gray-100 dark:border-gray-800">
                        <td colspan="2" class="pt-3 text-sm font-semibold text-gray-800 dark:text-gray-200">Total</td>
                        <td class="pt-3 text-right text-sm font-bold text-red-600">${formatRp(totalDebit)}</td>
                        <td class="pt-3 text-right text-sm font-bold text-green-600">${formatRp(totalKredit)}</td>
                    </tr>
                </tfoot>
            </table>
        </div>`;
}
</script>
@endpush
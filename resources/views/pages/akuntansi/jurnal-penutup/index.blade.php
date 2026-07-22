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
        <div class="flex items-center gap-2">
            {{-- Multi-periode: buka periode berikutnya secara manual --}}
            <form action="{{ route('dashboard.jurnal-penutup.buka-periode') }}" method="POST">
                @csrf
                <button type="submit"
                        onclick="event.preventDefault(); confirmAction({ title: 'Buka Periode Berikutnya', message: 'Buka periode bulan berikutnya? Periode ini bisa terbuka bersamaan dengan periode berjalan sehingga transaksi bulan baru dapat langsung dicatat.', confirmLabel: 'Buka Periode', confirmClass: 'bg-green-600 hover:bg-green-700', onConfirm: () => this.closest('form').submit() })"
                        class="inline-flex items-center gap-2 border border-blue-600 text-blue-700 dark:text-blue-400 dark:border-blue-600 text-sm font-medium px-4 py-2 rounded-lg hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors">
                    Buka Periode Berikutnya
                </button>
            </form>

            @if(!$periodeAktif || $tahapSelesai < 3)
                <a href="{{ route('dashboard.jurnal-penutup.create') }}"
                class="inline-flex items-center gap-2 border border-green-600 text-green-700 dark:text-green-400 dark:border-green-600 text-sm font-medium px-4 py-2 rounded-lg hover:bg-green-50 dark:hover:bg-green-900/20 transition-colors">
                    Mulai Penutupan
                </a>
            @endif
        </div>
    </div>

    {{-- Alert --}}
    @if(session('success'))
        <x-jurnal.alert type="success" :message="session('success')" />
    @endif
    @if(session('error'))
        <x-jurnal.alert type="error" :message="session('error')" />
    @endif

    {{-- Status Penutupan Periode Aktif --}}
    @if($periodeAktif)
    @php
        $tipes = ['TUTUP_PENDAPATAN', 'TUTUP_BEBAN', 'PELEPASAN_PEMBATASAN'];
        $labelsTahap = [
            'TUTUP_PENDAPATAN' => 'Tutup Pendapatan',
            'TUTUP_BEBAN'      => 'Tutup Beban',
            'PELEPASAN_PEMBATASAN' => 'Pelepasan Pembatasan',
        ];

        $sisaTahap = 3 - $tahapSelesai;
        $pct       = ($tahapSelesai / 3) * 100;

        $statusLabel = $tahapSelesai === 3
            ? 'Selesai — semua tahap telah diposting'
            : 'Belum selesai — ' . $tahapSelesai . ' dari 3 tahap selesai';

        // Cek apakah ada DRAFT yang siap diposting (semua tahap ada sebagai DRAFT)
        $adaDraftSiapPosting = collect($tipes)->every(
            fn($t) => isset($statusTahap[$t]) && $statusTahap[$t]['ada'] && !$statusTahap[$t]['selesai']
        );
    @endphp
    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 px-6 py-5">
        <div class="flex items-start justify-between mb-4">
            <div>
                <p class="text-xs text-gray-400 uppercase tracking-wider mb-0.5">
                    Status Penutupan — {{ $periodeAktif->nama_periode }}
                </p>
                <p class="text-sm font-semibold text-gray-800 dark:text-gray-200">{{ $statusLabel }}</p>
            </div>
            <div class="flex items-center gap-2">
                <div class="text-center px-3 py-1.5 rounded-xl bg-green-50 dark:bg-green-900/20 border border-green-100 dark:border-green-800">
                    <p class="text-lg font-bold text-green-600">{{ $tahapSelesai }}</p>
                    <p class="text-xs text-gray-400">Posted</p>
                </div>
                @if($sisaTahap > 0)
                <div class="text-center px-3 py-1.5 rounded-xl bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-100 dark:border-yellow-800">
                    <p class="text-lg font-bold text-yellow-600">{{ $sisaTahap }}</p>
                    <p class="text-xs text-gray-400">Belum Posted</p>
                </div>
                @endif
            </div>
        </div>

        <div class="w-full bg-gray-100 dark:bg-gray-800 rounded-full h-2 mb-4">
            <div class="bg-green-600 h-2 rounded-full transition-all duration-500" style="width: {{ $pct }}%"></div>
        </div>

        <div class="grid grid-cols-2 gap-3 mb-4">
            @foreach($tipes as $i => $tipe)
            @php
                $st = $statusTahap[$tipe] ?? ['selesai' => false, 'ada' => false];
            @endphp
            <div class="rounded-xl border px-3 py-2.5
                {{ $st['selesai'] ? 'border-green-200 dark:border-green-800 bg-green-50 dark:bg-green-900/10' : ($st['ada'] ? 'border-yellow-200 dark:border-yellow-800 bg-yellow-50 dark:bg-yellow-900/10' : 'border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/30') }}">
                <div class="flex items-center gap-1.5 mb-1">
                    @if($st['selesai'])
                        <svg class="w-3.5 h-3.5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                    @elseif($st['ada'])
                        {{-- Ada DRAFT --}}
                        <svg class="w-3.5 h-3.5 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
                        </svg>
                    @else
                        <span class="flex h-3.5 w-3.5 items-center justify-center rounded-full bg-gray-200 dark:bg-gray-700 text-xs font-bold text-gray-500">{{ $i + 1 }}</span>
                    @endif
                    <span class="text-xs font-medium
                        {{ $st['selesai'] ? 'text-green-700 dark:text-green-400' : ($st['ada'] ? 'text-yellow-600 dark:text-yellow-400' : 'text-gray-500 dark:text-gray-400') }}">
                        @if($st['selesai']) Posted
                        @elseif($st['ada']) Draft
                        @else Belum
                        @endif
                    </span>
                </div>
                <p class="text-xs font-medium
                    {{ $st['selesai'] ? 'text-green-800 dark:text-green-300' : ($st['ada'] ? 'text-yellow-800 dark:text-yellow-300' : 'text-gray-600 dark:text-gray-400') }}">
                    {{ $labelsTahap[$tipe] }}
                </p>
            </div>
            @endforeach
        </div>

        {{-- Tombol Posting cepat — hanya muncul kalau semua tahap sudah DRAFT --}}
        @if($adaDraftSiapPosting)
        <form action="{{ route('dashboard.jurnal-penutup.post-draft') }}" method="POST">
            @csrf
            <input type="hidden" name="periode_id" value="{{ $periodeAktif->id }}">
            <button type="submit"
                    onclick="event.preventDefault(); confirmAction({ title: 'Posting Jurnal', message: 'Posting semua jurnal penutup draft ke buku besar?', confirmLabel: 'Posting', confirmClass: 'bg-green-600 hover:bg-green-700', onConfirm: () => this.closest('form').submit() })"
                    class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Posting Semua Draft
            </button>
        </form>
        @endif
    </div>
    @endif

    {{-- Table --}}
    <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl overflow-hidden">

        <x-jurnal.table-toolbar
            :route="route('dashboard.jurnal-penutup.index')"
            :per-page="$perPage"
            :search="$search"
            :hidden-params="['periode_id' => $periodeId, 'status' => $status]">

            <x-slot name="filters">
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
            </x-slot>
        </x-jurnal.table-toolbar>

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
                        $nomorJurnal = $item->kode_jurnal ?? '—';
                        $tipeLabels = [
                            'TUTUP_PENDAPATAN' => 'Tutup Pendapatan',
                            'TUTUP_BEBAN'      => 'Tutup Beban',
                            'PELEPASAN_PEMBATASAN' => 'Pelepasan Pembatasan',
                        ];
                    @endphp
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/40 transition-colors" id="row-{{ $item->id }}">
                        <td class="px-5 py-3.5 font-mono text-sm font-medium text-green-600 dark:text-green-400 cursor-pointer"
                            onclick="showDrawer('/dashboard/jurnal-penutup/{{ $item->id }}')">
                            {{ $nomorJurnal }}
                        </td>
                        <td class="px-4 py-3.5 text-gray-500 dark:text-gray-400 cursor-pointer"
                            onclick="showDrawer('/dashboard/jurnal-penutup/{{ $item->id }}')">
                            {{ $item->tanggal->format('j M Y') }}
                        </td>
                        <td class="px-4 py-3.5 text-gray-700 dark:text-gray-300 cursor-pointer"
                            onclick="showDrawer('/dashboard/jurnal-penutup/{{ $item->id }}')">
                            {{ $item->periode->nama_periode ?? '—' }}
                        </td>
                        <td class="px-4 py-3.5 cursor-pointer"
                            onclick="showDrawer('/dashboard/jurnal-penutup/{{ $item->id }}')">
                            <span class="text-sm font-medium text-gray-800 dark:text-gray-200">
                                {{ $tipeLabels[$item->tipe_penutupan] ?? $item->tipe_penutupan ?? '—' }}
                            </span>
                        </td>
                        <td class="px-4 py-3.5 text-right font-medium text-gray-800 dark:text-gray-200 cursor-pointer"
                            onclick="showDrawer('/dashboard/jurnal-penutup/{{ $item->id }}')">
                            Rp {{ number_format($totalDebit, 2, ',', '.') }}
                        </td>
                        <td class="px-4 py-3.5 text-right font-medium text-gray-800 dark:text-gray-200 cursor-pointer"
                            onclick="showDrawer('/dashboard/jurnal-penutup/{{ $item->id }}')">
                            Rp {{ number_format($totalKredit, 2, ',', '.') }}
                        </td>
                        <td class="px-4 py-3.5 text-center cursor-pointer"
                            onclick="showDrawer('/dashboard/jurnal-penutup/{{ $item->id }}')">
                            @if($isPosted)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-50 dark:bg-green-900/20 text-green-600 dark:text-green-400">Posted</span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-50 dark:bg-yellow-900/20 text-yellow-600 dark:text-yellow-400">Draft</span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5" onclick="event.stopPropagation()">
                            <div class="flex items-center justify-center gap-1">
                                @if(!$isPosted)
                                <form action="{{ route('dashboard.jurnal-penutup.destroy', $item) }}" method="POST"
                                      data-confirm="Yakin hapus jurnal penutup ini?" data-confirm-label="Hapus">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                            class="p-1.5 text-gray-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors"
                                            title="Hapus">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                                @endif
                                <button type="button"
                                        onclick="showDrawer('/dashboard/jurnal-penutup/{{ $item->id }}')"
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

        <x-jurnal.table-pagination
            :paginator="$jurnal"
            :query-params="['search' => $search, 'periode_id' => $periodeId, 'status' => $status, 'per_page' => $perPage]" />

    </div>
</div>

{{-- Drawer --}}
<x-jurnal.drawer title="Detail Jurnal Penutup" />
@endsection

@push('scripts')
<script src="{{ asset('js/jurnal-shared.js') }}?v={{ filemtime(public_path('js/jurnal-shared.js')) }}"></script>

<script>
window.renderDrawerContent = function(data) {
    const j        = data.jurnal;
    const details  = j.detail_jurnal ?? [];
    const isPosted = j.status === 'POSTED';

    document.getElementById('drawerContent').innerHTML =
        buildDrawerHeader(j.nomor_jurnal, j.tanggal, isPosted) +
        buildInfoBox('Informasi Jurnal', [
            { label: 'Periode',        value: j.periode?.nama_periode },
            { label: 'Tipe Penutupan', value: j.label_penutupan },
            { label: 'Tanggal',        value: j.tanggal },
        ]) +
        buildDetailTable(details, 'Entri Jurnal');
};
</script>
@endpush
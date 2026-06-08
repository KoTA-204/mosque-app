@extends('layouts.app')

@section('title', 'Jurnal Koreksi')

@section('content')
<div class="p-6 space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 px-6 py-4">
        <div>
            <h1 class="text-lg font-semibold text-gray-900 dark:text-white">Jurnal Koreksi</h1>
            @if($periodeAktif)
            <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">
                Periode aktif: {{ $periodeAktif->nama_periode }}
                · {{ $periodeAktif->jurnal()->koreksi()->count() }} Jurnal tercatat
            </p>
            @endif
        </div>
        @if(auth()->user()->hasPermission('CREATE_JURNAL_KOREKSI'))
        <a href="{{ route('dashboard.jurnal-koreksi.create') }}"
           class="inline-flex items-center gap-2 border border-green-600 text-green-700 dark:text-green-400 dark:border-green-600 text-sm font-medium px-4 py-2 rounded-lg hover:bg-green-50 dark:hover:bg-green-900/20 transition-colors">
            Catat Koreksi
        </a>
        @endif
    </div>

    {{-- Alert — pakai komponen --}}
    @if(session('success'))
        <x-jurnal.alert type="success" :message="session('success')" />
    @endif
    @if(session('error'))
        <x-jurnal.alert type="error" :message="session('error')" />
    @endif

    {{-- Table Container --}}
    <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl overflow-hidden">

        {{-- Bulk Action Bar — pakai komponen --}}
        <x-jurnal.bulk-action-bar permission="CREATE_JURNAL_KOREKSI" />

        {{-- Toolbar (Show entries + filter + search) — pakai komponen --}}
        <x-jurnal.table-toolbar
            :route="route('dashboard.jurnal-koreksi.index')"
            :per-page="$perPage"
            :search="$search"
            :hidden-params="['periode_id' => $periodeId, 'status' => $status]">

            <x-slot name="filters">
                <select name="periode_id" onchange="document.getElementById('filterForm').submit()"
                        class="text-sm border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-1.5 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 outline-none focus:border-green-400">
                    <option value="">Semua Periode</option>
                    @foreach($periodeList as $periode)
                        <option value="{{ $periode->id }}" {{ $periodeId == $periode->id ? 'selected' : '' }}>
                            {{ $periode->nama_periode }}
                        </option>
                    @endforeach
                </select>

                <select name="status" onchange="document.getElementById('filterForm').submit()"
                        class="text-sm border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-1.5 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 outline-none focus:border-green-400">
                    <option value="">Semua Status</option>
                    <option value="DRAFT"  {{ $status === 'DRAFT'  ? 'selected' : '' }}>Draft</option>
                    <option value="POSTED" {{ $status === 'POSTED' ? 'selected' : '' }}>Posted</option>
                </select>
            </x-slot>
        </x-jurnal.table-toolbar>

        {{-- Bulk Post Form --}}
        <form method="POST" action="{{ route('dashboard.jurnal-koreksi.bulk-post') }}" id="bulkForm">
            @csrf
            <div id="bulkInputsContainer"></div>
        </form>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-800">
                        <th class="px-5 py-3 w-10">
                            <input type="checkbox" id="checkAll"
                                   class="w-4 h-4 rounded border-gray-300 dark:border-gray-600 text-green-600 focus:ring-green-500 cursor-pointer"
                                   onclick="toggleAll(this)">
                        </th>
                        <th class="text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider px-5 py-3">Nomor</th>
                        <th class="text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider px-4 py-3">Tanggal</th>
                        <th class="text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider px-4 py-3">Jurnal Dikoreksi</th>
                        <th class="text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider px-4 py-3">Alasan Koreksi</th>
                        <th class="text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider px-4 py-3">Debit</th>
                        <th class="text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider px-4 py-3">Kredit</th>
                        <th class="text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider px-4 py-3">Status</th>
                        <th class="text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider px-5 py-3">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
                    @forelse($jurnal as $item)
                    @php
                        $totalDebit  = $item->detailJurnal->where('tipe', 'DEBIT')->sum('nominal');
                        $totalKredit = $item->detailJurnal->where('tipe', 'KREDIT')->sum('nominal');
                        $isPosted    = $item->status === 'POSTED';
                        $nomorJurnal = 'JK-'
                            . $item->periode->tanggal_awal->format('Y') . '-'
                            . $item->periode->tanggal_awal->format('m') . '-'
                            . str_pad($jurnal->firstItem() + $loop->index, 4, '0', STR_PAD_LEFT);
                        $nomorRef    = $item->jurnal_ref_id
                            ? 'JP-' . str_pad($item->jurnal_ref_id, 5, '0', STR_PAD_LEFT)
                            : '—';
                    @endphp
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/40 transition-colors" id="row-{{ $item->id }}">

                        <td class="px-5 py-3.5" onclick="event.stopPropagation()">
                            @if(!$isPosted)
                            <input type="checkbox"
                                   class="row-check w-4 h-4 rounded border-gray-300 dark:border-gray-600 text-green-600 focus:ring-green-500 cursor-pointer"
                                   value="{{ $item->id }}"
                                   onchange="updateBulkBar()">
                            @endif
                        </td>

                        <td class="px-5 py-3.5 font-mono text-sm font-medium text-green-600 dark:text-green-400 cursor-pointer"
                            onclick="showDrawer('/dashboard/jurnal-koreksi/{{ $item->id }}')">
                            {{ $nomorJurnal }}
                        </td>
                        <td class="px-4 py-3.5 text-gray-500 dark:text-gray-400 cursor-pointer"
                            onclick="showDrawer('/dashboard/jurnal-koreksi/{{ $item->id }}')">
                            {{ $item->tanggal->format('j M Y') }}
                        </td>
                        <td class="px-4 py-3.5 text-gray-700 dark:text-gray-300 cursor-pointer"
                            onclick="showDrawer('/dashboard/jurnal-koreksi/{{ $item->id }}')">
                            <span class="font-mono text-xs bg-yellow-50 dark:bg-yellow-900/20 text-yellow-700 dark:text-yellow-400 px-2 py-0.5 rounded">
                                {{ $nomorRef }}
                            </span>
                        </td>
                        <td class="px-4 py-3.5 text-gray-500 dark:text-gray-400 max-w-xs cursor-pointer"
                            onclick="showDrawer('/dashboard/jurnal-koreksi/{{ $item->id }}')">
                            <span class="line-clamp-1">{{ $item->keterangan ?? '—' }}</span>
                        </td>
                        <td class="px-4 py-3.5 text-right font-medium text-gray-800 dark:text-gray-200 cursor-pointer"
                            onclick="showDrawer('/dashboard/jurnal-koreksi/{{ $item->id }}')">
                            Rp {{ number_format($totalDebit, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3.5 text-right font-medium text-gray-800 dark:text-gray-200 cursor-pointer"
                            onclick="showDrawer('/dashboard/jurnal-koreksi/{{ $item->id }}')">
                            Rp {{ number_format($totalKredit, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3.5 text-center cursor-pointer"
                            onclick="showDrawer('/dashboard/jurnal-koreksi/{{ $item->id }}')">
                            @if($isPosted)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-50 dark:bg-green-900/20 text-green-600 dark:text-green-400">Posted</span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-50 dark:bg-yellow-900/20 text-yellow-600 dark:text-yellow-400">Draft</span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5" onclick="event.stopPropagation()">
                            <div class="flex items-center justify-center gap-1">
                                @if(!$isPosted)
                                <form action="{{ route('dashboard.jurnal-koreksi.destroy', $item) }}" method="POST"
                                      onsubmit="return confirm('Yakin hapus jurnal ini?')">
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
                                        onclick="showDrawer('/dashboard/jurnal-koreksi/{{ $item->id }}')"
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
                        <td colspan="9" class="px-5 py-12 text-center text-sm text-gray-400 dark:text-gray-600">
                            Belum ada jurnal koreksi.
                            @if(auth()->user()->hasPermission('CREATE_JURNAL_KOREKSI'))
                                <a href="{{ route('dashboard.jurnal-koreksi.create') }}" class="text-green-600 hover:underline ml-1">Catat sekarang</a>
                            @endif
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination — pakai komponen --}}
        <x-jurnal.table-pagination
            :paginator="$jurnal"
            :query-params="['search' => $search, 'periode_id' => $periodeId, 'status' => $status, 'per_page' => $perPage]" />

    </div>
</div>

{{-- Drawer — pakai komponen --}}
<x-jurnal.drawer title="Detail Jurnal Koreksi" />
@endsection

@push('scripts')
{{-- Shared JS (drawer + bulk) --}}
<script src="{{ asset('js/jurnal-shared.js') }}"></script>

<script>
/**
 * renderDrawerContent — spesifik untuk Jurnal Koreksi
 * Dipanggil otomatis oleh showDrawer() di jurnal-shared.js
 */
window.renderDrawerContent = function(data) {
    const j        = data.jurnal;
    const details  = j.detail_jurnal ?? [];
    const isPosted = j.status === 'POSTED';

    const nomorRef = j.jurnal_ref_id
        ? 'JP-' + String(j.jurnal_ref_id).padStart(5, '0')
        : '—';

    const refInfo = j.jurnal_ref
        ? `<span class="font-mono text-xs">${nomorRef}</span>
           <span class="text-xs text-gray-400 ml-1">(${j.jurnal_ref.keterangan ?? ''}, ${j.jurnal_ref.tanggal ?? ''})</span>`
        : `<span class="font-mono text-xs">${nomorRef}</span>`;

    document.getElementById('drawerContent').innerHTML =
        buildDrawerHeader(j.nomor_jurnal, j.tanggal, isPosted) +
        buildInfoBox('Informasi Koreksi', [
            { label: 'Jurnal Dikoreksi', value: refInfo },
            { label: 'Alasan Koreksi',   value: j.keterangan },
            { label: 'Periode',          value: j.periode?.nama_periode },
        ]) +
        buildDetailTable(details, 'Entri Jurnal Koreksi');
};
</script>
@endpush
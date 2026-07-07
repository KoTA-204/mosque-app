@extends('layouts.app')

@section('title', 'Jurnal Penyesuaian')

@section('content')
<div class="p-6 space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 px-6 py-4">
        <div>
            <h1 class="text-lg font-semibold text-gray-900 dark:text-white">Jurnal Penyesuaian</h1>
            @if($periodeAktif)
            <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">
                Periode aktif: {{ $periodeAktif->nama_periode }}
                · {{ $periodeAktif->jurnal()->penyesuaian()->count() }} Jurnal tercatat
            </p>
            @endif
        </div>
        @if(auth()->user()->hasPermission('CREATE_JURNAL_PENYESUAIAN'))
        <a href="{{ route('dashboard.jurnal-penyesuaian.create') }}"
           class="inline-flex items-center gap-2 border border-green-600 text-green-700 dark:text-green-400 dark:border-green-600 text-sm font-medium px-4 py-2 rounded-lg hover:bg-green-50 dark:hover:bg-green-900/20 transition-colors">
            Catat Penyesuaian
        </a>
        @endif
    </div>

    {{-- Alert --}}
    @if(session('success'))
        <x-jurnal.alert type="success" :message="session('success')" />
    @endif
    @if(session('error'))
        <x-jurnal.alert type="error" :message="session('error')" />
    @endif

    {{-- Table Container --}}
    <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl overflow-hidden">

        {{-- Bulk Action Bar --}}
        <x-jurnal.bulk-action-bar permission="CREATE_JURNAL_PENYESUAIAN" />

        {{-- Toolbar --}}
        <x-jurnal.table-toolbar
            :route="route('dashboard.jurnal-penyesuaian.index')"
            :per-page="$perPage"
            :search="$search"
            :hidden-params="['periode_id' => $periodeId, 'tipe' => $tipe, 'status' => $status]">

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

                <select name="tipe" onchange="document.getElementById('filterForm').submit()"
                        class="text-sm border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-1.5 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 outline-none focus:border-green-400">
                    <option value="">Semua Jenis</option>
                    @foreach($tipeLabels as $key => $label)
                        <option value="{{ $key }}" {{ $tipe === $key ? 'selected' : '' }}>{{ $label }}</option>
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
        <form method="POST" action="{{ route('dashboard.jurnal-penyesuaian.bulk-post') }}" id="bulkForm">
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
                        <th class="text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider px-4 py-3">Jenis Penyesuaian</th>
                        <th class="text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider px-4 py-3">Keterangan</th>
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
                        $nomorJurnal = 'JP-'
                            . $item->periode->tanggal_awal->format('Y') . '-'
                            . $item->periode->tanggal_awal->format('m') . '-'
                            . str_pad($jurnal->firstItem() + $loop->index, 4, '0', STR_PAD_LEFT);
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
                            onclick="showDrawer('/dashboard/jurnal-penyesuaian/{{ $item->id }}')">
                            {{ $nomorJurnal }}
                        </td>
                        <td class="px-4 py-3.5 text-gray-500 dark:text-gray-400 cursor-pointer"
                            onclick="showDrawer('/dashboard/jurnal-penyesuaian/{{ $item->id }}')">
                            {{ $item->tanggal->format('j M Y') }}
                        </td>
                        <td class="px-4 py-3.5 font-medium text-gray-800 dark:text-gray-200 cursor-pointer"
                            onclick="showDrawer('/dashboard/jurnal-penyesuaian/{{ $item->id }}')">
                            {{ $tipeLabels[$item->tipe_penyesuaian] ?? $item->tipe_penyesuaian }}
                        </td>
                        <td class="px-4 py-3.5 text-gray-500 dark:text-gray-400 max-w-xs cursor-pointer"
                            onclick="showDrawer('/dashboard/jurnal-penyesuaian/{{ $item->id }}')">
                            <span class="line-clamp-1">{{ $item->keterangan ?? '—' }}</span>
                        </td>
                        <td class="px-4 py-3.5 text-right font-medium text-gray-800 dark:text-gray-200 cursor-pointer"
                            onclick="showDrawer('/dashboard/jurnal-penyesuaian/{{ $item->id }}')">
                            Rp {{ number_format($totalDebit, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3.5 text-right font-medium text-gray-800 dark:text-gray-200 cursor-pointer"
                            onclick="showDrawer('/dashboard/jurnal-penyesuaian/{{ $item->id }}')">
                            Rp {{ number_format($totalKredit, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3.5 text-center cursor-pointer"
                            onclick="showDrawer('/dashboard/jurnal-penyesuaian/{{ $item->id }}')">
                            @if($isPosted)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-50 dark:bg-green-900/20 text-green-600 dark:text-green-400">Posted</span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-50 dark:bg-yellow-900/20 text-yellow-600 dark:text-yellow-400">Draft</span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5" onclick="event.stopPropagation()">
                            <div class="flex items-center justify-center gap-1">
                                @if(!$isPosted)
                                <form action="{{ route('dashboard.jurnal-penyesuaian.destroy', $item) }}" method="POST"
                                      data-confirm="Yakin hapus jurnal ini?" data-confirm-label="Hapus">
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
                                        onclick="showDrawer('/dashboard/jurnal-penyesuaian/{{ $item->id }}')"
                                        class="p-1.5 text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors"
                                        title="Detail">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-5 py-12 text-center text-sm text-gray-400 dark:text-gray-600">
                            Belum ada jurnal penyesuaian.
                            @if(auth()->user()->hasPermission('CREATE_JURNAL_PENYESUAIAN'))
                                <a href="{{ route('dashboard.jurnal-penyesuaian.create') }}" class="text-green-600 hover:underline ml-1">Catat sekarang</a>
                            @endif
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <x-jurnal.table-pagination
            :paginator="$jurnal"
            :query-params="['search' => $search, 'periode_id' => $periodeId, 'tipe' => $tipe, 'status' => $status, 'per_page' => $perPage]" />

    </div>
</div>

{{-- Drawer --}}
<x-jurnal.drawer title="Detail Jurnal Penyesuaian" />
@endsection

@push('scripts')
<script src="{{ asset('js/jurnal-shared.js') }}"></script>

<script>
/**
 * renderDrawerContent — spesifik untuk Jurnal Penyesuaian
 */
window.renderDrawerContent = function(data) {
    const j        = data.jurnal;
    const labels   = data.labels ?? {};
    const details  = j.detail_jurnal ?? [];
    const asets    = j.aset ?? [];
    const isPosted = j.status === 'POSTED';

    // Section aset (hanya untuk PENYUSUTAN_ASET)
    let asetSection = '';
    if (j.tipe_penyesuaian === 'PENYUSUTAN_ASET' && asets.length > 0) {
        const totalAset = asets.reduce((s, a) => s + parseFloat(a.pivot?.nominal ?? 0), 0);
        const asetRows  = asets.map(a => `
            <div class="flex justify-between items-center px-3 py-2.5 bg-green-50 border border-green-100 rounded-xl">
                <div>
                    <p class="text-sm font-medium text-gray-800">${a.nama_aset}</p>
                    <p class="text-xs text-gray-400 mt-0.5">Penyusutan periode ini</p>
                </div>
                <span class="text-sm font-semibold text-green-600">${formatRp(a.pivot?.nominal ?? 0)}</span>
            </div>`).join('');

        asetSection = `
            <div class="mt-6">
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 mb-3">
                    Rincian Aset yang Disusutkan
                </p>
                <div class="space-y-2">
                    ${asetRows}
                    <div class="flex justify-between items-center px-3 py-2 bg-green-100 rounded-xl mt-1">
                        <span class="text-xs font-semibold text-green-700">Total Penyusutan</span>
                        <span class="text-sm font-bold text-green-700">${formatRp(totalAset)}</span>
                    </div>
                </div>
            </div>`;
    }

    document.getElementById('drawerContent').innerHTML =
        buildDrawerHeader(j.nomor_jurnal, j.tanggal, isPosted) +
        buildInfoBox('Informasi Jurnal', [
            { label: 'Jenis Penyesuaian', value: labels[j.tipe_penyesuaian] ?? j.tipe_penyesuaian },
            { label: 'Keterangan',        value: j.keterangan },
            { label: 'Tanggal',           value: j.tanggal },
        ]) +
        buildDetailTable(details, 'Detail Debit & Kredit') +
        asetSection;
};
</script>
@endpush
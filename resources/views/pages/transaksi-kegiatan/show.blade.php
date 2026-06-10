@extends('layouts.app')

@section('title', 'Detail Kegiatan')

@section('content')
<div class="p-6 space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 px-6 py-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('dashboard.transaksi-kegiatan.index') }}"
               class="p-1.5 text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>

            <div>
                <h1 class="text-lg font-semibold text-gray-900 dark:text-white">
                    Detail Kegiatan
                </h1>
            </div>
        </div>
    </div>

    @php
        $bgColor = match($kegiatan->jenis_kegiatan) {
            'QURBAN' => 'bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400',
            'ZAKAT'  => 'bg-green-50 dark:bg-green-900/20 text-green-600 dark:text-green-400',
            'KAJIAN' => 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400',
            'SOSIAL' => 'bg-purple-50 dark:bg-purple-900/20 text-purple-600 dark:text-purple-400',
            default  => 'bg-gray-50 dark:bg-gray-800 text-gray-600 dark:text-gray-400',
        };

        $statusColor = match($kegiatan->status) {
            'BERJALAN'   => 'bg-green-50 dark:bg-green-900/20 text-green-600 dark:text-green-400',
            'SELESAI'    => 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400',
            'DRAFT'      => 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400',
            'DIBATALKAN' => 'bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400',
            default      => 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400',
        };
    @endphp

    {{-- Detail Card --}}
    <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl overflow-hidden">

        {{-- Top Section --}}
        <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-800">
            <div class="flex items-start justify-between gap-4 flex-wrap">

                <div class="flex items-start gap-4">
                    <div class="h-14 w-14 rounded-2xl {{ $bgColor }} flex items-center justify-center flex-shrink-0">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M8 7V3m8 4V3m-9 8h10m-11 9h12a2 2 0 002-2V7a2 2 0 00-2-2H6a2 2 0 00-2 2v11a2 2 0 002 2z"/>
                        </svg>
                    </div>

                    <div>
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
                            {{ $kegiatan->nama_kegiatan }}
                        </h2>

                        <div class="mt-2 flex flex-wrap items-center gap-2 text-sm">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full font-medium {{ $bgColor }}">
                                {{ ucfirst(strtolower($kegiatan->jenis_kegiatan)) }}
                            </span>

                            <span class="text-gray-300 dark:text-gray-600">•</span>

                            <span class="text-gray-500 dark:text-gray-400">
                                {{ $kegiatan->tanggal_mulai->format('d M Y') }}
                                —
                                {{ $kegiatan->tanggal_selesai?->format('d M Y') ?? '-' }}
                            </span>
                        </div>
                    </div>
                </div>

                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $statusColor }}">
                    {{ ucfirst(strtolower($kegiatan->status)) }}
                </span>

            </div>
        </div>

        {{-- Detail Info --}}
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-5 px-6 py-5">

            <div class="space-y-1">
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">
                    Anggaran
                </p>
                <p class="text-sm font-medium text-gray-800 dark:text-gray-200">
                    Rp {{ number_format($kegiatan->anggaran, 0, ',', '.') }}
                </p>
            </div>

            <div class="space-y-1">
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">
                    Tanggal Mulai
                </p>
                <p class="text-sm font-medium text-gray-800 dark:text-gray-200">
                    {{ $kegiatan->tanggal_mulai->format('d F Y') }}
                </p>
            </div>

            <div class="space-y-1">
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">
                    Tanggal Selesai
                </p>
                <p class="text-sm font-medium text-gray-800 dark:text-gray-200">
                    {{ $kegiatan->tanggal_selesai?->format('d F Y') ?? '-' }}
                </p>
            </div>

            <div class="space-y-1">
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">
                    Jenis Kegiatan
                </p>
                <p class="text-sm font-medium text-gray-800 dark:text-gray-200">
                    {{ ucfirst(strtolower($kegiatan->jenis_kegiatan)) }}
                </p>
            </div>

        </div>

        @if($kegiatan->deskripsi)
        <div class="px-6 pb-6">
            <div class="rounded-2xl bg-gray-50 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-700 p-4">
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-2">
                    Deskripsi
                </p>

                <p class="text-sm leading-relaxed text-gray-600 dark:text-gray-300">
                    {{ $kegiatan->deskripsi }}
                </p>
            </div>
        </div>
        @endif

    </div>

    {{-- Table Card --}}
    <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl overflow-hidden">

        {{-- Toolbar --}}
        <div class="flex items-center justify-between gap-4 px-5 py-4 border-b border-gray-100 dark:border-gray-800 flex-wrap">

            <div>
                <h3 class="text-base font-semibold text-gray-900 dark:text-white">
                    Daftar Transaksi
                </h3>
            </div>

            <div class="flex items-center gap-2">

                {{-- Search --}}
                <form method="GET" class="flex items-center gap-2">
                    <input type="hidden" name="per_page" value="{{ request('per_page', 10) }}">

                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"
                             fill="none"
                             stroke="currentColor"
                             viewBox="0 0 24 24">
                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  stroke-width="2"
                                  d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>

                        <input type="text"
                               name="search"
                               value="{{ request('search') }}"
                               placeholder="Search..."
                               class="pl-9 pr-4 py-2 text-sm border border-gray-200 dark:border-gray-700 rounded-xl bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 outline-none focus:border-green-400 w-56 placeholder-gray-400">
                    </div>
                </form>

                @if($kegiatan->status === 'AKTIF')
                    @if(auth()->user()->hasPermission('CREATE_TRANSAKSI_KEGIATAN'))
                        <a href= "#" onclick="openModal('modal-catat-transaksi')"
                           class="inline-flex items-center gap-2 border border-green-600 text-green-700 dark:text-green-400 dark:border-green-600 text-sm font-medium px-4 py-2 rounded-lg hover:bg-green-50 dark:hover:bg-green-900/20 transition-colors">
                            Catat Transaksi
                        </a>
                    @endif
                @endif

            </div>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="w-full text-sm">

                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-800">
                        <th class="text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider px-5 py-3 w-12">
                            No
                        </th>

                        <th class="text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider px-4 py-3">
                            Kode
                        </th>

                        <th class="text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider px-4 py-3">
                            Tanggal
                        </th>

                        <th class="text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider px-4 py-3">
                            Jenis
                        </th>

                        <th class="text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider px-4 py-3">
                            Jumlah
                        </th>

                        <th class="text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider px-4 py-3">
                            Dompet
                        </th>

                        <th class="text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider px-4 py-3">
                            Status
                        </th>

                        <th class="text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider px-5 py-3">
                            Action
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
                    @forelse($transaksi as $index => $item)

                    @php
                        $jenis = $item->jenis_transaksi;

                        $statusBadge = match($item->status_approval) {
                            'PENDING'  => 'bg-yellow-50 dark:bg-yellow-900/20 text-yellow-600 dark:text-yellow-400',
                            'APPROVED' => 'bg-green-50 dark:bg-green-900/20 text-green-600 dark:text-green-400',
                            'REJECTED' => 'bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400',
                            'REVISION' => 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400',
                            default    => 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400',
                        };
                    @endphp

                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/40 transition-colors">

                        <td class="px-5 py-3.5 text-center text-gray-500 dark:text-gray-400">
                            {{ $transaksi->firstItem() + $index }}
                        </td>

                        <td class="px-4 py-3.5 font-mono text-gray-700 dark:text-gray-300">
                            TRX-{{ now()->year }}-{{ str_pad($item->id, 3, '0', STR_PAD_LEFT) }}
                        </td>

                        <td class="px-4 py-3.5 text-gray-500 dark:text-gray-400">
                            {{ $item->tanggal_transaksi->format('d M Y') }}
                        </td>

                        <td class="px-4 py-3.5 text-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                {{ $jenis === 'PEMASUKAN'
                                    ? 'bg-green-50 dark:bg-green-900/20 text-green-600 dark:text-green-400'
                                    : 'bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400' }}">
                                {{ ucfirst(strtolower($jenis)) }}
                            </span>
                        </td>

                        <td class="px-4 py-3.5 font-semibold
                            {{ $jenis === 'PEMASUKAN'
                                ? 'text-green-600 dark:text-green-400'
                                : 'text-red-500 dark:text-red-400' }}">
                            {{ $jenis === 'PEMASUKAN' ? '+' : '-' }}
                            Rp {{ number_format($item->jumlah, 0, ',', '.') }}
                        </td>

                        <td class="px-4 py-3.5 text-gray-500 dark:text-gray-400">
                            {{ $item->dompet->nama_dompet }}
                        </td>

                        <td class="px-4 py-3.5 text-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusBadge }}">
                                {{ ucfirst(strtolower($item->status_approval)) }}
                            </span>
                        </td>

                        <td class="px-5 py-3.5">
                            <div class="flex items-center justify-center gap-1">

                                {{-- Detail --}}
                                <a href="{{ route('dashboard.transaksi-kegiatan.transaksi.show', [$kegiatan, $item]) }}"
                                   class="p-1.5 text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                        <circle cx="12" cy="12" r="3"/>
                                    </svg>
                                </a>

                                {{-- Edit --}}
                                @if(in_array($item->status_approval, ['PENDING', 'REVISION']))
                                <a data-transaksi="{{ json_encode([
                                        'kode_transaksi'        => $kodeTransaksi,
                                        'jenis_transaksi'       => $item->jenis_transaksi,
                                        'tanggal_transaksi'     => $item->tanggal_transaksi?->format('Y-m-d'),
                                        'jumlah'                => $item->jumlah,
                                        'dompet_id'             => $item->dompet_id,
                                        'kategori_transaksi_id' => $item->kategori_transaksi_id,
                                        'deskripsi'             => $item->deskripsi,
                                        'pencatat'              => auth()->user()->name,
                                        'bukti'                 => $item->buktiTransaksi->map(fn($b) => [
                                            'id'        => $b->id,
                                            'nama_file' => $b->nama_file ?? basename($b->path),
                                            'url'       => Storage::url($b->path),
                                        ])->values(),
                                        'update_url' => route('dashboard.transaksi-kegiatan.transaksi.update', [$kegiatan, $item]),
                                    ]) }}"
                                    onclick="openEditModal(this)"
                                   class="p-1.5 text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </a>
                                @endif

                                {{-- Delete --}}
                                @if($item->status_approval === 'PENDING')
                                <form action="{{ route('dashboard.transaksi-kegiatan.transaksi.destroy', [$kegiatan, $item]) }}"
                                      method="POST"
                                      onsubmit="return confirm('Yakin hapus transaksi ini?')">
                                    @csrf
                                    @method('DELETE')

                                    <button type="submit"
                                            class="p-1.5 text-gray-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                                @endif

                            </div>
                        </td>

                    </tr>

                    @empty
                    <tr>
                        <td colspan="8"
                            class="px-5 py-12 text-center text-sm text-gray-400 dark:text-gray-600">
                            Belum ada transaksi kegiatan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>

            </table>
        </div>

        {{-- Pagination --}}
        @if($transaksi->hasPages())
        <div class="flex items-center justify-between px-5 py-4 border-t border-gray-100 dark:border-gray-800 flex-wrap gap-3">

            <div class="flex items-center gap-1">

                {{-- Previous --}}
                @if($transaksi->onFirstPage())
                <span class="px-3 py-1.5 text-sm text-gray-300 dark:text-gray-600 border border-gray-200 dark:border-gray-700 rounded-lg">
                    Previous
                </span>
                @else
                <a href="{{ $transaksi->previousPageUrl() }}"
                   class="px-3 py-1.5 text-sm text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                    Previous
                </a>
                @endif

                {{-- Page Number --}}
                @foreach($transaksi->getUrlRange(1, $transaksi->lastPage()) as $page => $url)
                <a href="{{ $url }}"
                   class="w-8 h-8 flex items-center justify-center text-sm rounded-lg transition-colors
                    {{ $page === $transaksi->currentPage()
                        ? 'bg-green-600 text-white font-medium'
                        : 'text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800' }}">
                    {{ $page }}
                </a>
                @endforeach

                {{-- Next --}}
                @if($transaksi->hasMorePages())
                <a href="{{ $transaksi->nextPageUrl() }}"
                   class="px-3 py-1.5 text-sm text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                    Next
                </a>
                @else
                <span class="px-3 py-1.5 text-sm text-gray-300 dark:text-gray-600 border border-gray-200 dark:border-gray-700 rounded-lg">
                    Next
                </span>
                @endif

            </div>

            <span class="text-xs text-gray-400 dark:text-gray-600">
                Showing {{ $transaksi->firstItem() }} to {{ $transaksi->lastItem() }} of {{ $transaksi->total() }} entries
            </span>

        </div>
        @endif

    </div>
</div>
@include('pages.transaksi-kegiatan.create-transaksi')
@include('pages.transaksi-kegiatan.edit-transaksi')
<script>
    function openModal(id) {
        const modal = document.getElementById(id);
        modal.classList.remove('hidden');
        modal.style.display = 'flex';
    }

    function closeModal(id) {
        const modal = document.getElementById(id);
        modal.classList.add('hidden');
        modal.style.display = 'none';
    }

    function openDeleteModal(actionUrl) {
        const form = document.getElementById('deleteModalForm');
        form.action = actionUrl;
        openModal('deleteModal');
    }

    function openEditModal(el) {
        const data = JSON.parse(el.dataset.transaksi);

        // Set form action
        document.getElementById('form-edit-transaksi').action = data.update_url;

        // Info bar
        document.getElementById('edit-kode').textContent     = data.kode_transaksi;
        document.getElementById('edit-pencatat').textContent = data.pencatat;

        // Fields
        document.getElementById('edit-tanggal').value   = data.tanggal_transaksi;
        document.getElementById('edit-jumlah').value    = data.jumlah;
        document.getElementById('edit-deskripsi').value = data.deskripsi ?? '';
        document.getElementById('edit-dompet').value    = data.dompet_id;
        document.getElementById('edit-kategori').value  = data.kategori_transaksi_id;

        // Toggle jenis
        const radio = document.querySelector(`input[name="jenis_transaksi"][value="${data.jenis_transaksi}"]`);
        if (radio) radio.checked = true;
        updateEditToggleStyle(data.jenis_transaksi);

        // Bukti lama
        const buktiList = document.getElementById('edit-bukti-list');
        const buktiHint = document.getElementById('edit-bukti-hint');
        buktiList.innerHTML = '';

        if (data.bukti && data.bukti.length > 0) {
            buktiHint.classList.remove('hidden');
            data.bukti.forEach(b => {
                buktiList.insertAdjacentHTML('beforeend', `
                    <div class="flex items-center gap-2 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2">
                        <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                        </svg>
                        <a href="${b.url}" target="_blank"
                        class="text-xs text-gray-600 dark:text-gray-300 hover:text-green-600 truncate max-w-[140px]">
                            ${b.nama_file}
                        </a>
                        <label class="cursor-pointer ml-1" title="Hapus file ini">
                            <input type="checkbox" name="hapus_bukti[]" value="${b.id}" class="sr-only peer">
                            <span class="text-gray-300 peer-checked:text-red-500 hover:text-red-400 transition-colors text-xs select-none">✕</span>
                        </label>
                    </div>
                `);
            });
        } else {
            buktiHint.classList.add('hidden');
        }

        // Reset file input
        document.getElementById('editBuktiInput').value = '';
        document.getElementById('editFileLabel').innerHTML = `
            <svg class="mx-auto mb-2 w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/>
            </svg>
            <p class="text-sm text-gray-500 dark:text-gray-400">Klik untuk upload foto atau PDF baru</p>
            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Maks. 5MB · JPG, PNG, PDF</p>
        `;

        openModal('modal-edit-transaksi');
    }

    function updateEditToggleStyle(jenis) {
        const btnPemasukan   = document.getElementById('edit-btn-pemasukan');
        const btnPengeluaran = document.getElementById('edit-btn-pengeluaran');
        if (!btnPemasukan || !btnPengeluaran) return;
        if (jenis === 'PEMASUKAN') {
            btnPemasukan.classList.add('bg-green-600', 'text-white');
            btnPemasukan.classList.remove('text-gray-500');
            btnPengeluaran.classList.remove('bg-green-600', 'text-white');
            btnPengeluaran.classList.add('text-gray-500');
        } else {
            btnPengeluaran.classList.add('bg-green-600', 'text-white');
            btnPengeluaran.classList.remove('text-gray-500');
            btnPemasukan.classList.remove('bg-green-600', 'text-white');
            btnPemasukan.classList.add('text-gray-500');
        }
    }

    function showEditFileNames(input) {
        const label = document.getElementById('editFileLabel');
        if (input.files.length > 0 && label) {
            const names = Array.from(input.files).map(f => f.name).join(', ');
            label.innerHTML = `<p class="text-sm font-medium text-gray-900 dark:text-white">${names}</p>`;
        }
    }

    document.getElementById('search-input').addEventListener('input', function () {
        const btn = document.getElementById('clear-search');
        btn.classList.toggle('hidden', this.value === '');
    });

    function clearSearch() {
        const input = document.getElementById('search-input');
        input.value = '';
        document.getElementById('clear-search').classList.add('hidden');
        document.getElementById('search-form').submit();
    }

    setTimeout(() => {
        const successAlert = document.getElementById('success-alert');
        if (successAlert) {
            successAlert.classList.add('opacity-0');
            setTimeout(() => successAlert.remove(), 500);
        }
    }, 5000);

    setTimeout(() => {
        const errorAlert = document.getElementById('error-alert');
        if (errorAlert) {
            errorAlert.classList.add('opacity-0');
            setTimeout(() => errorAlert.remove(), 500);
        }
    }, 5000);
</script>
@endsection
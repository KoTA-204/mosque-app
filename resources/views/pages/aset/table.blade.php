{{-- resources/views/pages/aset/table.blade.php --}}
<div class="overflow-x-auto">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-gray-100 dark:border-gray-800">
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide w-12">No</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">ID Aset</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Nama Aset</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Lokasi</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Sumber</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Status</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Aktif</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
            @forelse($asets as $aset)
            @php $no = ($asets->currentPage() - 1) * $asets->perPage() + $loop->iteration; @endphp
            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors" id="row-{{ $aset->id }}">

                {{-- No --}}
                <td class="px-4 py-3 text-xs text-gray-400 dark:text-gray-500">
                    {{ $no }}
                </td>

                {{-- ID Aset --}}
                <td class="px-4 py-3 font-mono text-xs text-gray-500 dark:text-gray-400 whitespace-nowrap">
                    {{ $aset->kode_aset ?? '-' }}
                </td>

                {{-- Nama --}}
                <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">
                    {{ $aset->nama_aset }}
                </td>

                {{-- Lokasi --}}
                <td class="px-4 py-3 text-gray-600 dark:text-gray-400 text-xs">
                    {{ $aset->lokasi_aset }}
                </td>

                {{-- Sumber --}}
                <td class="px-4 py-3">
                    @php
                        $sumberClass = match($aset->sumber_perolehan) {
                            'Wakaf'        => 'bg-purple-50 text-purple-700 dark:bg-purple-900/20 dark:text-purple-400',
                            'Hibah/Donasi' => 'bg-blue-50 text-blue-700 dark:bg-blue-900/20 dark:text-blue-400',
                            'Pembelian'    => 'bg-orange-50 text-orange-700 dark:bg-orange-900/20 dark:text-orange-400',
                            'Infak Jamaah' => 'bg-teal-50 text-teal-700 dark:bg-teal-900/20 dark:text-teal-400',
                            default        => 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400',
                        };
                    @endphp
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $sumberClass }}">
                        {{ $aset->sumber_perolehan }}
                    </span>
                </td>

                {{-- Status badge --}}
                <td class="px-4 py-3" id="status-cell-{{ $aset->id }}">
                    @php
                        $statusClass = match($aset->status_aset) {
                            'AKTIF'       => 'text-green-600 dark:text-green-400',
                            'TIDAK AKTIF' => 'text-red-500 dark:text-red-400',
                            'DRAFT'       => 'text-yellow-600 dark:text-yellow-400',
                            default       => 'text-gray-500',
                        };
                        $statusLabel = match($aset->status_aset) {
                            'AKTIF'       => 'Aktif',
                            'TIDAK AKTIF' => 'Tidak Aktif',
                            'DRAFT'       => 'Draft',
                            default       => $aset->status_aset,
                        };
                    @endphp
                    <span class="text-sm font-medium {{ $statusClass }}">{{ $statusLabel }}</span>
                </td>

                {{-- Toggle --}}
                <td class="px-4 py-3">
                    <button
                        onclick="toggleStatus({{ $aset->id }}, '{{ $aset->status_aset }}')"
                        id="toggle-{{ $aset->id }}"
                        title="{{ $aset->status_aset === 'AKTIF' ? 'Nonaktifkan' : 'Aktifkan' }}"
                        class="relative inline-flex h-5 w-9 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 focus:outline-none
                            {{ $aset->status_aset === 'AKTIF' ? 'bg-green-500' : 'bg-gray-300 dark:bg-gray-600' }}">
                        <span
                            id="toggle-knob-{{ $aset->id }}"
                            class="pointer-events-none inline-block h-4 w-4 transform rounded-full bg-white shadow ring-0 transition duration-200
                                {{ $aset->status_aset === 'AKTIF' ? 'translate-x-4' : 'translate-x-0' }}">
                        </span>
                    </button>
                </td>

                {{-- Aksi --}}
                <td class="px-4 py-3">
                    <div class="flex items-center gap-2">
                        <button onclick="openShowModal({{ $aset->id }})"
                            class="p-1 text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors" title="Detail">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </button>
                        <button onclick="openEditModal({{ $aset->id }})"
                            class="p-1 text-gray-400 hover:text-yellow-600 dark:hover:text-yellow-400 transition-colors" title="Edit">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                            </svg>
                        </button>
                    </div>
                </td>

            </tr>
            @empty
            <tr>
                <td colspan="8" class="px-4 py-12 text-center text-gray-400 dark:text-gray-600">
                    <svg class="w-8 h-8 mx-auto mb-2 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                    </svg>
                    <p class="text-sm">Tidak ada data aset</p>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Pagination — kiri: tombol navigasi | kanan: keterangan entries --}}
<div class="flex items-center justify-between px-5 py-4 border-t border-gray-100 dark:border-gray-800">

    {{-- Kiri: navigasi halaman --}}
    <div class="flex items-center gap-1">
        @if($asets->onFirstPage())
            <span class="px-3 py-1.5 text-sm text-gray-300 dark:text-gray-600 border border-gray-200 dark:border-gray-700 rounded-lg cursor-not-allowed">Previous</span>
        @else
            <button onclick="goToPage({{ $asets->currentPage() - 1 }})"
                class="px-3 py-1.5 text-sm text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">Previous</button>
        @endif

        @foreach($asets->getUrlRange(1, $asets->lastPage()) as $page => $url)
            <button onclick="goToPage({{ $page }})"
                class="px-3 py-1.5 text-sm rounded-lg transition-colors
                    {{ $page == $asets->currentPage()
                        ? 'bg-green-600 text-white'
                        : 'text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800' }}">
                {{ $page }}
            </button>
        @endforeach

        @if($asets->hasMorePages())
            <button onclick="goToPage({{ $asets->currentPage() + 1 }})"
                class="px-3 py-1.5 text-sm text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">Next</button>
        @else
            <span class="px-3 py-1.5 text-sm text-gray-300 dark:text-gray-600 border border-gray-200 dark:border-gray-700 rounded-lg cursor-not-allowed">Next</span>
        @endif
    </div>

    {{-- Kanan: keterangan entries --}}
    <p class="text-sm text-gray-500 dark:text-gray-400">
        Showing {{ $asets->firstItem() ?? 0 }} to {{ $asets->lastItem() ?? 0 }} of {{ $asets->total() }} entries
    </p>
</div>

<script>
function goToPage(page) {
    const search  = document.getElementById('filterSearch')?.value  ?? '';
    const sumber  = document.getElementById('filterSumber')?.value  ?? '';
    const status  = document.getElementById('filterStatus')?.value  ?? '';
    const perPage = document.getElementById('perPage')?.value       ?? 10;
    const params  = new URLSearchParams();
    if (search)  params.set('search', search);
    if (sumber)  params.set('sumber', sumber);
    if (status)  params.set('status', status);
    params.set('per_page', perPage);
    params.set('page', page);
    fetch(`{{ route('dashboard.aset.index') }}?${params.toString()}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => { document.getElementById('tableWrapper').innerHTML = data.html; });
}

function toggleStatus(id, currentStatus) {
    fetch(`/dashboard/aset/${id}/toggle-status`, {
        method: 'PATCH',
        headers: {
            'X-CSRF-TOKEN':     document.querySelector('meta[name="csrf-token"]').content,
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type':     'application/json',
        },
    })
    .then(r => r.json())
    .then(data => {
        if (!data.success) return;
        const isAktif = data.status === 'AKTIF';

        const btn  = document.getElementById(`toggle-${id}`);
        const knob = document.getElementById(`toggle-knob-${id}`);
        btn.classList.toggle('bg-green-500',        isAktif);
        btn.classList.toggle('bg-gray-300',          !isAktif);
        btn.classList.toggle('dark:bg-gray-600',     !isAktif);
        knob.classList.toggle('translate-x-4',       isAktif);
        knob.classList.toggle('translate-x-0',       !isAktif);
        btn.title = isAktif ? 'Nonaktifkan' : 'Aktifkan';

        const cell = document.getElementById(`status-cell-${id}`);
        cell.innerHTML = `<span class="text-sm font-medium ${isAktif ? 'text-green-600 dark:text-green-400' : 'text-red-500 dark:text-red-400'}">${isAktif ? 'Aktif' : 'Tidak Aktif'}</span>`;

        showToast(data.message, 'success');
    })
    .catch(() => showToast('Gagal mengubah status.', 'error'));
}
</script>
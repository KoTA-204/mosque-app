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
            @php
                $no = ($asets->currentPage() - 1) * $asets->perPage() + $loop->iteration;
                $sumberClass = match($aset->sumber_perolehan) {
                    'Wakaf'        => 'bg-purple-50 text-purple-700 dark:bg-purple-900/20 dark:text-purple-400',
                    'Hibah/Donasi' => 'bg-blue-50 text-blue-700 dark:bg-blue-900/20 dark:text-blue-400',
                    'Pembelian'    => 'bg-orange-50 text-orange-700 dark:bg-orange-900/20 dark:text-orange-400',
                    'Infak Jamaah' => 'bg-teal-50 text-teal-700 dark:bg-teal-900/20 dark:text-teal-400',
                    default        => 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400',
                };
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
                $toggleBg    = $aset->status_aset === 'AKTIF' ? 'bg-green-500' : 'bg-gray-300 dark:bg-gray-600';
                $toggleKnob  = $aset->status_aset === 'AKTIF' ? 'translate-x-4' : 'translate-x-0';
                $toggleTitle = $aset->status_aset === 'AKTIF' ? 'Nonaktifkan' : 'Aktifkan';
            @endphp
            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors" id="row-{{ $aset->id }}">

                <td class="px-4 py-3 text-xs text-gray-400 dark:text-gray-500">
                     {{ $no }}
                </td>

                <td class="px-4 py-3 font-mono text-xs text-gray-500 dark:text-gray-400 whitespace-nowrap">
                     {{ $aset->kode_aset }}
                </td>

                <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">
                     {{ $aset->nama_aset }}
                </td>

                <td class="px-4 py-3 text-gray-600 dark:text-gray-400 text-xs">
                     {{ $aset->lokasi_aset }}
                </td>

                <td class="px-4 py-3">
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $sumberClass }}">
                         {{ $aset->sumber_perolehan }}
                    </span>
                </td>

                <td class="px-4 py-3" id="status-cell-{{ $aset->id }}">
                    <span class="text-sm font-medium {{ $statusClass }}">
                        {{ $statusLabel }}
                    </span>
                </td>

                <td class="px-4 py-3">
                    <button onclick="toggleStatus({{ $aset->id }}, '{{ $aset->status_aset }}')"
                        id="toggle-{{ $aset->id }}"
                        title="{{ $toggleTitle }}"
                        class="relative inline-flex h-5 w-9 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 focus:outline-none {{ $toggleBg }}">
                        <span id="toggle-knob-{{ $aset->id }}"
                            class="pointer-events-none inline-block h-4 w-4 transform rounded-full bg-white shadow ring-0 transition duration-200 {{ $toggleKnob }}">
                        </span>
                    </button>
                </td>

                <td class="px-4 py-3">
                    <div class="flex items-center gap-2">
                        <button onclick="openShowModal( {{ $aset->id }} )"
                            class="p-1 text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors" title="Detail">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </button>
                        <button onclick="openEditModal( {{ $aset->id }} )"
                            class="p-1 text-gray-400 hover:text-yellow-600 dark:hover:text-yellow-400 transition-colors" title="Edit">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                            </svg>
                        </button>
                        @php
                            $bisaHapus = !is_null($aset->umur_manfaat) && $aset->hitungNilaiBukuRealTime() <= 0;
                        @endphp
                        @if($bisaHapus)
                        <button onclick="hapusAset( {{ $aset->id }} , '{{ addslashes($aset->nama_aset) }}' )"
                            class="p-1 text-gray-400 hover:text-red-600 dark:hover:text-red-400 transition-colors" title="Hapus">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                        @else
                        <span class="p-1 text-gray-200 dark:text-gray-700 cursor-not-allowed"
                            title=" {{ is_null($aset->umur_manfaat) ? 'Aset tidak menyusut, gunakan toggle Tidak Aktif' : 'Nilai buku masih ada (Rp '.number_format($aset->hitungNilaiBukuRealTime(),0,',','.').')' }} ">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </span>
                        @endif
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

<div class="flex items-center justify-between px-5 py-4 border-t border-gray-100 dark:border-gray-800">
    <div class="flex items-center gap-1">
        @if($asets->onFirstPage())
            <span class="px-3 py-1.5 text-sm text-gray-300 dark:text-gray-600 border border-gray-200 dark:border-gray-700 rounded-lg cursor-not-allowed">Previous</span>
        @else
            <button onclick="goToPage( {{$asets->currentPage() - 1 }} )"
                class="px-3 py-1.5 text-sm text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">Previous</button>
        @endif

        @foreach($asets->getUrlRange(1, $asets->lastPage()) as $page => $url)
            <button onclick="goToPage( {{ $page }} )"
                class="px-3 py-1.5 text-sm rounded-lg transition-colors
                     {{ $page === $asets->currentPage() ? 'bg-green-600 text-white font-medium' : 'text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800' }}">
                {{ $page }}
            </button>
        @endforeach

        @if($asets->hasMorePages())
            <button onclick="goToPage( {{ $asets->currentPage() + 1 }} )"
                class="px-3 py-1.5 text-sm text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">Next</button>
        @else
            <span class="px-3 py-1.5 text-sm text-gray-300 dark:text-gray-600 border border-gray-200 dark:border-gray-700 rounded-lg cursor-not-allowed">Next</span>
        @endif
    </div>
    <p class="text-sm text-gray-500 dark:text-gray-400">
        Showing  {{ $asets->firstItem() }}  to  {{ $asets->lastItem() }}  of  {{ $asets->total() }}  entries
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
    // Nonaktifkan -> wajib pilih alasan lewat modal.
    if (currentStatus === 'AKTIF') {
        if (typeof openNonaktifModal === 'function') {
            openNonaktifModal(id);
        } else {
            kirimToggle(id, {});
        }
        return;
    }
    // Aktifkan kembali (server bisa menolak bila terkunci: rusak berat / akan dilepas).
    kirimToggle(id, {});
}

function kirimToggle(id, payload) {
    fetch(`/dashboard/aset/${id}/toggle-status`, {
        method: 'PATCH',
        headers: {
            'X-CSRF-TOKEN':     document.querySelector('meta[name="csrf-token"]').content,
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type':     'application/json',
            'Accept':           'application/json',
        },
        body: JSON.stringify(payload || {}),
    })
    .then(r => r.json().then(data => ({ ok: r.ok, data })))
    .then(({ ok, data }) => {
        if (typeof closeModal === 'function') closeModal('nonaktifAsetModal');
        if (!ok || !data.success) {
            showAlert(data.message || 'Gagal mengubah status.', 'error');
            return;
        }
        showAlert(data.message, 'success');
        // Render ulang tabel penuh dari server agar argumen status pada tombol
        // (onclick="toggleStatus(id, '...')") ikut ter-update. Tanpa ini, status
        // lama tetap 'AKTIF' sehingga klik berikutnya membuka modal nonaktif lagi.
        if (typeof applyFilters === 'function') applyFilters();
        if (typeof fetchStats  === 'function') fetchStats();
    })
    .catch(() => showAlert('Gagal mengubah status.', 'error'));
}

function hapusAset(id, nama) {
    const modal = document.getElementById('hapusAsetModal');
    if (!modal) {
        confirmAction({ message: `Hapus aset "${nama}"?`, confirmLabel: 'Hapus', onConfirm: () => doHapusAset(id) });
        return;
    }
    const msgEl = document.getElementById('hapusAsetModalMessage');
    if (msgEl) msgEl.textContent = `Hapus aset "${nama}"? Data tetap tersimpan untuk keperluan historis.`;
    modal._pendingId = id;
    modal.style.display = 'flex';
}

function doHapusAset() {
    const modal = document.getElementById('hapusAsetModal');
    const id    = modal ? modal._pendingId : null;
    if (!id) return;

    fetch(`/dashboard/aset/${id}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN':     document.querySelector('meta[name="csrf-token"]').content,
            'X-Requested-With': 'XMLHttpRequest',
            'Accept':           'application/json',
        },
        body: (() => { const f = new FormData(); f.append('_method', 'DELETE'); return f; })(),
    })
    .then(r => r.json())
    .then(data => {
        closeModal('hapusAsetModal');
        if (!data.success) {
            showAlert(data.message ?? 'Gagal menghapus aset.', 'error');
            return;
        }
        const row = document.getElementById(`row-${id}`);
        if (row) row.remove();
        fetchStats();
        showAlert(data.message, 'success');
    })
    .catch(() => showAlert('Gagal menghapus aset.', 'error'));
}
</script>
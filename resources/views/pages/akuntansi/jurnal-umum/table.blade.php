<div class="overflow-x-auto">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-gray-100 dark:border-gray-800">
                <th class="px-5 py-3 w-10">
                    <input type="checkbox" onchange="toggleSelectAll(this)"
                        class="rounded border-gray-300 dark:border-gray-600 text-green-600 focus:ring-green-500">
                </th>
                <th class="text-left text-xs font-medium text-gray-500 dark:text-gray-400 px-4 py-3 w-10">No</th>
                <th class="text-left text-xs font-medium text-gray-500 dark:text-gray-400 px-4 py-3">Tanggal</th>
                <th class="text-left text-xs font-medium text-gray-500 dark:text-gray-400 px-4 py-3">Bukti</th>
                <th class="text-left text-xs font-medium text-gray-500 dark:text-gray-400 px-4 py-3">Akun</th>
                <th class="text-left text-xs font-medium text-gray-500 dark:text-gray-400 px-4 py-3">Keterangan</th>
                <th class="text-left text-xs font-medium text-gray-500 dark:text-gray-400 px-4 py-3">Kode Akun</th>
                <th class="text-right text-xs font-medium text-gray-500 dark:text-gray-400 px-4 py-3">Debit</th>
                <th class="text-right text-xs font-medium text-gray-500 dark:text-gray-400 px-4 py-3">Kredit</th>
                <th class="text-left text-xs font-medium text-gray-500 dark:text-gray-400 px-4 py-3">Status</th>
                <th class="text-center text-xs font-medium text-gray-500 dark:text-gray-400 px-4 py-3">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50 dark:divide-gray-800/60">
            @forelse($jurnals as $i => $jurnal)
                @foreach($jurnal->detailJurnal as $j => $detail)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/40 transition-colors">
                    {{-- Checkbox hanya di baris pertama setiap jurnal, hanya untuk DRAFT --}}
                    <td class="px-5 py-3.5">
                        @if($j === 0 && $jurnal->status === 'DRAFT')
                        {{-- table.blade.php, baris checkbox --}}
                        <input type="checkbox" value="{{ $jurnal->id }}"
                            class="jurnal-checkbox rounded border-gray-300 dark:border-gray-600 text-green-600 focus:ring-green-500"
                            onchange="updateBulkBar()">
                        @endif
                    </td>
                    <td class="px-4 py-3.5 text-gray-500 dark:text-gray-400">
                        {{ $j === 0 ? $jurnals->firstItem() + $i : '' }}
                    </td>
                    <td class="px-4 py-3.5 text-gray-700 dark:text-gray-300 whitespace-nowrap">
                        {{ $j === 0 ? $jurnal->tanggal->translatedFormat('d M Y') : '' }}
                    </td>
                    <td class="px-4 py-3.5 text-gray-600 dark:text-gray-400">
                        {{ $j === 0 ? $jurnal->id : '' }}
                    </td>
                    <td class="px-4 py-3.5 text-gray-900 dark:text-white font-medium">
                        {{ $detail->akun->nama_akun ?? '-' }}
                    </td>
                    <td class="px-4 py-3.5 text-gray-600 dark:text-gray-400 max-w-xs truncate">
                        {{ $j === 0 ? ($jurnal->keterangan ?: $jurnal->transaksi?->deskripsi ?: '-') : '' }}
                    </td>
                    <td class="px-4 py-3.5 text-gray-600 dark:text-gray-400 font-mono text-xs">
                        {{ $detail->akun->kode_akun ?? '-' }}
                    </td>
                    <td class="px-4 py-3.5 text-right">
                        @if($detail->tipe === 'DEBIT')
                            <span class="text-gray-900 dark:text-white">Rp {{ number_format($detail->nominal, 0, ',', '.') }}</span>
                        @else
                            <span class="text-gray-300 dark:text-gray-600">-</span>
                        @endif
                    </td>
                    <td class="px-4 py-3.5 text-right">
                        @if($detail->tipe === 'KREDIT')
                            <span class="text-gray-900 dark:text-white">Rp {{ number_format($detail->nominal, 0, ',', '.') }}</span>
                        @else
                            <span class="text-gray-300 dark:text-gray-600">-</span>
                        @endif
                    </td>
                    {{-- Status & Aksi hanya di baris pertama --}}
                    <td class="px-4 py-3.5">
                        @if($j === 0)
                            @if($jurnal->status === 'POSTED')
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400">
                                    <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> Posted
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400">
                                    <span class="w-1.5 h-1.5 rounded-full bg-yellow-500"></span> Draft
                                </span>
                            @endif
                        @endif
                    </td>
                    <td class="px-4 py-3.5 text-center">
                        @if($j === 0)
                        <div class="flex items-center justify-center gap-2">
                            @if($jurnal->status === 'DRAFT')
                            {{-- Tombol Post --}}
                            <form action="{{ route('dashboard.jurnal-umum.post', $jurnal->id) }}" method="POST"
                                data-confirm="Posting jurnal ini?" data-confirm-title="Posting Jurnal" data-confirm-label="Posting" data-confirm-class="bg-green-600 hover:bg-green-700">
                                @csrf
                                @if(auth()->user()?->hasPermission('CREATE_JURNAL'))
                                <button type="submit" title="Posting"
                                    class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium text-green-700 dark:text-green-400 border border-green-300 dark:border-green-700 rounded-lg hover:bg-green-50 dark:hover:bg-green-900/20 transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Post
                                </button>
                                @endif
                            </form>
                            {{-- Tombol Hapus --}}
                            <form action="{{ route('dashboard.jurnal-umum.destroy', $jurnal->id) }}" method="POST"
                                data-confirm="Hapus jurnal ini?" data-confirm-label="Hapus">
                                @csrf @method('DELETE')
                                @if(auth()->user()?->hasPermission('DELETE_JURNAL'))
                                <button type="submit" title="Hapus"
                                    class="text-gray-400 hover:text-red-500 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                                @endif
                            </form>
                            @else
                            {{-- Sudah posted, tidak bisa dihapus/diedit --}}
                            <span class="text-xs text-gray-400 dark:text-gray-500 italic">—</span>
                            @endif
                        </div>
                        @endif
                    </td>
                </tr>
                @endforeach
            @empty
            <tr>
                <td colspan="11" class="px-5 py-12 text-center text-gray-400 dark:text-gray-500">
                    Tidak ada data jurnal umum.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Pagination --}}
@if($jurnals->hasPages())
<div class="flex items-center justify-between px-5 py-4 border-t border-gray-100 dark:border-gray-800">
    <p class="text-sm text-gray-500 dark:text-gray-400">
        Menampilkan {{ $jurnals->firstItem() }} s.d. {{ $jurnals->lastItem() }} dari {{ $jurnals->total() }} data
    </p>
    <div class="flex items-center gap-1">
        @if($jurnals->onFirstPage())
            <span class="px-3 py-1.5 text-sm text-gray-300 dark:text-gray-600 border border-gray-200 dark:border-gray-700 rounded-lg">Sebelumnya</span>
        @else
            <button onclick="goToPage({{ $jurnals->currentPage() - 1 }})" class="px-3 py-1.5 text-sm text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800">Sebelumnya</button>
        @endif
        @foreach($jurnals->getUrlRange(max(1,$jurnals->currentPage()-2), min($jurnals->lastPage(),$jurnals->currentPage()+2)) as $page => $url)
            @if($page == $jurnals->currentPage())
                <span class="px-3 py-1.5 text-sm font-medium bg-green-600 text-white rounded-lg">{{ $page }}</span>
            @else
                <button onclick="goToPage({{ $page }})" class="px-3 py-1.5 text-sm text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800">{{ $page }}</button>
            @endif
        @endforeach
        @if($jurnals->hasMorePages())
            <button onclick="goToPage({{ $jurnals->currentPage() + 1 }})" class="px-3 py-1.5 text-sm text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800">Selanjutnya</button>
        @else
            <span class="px-3 py-1.5 text-sm text-gray-300 dark:text-gray-600 border border-gray-200 dark:border-gray-700 rounded-lg">Selanjutnya</span>
        @endif
    </div>
</div>
@endif

<script>
function goToPage(page) {
    const bulan   = document.getElementById('filterBulan')?.value ?? '';
    const search  = document.getElementById('filterSearch')?.value ?? '';
    const status  = document.getElementById('filterStatus')?.value ?? '';
    const perPage = document.getElementById('perPage')?.value ?? 10;
    const params  = new URLSearchParams();
    if (bulan)  params.set('bulan', bulan);
    if (search) params.set('search', search);
    if (status) params.set('status', status);
    params.set('per_page', perPage);
    params.set('page', page);

    fetch(`{{ route('dashboard.jurnal-umum.index') }}?${params.toString()}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        document.getElementById('tableWrapper').innerHTML = data.html;
        updateBulkBar(); // ← fix
    });
}
</script>
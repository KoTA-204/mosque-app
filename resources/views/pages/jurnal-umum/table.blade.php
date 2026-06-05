<div class="overflow-x-auto">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-gray-100 dark:border-gray-800">
                <th class="text-left text-xs font-medium text-gray-500 dark:text-gray-400 px-5 py-3 w-12">No</th>
                <th class="text-left text-xs font-medium text-gray-500 dark:text-gray-400 px-4 py-3">Tanggal</th>
                <th class="text-left text-xs font-medium text-gray-500 dark:text-gray-400 px-4 py-3">Bukti</th>
                <th class="text-left text-xs font-medium text-gray-500 dark:text-gray-400 px-4 py-3">Akun</th>
                <th class="text-left text-xs font-medium text-gray-500 dark:text-gray-400 px-4 py-3">Keterangan</th>
                <th class="text-left text-xs font-medium text-gray-500 dark:text-gray-400 px-4 py-3">Kode Akun</th>
                <th class="text-right text-xs font-medium text-gray-500 dark:text-gray-400 px-4 py-3">Debit</th>
                <th class="text-right text-xs font-medium text-gray-500 dark:text-gray-400 px-4 py-3">Kredit</th>
                <th class="text-center text-xs font-medium text-gray-500 dark:text-gray-400 px-4 py-3">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50 dark:divide-gray-800/60">
            @forelse($details as $i => $detail)
            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/40 transition-colors">
                <td class="px-5 py-3.5 text-gray-500 dark:text-gray-400">{{ $details->firstItem() + $i }}</td>
                <td class="px-4 py-3.5 text-gray-700 dark:text-gray-300 whitespace-nowrap">
                    {{ $detail->jurnal->tanggal->translatedFormat('d M Y') }}
                </td>
                <td class="px-4 py-3.5 text-gray-600 dark:text-gray-400">{{ $detail->jurnal_id }}</td>
                <td class="px-4 py-3.5 text-gray-900 dark:text-white font-medium">
                    {{ $detail->akun->nama_akun ?? '-' }}
                </td>
                <td class="px-4 py-3.5 text-gray-600 dark:text-gray-400 max-w-xs truncate">
                    {{ $detail->jurnal->keterangan ?? '-' }}
                </td>
                <td class="px-4 py-3.5 text-gray-600 dark:text-gray-400 font-mono text-xs">
                    {{ $detail->akun->kode_akun ?? '-' }}
                </td>
                <td class="px-4 py-3.5 text-right">
                    @if($detail->tipe === 'DEBIT')
                        <span class="text-gray-900 dark:text-white">Rp {{ number_format($detail->nominal, 0, ',', '.') }}</span>
                        <br><span class="text-gray-300 dark:text-gray-600 text-xs">-</span>
                    @else
                        <span class="text-gray-300 dark:text-gray-600">-</span>
                    @endif
                </td>
                <td class="px-4 py-3.5 text-right">
                    @if($detail->tipe === 'KREDIT')
                        <span class="text-gray-900 dark:text-white">Rp {{ number_format($detail->nominal, 0, ',', '.') }}</span>
                        <br><span class="text-gray-300 dark:text-gray-600 text-xs">-</span>
                    @else
                        <span class="text-gray-300 dark:text-gray-600">-</span>
                    @endif
                </td>
                <td class="px-4 py-3.5 text-center">
                    <div class="flex items-center justify-center gap-2">
                        @can('permission:DELETE_JURNAL_UMUM')
                        @if($detail->jurnal->status !== 'POSTED')
                        <form action="{{ route('dashboard.jurnal-umum.destroy', $detail->jurnal_id) }}" method="POST"
                            onsubmit="return confirm('Hapus jurnal ini?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-gray-400 hover:text-red-500 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </form>
                        @endif
                        @endcan
                        @can('permission:EDIT_JURNAL_UMUM')
                        @if($detail->jurnal->status !== 'POSTED')
                        <a href="{{ route('dashboard.jurnal-umum.edit', $detail->jurnal_id) }}"
                            class="text-gray-400 hover:text-blue-500 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                            </svg>
                        </a>
                        @endif
                        @endcan
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="9" class="px-5 py-12 text-center text-gray-400 dark:text-gray-500">
                    Tidak ada data jurnal umum.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Pagination --}}
@if($details->hasPages())
<div class="flex items-center justify-between px-5 py-4 border-t border-gray-100 dark:border-gray-800">
    <p class="text-sm text-gray-500 dark:text-gray-400">
        Menampilkan {{ $details->firstItem() }} s.d. {{ $details->lastItem() }} dari {{ $details->total() }} data
    </p>
    <div class="flex items-center gap-1">
        @if($details->onFirstPage())
            <span class="px-3 py-1.5 text-sm text-gray-300 dark:text-gray-600 border border-gray-200 dark:border-gray-700 rounded-lg">Sebelumnya</span>
        @else
            <button onclick="goToPage({{ $details->currentPage() - 1 }})" class="px-3 py-1.5 text-sm text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800">Sebelumnya</button>
        @endif

        @foreach($details->getUrlRange(max(1, $details->currentPage()-2), min($details->lastPage(), $details->currentPage()+2)) as $page => $url)
            @if($page == $details->currentPage())
                <span class="px-3 py-1.5 text-sm font-medium bg-green-600 text-white rounded-lg">{{ $page }}</span>
            @else
                <button onclick="goToPage({{ $page }})" class="px-3 py-1.5 text-sm text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800">{{ $page }}</button>
            @endif
        @endforeach

        @if($details->hasMorePages())
            <button onclick="goToPage({{ $details->currentPage() + 1 }})" class="px-3 py-1.5 text-sm text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800">Selanjutnya</button>
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
    const perPage = document.getElementById('perPage')?.value ?? 10;
    const params  = new URLSearchParams();
    if (bulan)  params.set('bulan', bulan);
    if (search) params.set('search', search);
    params.set('per_page', perPage);
    params.set('page', page);

    fetch(`{{ route('dashboard.jurnal-umum.index') }}?${params.toString()}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        document.getElementById('tableWrapper').innerHTML = data.html;
    });
}
</script>
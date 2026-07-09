<div class="overflow-x-auto">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-gray-100 dark:border-gray-800">
                <th class="text-left text-xs font-medium text-gray-500 dark:text-gray-400 px-5 py-3 w-12">No</th>
                <th class="text-left text-xs font-medium text-gray-500 dark:text-gray-400 px-4 py-3">Kode Akun</th>
                <th class="text-left text-xs font-medium text-gray-500 dark:text-gray-400 px-4 py-3">Keterangan</th>
                <th class="text-right text-xs font-medium text-gray-500 dark:text-gray-400 px-4 py-3">Debit</th>
                <th class="text-right text-xs font-medium text-gray-500 dark:text-gray-400 px-4 py-3">Kredit</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50 dark:divide-gray-800/60">
            @forelse($akuns as $i => $akun)
            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/40 transition-colors">
                <td class="px-5 py-3.5 text-gray-500 dark:text-gray-400">{{ $akuns->firstItem() + $i }}</td>
                <td class="px-4 py-3.5 text-gray-700 dark:text-gray-300 font-mono text-xs">{{ $akun->kode_akun }}</td>
                <td class="px-4 py-3.5 text-gray-900 dark:text-white">{{ $akun->nama_akun }}</td>
                <td class="px-4 py-3.5 text-right text-gray-900 dark:text-white">
                    @if($akun->total_debit > 0)
                        Rp {{ number_format($akun->total_debit, 2, ',', '.') }}
                    @else
                        <span class="text-gray-300 dark:text-gray-600">-</span>
                    @endif
                </td>
                <td class="px-4 py-3.5 text-right text-gray-900 dark:text-white">
                    @if($akun->total_kredit > 0)
                        Rp {{ number_format($akun->total_kredit, 2, ',', '.') }}
                    @else
                        <span class="text-gray-300 dark:text-gray-600">-</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="px-5 py-12 text-center text-gray-400 dark:text-gray-500">
                    Tidak ada data neraca saldo.
                </td>
            </tr>
            @endforelse
        </tbody>
        @if($akuns->count() > 0)
        <tfoot>
            <tr class="border-t-2 border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                <td colspan="3" class="px-5 py-3.5 text-sm font-bold text-gray-900 dark:text-white">Total</td>
                <td class="px-4 py-3.5 text-right text-sm font-bold text-gray-900 dark:text-white">
                    Rp {{ number_format($grandTotalDebit, 2, ',', '.') }}
                </td>
                <td class="px-4 py-3.5 text-right text-sm font-bold text-gray-900 dark:text-white">
                    Rp {{ number_format($grandTotalKredit, 2, ',', '.') }}
                </td>
            </tr>
        </tfoot>
        @endif
    </table>
</div>

@if($akuns->hasPages())
<div class="flex items-center justify-between px-5 py-4 border-t border-gray-100 dark:border-gray-800">
    <p class="text-sm text-gray-500 dark:text-gray-400">
        Menampilkan {{ $akuns->firstItem() }} s.d. {{ $akuns->lastItem() }} dari {{ $akuns->total() }} data
    </p>
    <div class="flex items-center gap-1">
        @if($akuns->onFirstPage())
            <span class="px-3 py-1.5 text-sm text-gray-300 dark:text-gray-600 border border-gray-200 dark:border-gray-700 rounded-lg">Sebelumnya</span>
        @else
            <button onclick="goToPage({{ $akuns->currentPage() - 1 }})" class="px-3 py-1.5 text-sm text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800">Sebelumnya</button>
        @endif
        @foreach($akuns->getUrlRange(max(1,$akuns->currentPage()-2), min($akuns->lastPage(),$akuns->currentPage()+2)) as $page => $url)
            @if($page == $akuns->currentPage())
                <span class="px-3 py-1.5 text-sm font-medium bg-green-600 text-white rounded-lg">{{ $page }}</span>
            @else
                <button onclick="goToPage({{ $page }})" class="px-3 py-1.5 text-sm text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800">{{ $page }}</button>
            @endif
        @endforeach
        @if($akuns->hasMorePages())
            <button onclick="goToPage({{ $akuns->currentPage() + 1 }})" class="px-3 py-1.5 text-sm text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800">Selanjutnya</button>
        @else
            <span class="px-3 py-1.5 text-sm text-gray-300 dark:text-gray-600 border border-gray-200 dark:border-gray-700 rounded-lg">Selanjutnya</span>
        @endif
    </div>
</div>
@endif

<script>
function goToPage(page) {
    const periode = document.getElementById('filterPeriode')?.value ?? '';
    const akun    = document.getElementById('filterAkun')?.value ?? '';
    const sort    = document.getElementById('filterSort')?.value ?? 'kode_akun_asc';
    const perPage = document.getElementById('perPage')?.value ?? 10;
    const params  = new URLSearchParams();
    if (periode) params.set('periode_id', periode);
    if (akun)    params.set('akun_filter', akun);
    if (sort)    params.set('sort_by', sort);
    params.set('per_page', perPage);
    params.set('page', page);

    fetch(`{{ route('dashboard.neraca-saldo.index') }}?${params.toString()}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        document.getElementById('tableWrapper').innerHTML = data.html;
    });
}
</script>
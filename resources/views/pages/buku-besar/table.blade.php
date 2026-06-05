<div class="overflow-x-auto">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-gray-100 dark:border-gray-800">
                <th class="text-left text-xs font-medium text-gray-500 dark:text-gray-400 px-5 py-3 w-12">No</th>
                <th class="text-left text-xs font-medium text-gray-500 dark:text-gray-400 px-4 py-3">Tanggal</th>
                <th class="text-left text-xs font-medium text-gray-500 dark:text-gray-400 px-4 py-3">Keterangan</th>
                <th class="text-left text-xs font-medium text-gray-500 dark:text-gray-400 px-4 py-3">Ref.</th>
                <th class="text-right text-xs font-medium text-gray-500 dark:text-gray-400 px-4 py-3">Debit</th>
                <th class="text-right text-xs font-medium text-gray-500 dark:text-gray-400 px-4 py-3">Kredit</th>
                <th class="text-right text-xs font-medium text-gray-500 dark:text-gray-400 px-4 py-3">Saldo</th>
                <th class="text-left text-xs font-medium text-gray-500 dark:text-gray-400 px-4 py-3">Sumber Dana</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50 dark:divide-gray-800/60">
            @php $runningBalance = $saldoAwal; @endphp
            @forelse($details as $i => $detail)
            @php
                $isDebit = $detail->tipe === 'DEBIT';
                $runningBalance = $isDebit
                    ? $runningBalance + $detail->nominal
                    : $runningBalance - $detail->nominal;
            @endphp
            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/40 transition-colors">
                <td class="px-5 py-3.5 text-gray-500 dark:text-gray-400">{{ $details->firstItem() + $i }}</td>
                <td class="px-4 py-3.5 text-gray-700 dark:text-gray-300 whitespace-nowrap">
                    {{ $detail->jurnal->tanggal->translatedFormat('d M Y') }}
                </td>
                <td class="px-4 py-3.5 text-gray-600 dark:text-gray-400 max-w-xs truncate">
                    {{ $detail->jurnal->keterangan ?? 'Saldo Awal' }}
                </td>
                <td class="px-4 py-3.5 text-gray-600 dark:text-gray-400 font-mono text-xs">
                    J - {{ str_pad($detail->jurnal_id, 4, '0', STR_PAD_LEFT) }}
                </td>
                <td class="px-4 py-3.5 text-right text-gray-900 dark:text-white">
                    @if($isDebit)
                        Rp {{ number_format($detail->nominal, 0, ',', '.') }}
                    @else
                        <span class="text-gray-300 dark:text-gray-600">-</span>
                    @endif
                </td>
                <td class="px-4 py-3.5 text-right text-gray-900 dark:text-white">
                    @if(!$isDebit)
                        <span class="font-semibold">Rp{{ number_format($detail->nominal, 0, ',', '.') }}</span>
                    @else
                        <span class="text-gray-300 dark:text-gray-600">-</span>
                    @endif
                </td>
                <td class="px-4 py-3.5 text-right font-semibold {{ $runningBalance >= 0 ? 'text-gray-900 dark:text-white' : 'text-red-500' }}">
                    Rp.{{ number_format(abs($runningBalance), 0, ',', '.') }}
                </td>
                <td class="px-4 py-3.5 text-gray-600 dark:text-gray-400 text-xs">
                    {{ $detail->jurnal->transaksi->dompet->nama_dompet ?? 'Dana Operasional' }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="px-5 py-12 text-center text-gray-400 dark:text-gray-500">
                    Tidak ada data buku besar.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

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
        @foreach($details->getUrlRange(max(1,$details->currentPage()-2), min($details->lastPage(),$details->currentPage()+2)) as $page => $url)
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
    const periode = document.getElementById('filterPeriode')?.value ?? '';
    const akun    = document.getElementById('filterAkun')?.value ?? '';
    const perPage = document.getElementById('perPage')?.value ?? 10;
    const params  = new URLSearchParams();
    if (periode) params.set('periode_id', periode);
    if (akun)    params.set('akun_id', akun);
    params.set('per_page', perPage);
    params.set('page', page);

    fetch(`{{ route('dashboard.buku-besar.index') }}?${params.toString()}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        document.getElementById('tableWrapper').innerHTML = data.html;
    });
}
</script>
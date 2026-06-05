@extends('layouts.app')

@section('content')
<div class="p-6 space-y-6">

    {{-- Header --}}
    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 px-6 py-4 flex items-start justify-between flex-wrap gap-4">
        <div>
            <h1 class="text-lg font-semibold text-gray-900 dark:text-white">Catatan Atas Laporan Keuangan (CALK)</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Masjid Luqmanul Hakim</p>
            @if($periode)
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Periode
                {{ $periode->tanggal_awal->locale('id')->translatedFormat('d F Y') }}
                –
                {{ $periode->tanggal_akhir->locale('id')->translatedFormat('d F Y') }}
            </p>
            @endif
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <select id="filterPeriode" onchange="changePeriode()"
                class="text-sm border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-1.5 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 outline-none focus:border-green-400">
                <option value="">Semua Periode</option>
                @foreach($periodes as $p)
                <option value="{{ $p->id }}" {{ $periodeId == $p->id ? 'selected' : '' }}>
                    {{ $p->nama_periode ?? $p->tanggal_awal->translatedFormat('F Y') }}
                </option>
                @endforeach
            </select>
            <button onclick="window.print()"
                class="inline-flex items-center gap-2 border border-green-600 text-green-700 dark:text-green-400 text-sm font-medium px-4 py-2 rounded-lg hover:bg-green-50 dark:hover:bg-green-900/20 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                Print
            </button>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 px-5 py-4 flex items-center gap-4">
            <div class="w-10 h-10 rounded-xl bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7"/>
                </svg>
            </div>
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400">Total Aset</p>
                <p class="text-sm font-bold text-gray-900 dark:text-white">Rp. {{ number_format($totalAset, 0, ',', '.') }}</p>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 px-5 py-4 flex items-center gap-4">
            <div class="w-10 h-10 rounded-xl bg-green-100 dark:bg-green-900/30 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8l-4 4m0 0l4 4m-4-4h18"/>
                </svg>
            </div>
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400">Total Liabilitas</p>
                <p class="text-sm font-bold text-gray-900 dark:text-white">Rp. {{ number_format($totalLiabilitas, 0, ',', '.') }}</p>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 px-5 py-4 flex items-center gap-4">
            <div class="w-10 h-10 rounded-xl bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7"/>
                </svg>
            </div>
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400">Total Aset Neto</p>
                <p class="text-sm font-bold text-gray-900 dark:text-white">Rp. {{ number_format($totalAsetNeto, 0, ',', '.') }}</p>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 px-5 py-4 flex items-center gap-4">
            <div class="w-10 h-10 rounded-xl bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/>
                </svg>
            </div>
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400">Jumlah Catatan</p>
                <p class="text-sm font-bold text-gray-900 dark:text-white">{{ $jumlahCatatan }} Catatan</p>
            </div>
        </div>
    </div>

    {{-- Konten Dua Kolom --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Sidebar Daftar Catatan --}}
        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-5">
            <h2 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Daftar Catatan</h2>
            <nav class="space-y-1" id="calkNav">
                @php
                $catatanList = [
                    1 => 'Informasi Umum',
                    2 => 'Kebijakan Akuntansi',
                    3 => 'Kas dan Setara Kas',
                    4 => 'Aset Tetap',
                    5 => 'Dana Infak dan Sedekah',
                    6 => 'Beban Operasional',
                    7 => 'Aset Neto',
                    8 => 'Peristiwa Setelah Periode Pelaporan',
                ];
                @endphp
                @foreach($catatanList as $no => $judul)
                <button onclick="showCatatan({{ $no }})"
                    id="nav-{{ $no }}"
                    class="catatan-nav-btn w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-sm transition-colors
                        {{ $no === 1 ? 'bg-indigo-600 text-white font-medium' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800' }}">
                    <span class="flex items-center gap-3">
                        <span class="w-5 h-5 rounded-full {{ $no === 1 ? 'bg-white/20 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400' }} flex items-center justify-center text-xs font-bold shrink-0">{{ $no }}</span>
                        {{ $judul }}
                    </span>
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
                @endforeach
            </nav>
        </div>

        {{-- Konten Catatan --}}
        <div class="lg:col-span-2 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-6" id="calkContent">

            {{-- Catatan 1: Informasi Umum --}}
            <div id="catatan-1" class="catatan-section">
                <div class="flex items-center gap-3 mb-4">
                    <span class="w-8 h-8 rounded-full bg-indigo-600 text-white flex items-center justify-center text-sm font-bold shrink-0">1</span>
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">Catatan 1 – Informasi Umum</h3>
                </div>
                <div class="space-y-3 text-sm text-gray-700 dark:text-gray-300 leading-relaxed">
                    <p><strong>Tujuan dan Kegiatan Entitas</strong></p>
                    <p>Masjid Luqmanul Hakim merupakan entitas berorientasi nonlaba yang bergerak dalam bidang peribadatan, pendidikan, dakwah, dan pelayanan sosial kemasyarakatan.</p>
                    <p>Kegiatan utama masjid meliputi:</p>
                    <ul class="list-disc pl-5 space-y-1">
                        <li>Penyelenggaraan ibadah harian dan ibadah Jumat</li>
                        <li>Kegiatan pendidikan Al-Qur'an</li>
                        <li>Kajian keislaman</li>
                        <li>Pengelolaan zakat, infak, dan sedekah</li>
                        <li>Kegiatan sosial dan kemanusiaan</li>
                    </ul>
                    <p>Masjid Luqmanul Hakim beralamat di Jalan Politeknik Negeri Bandung, Jl. Ciwaruga, Ciwaruga, Kec. Parongpong, Kabupaten Bandung Barat, Jawa Barat 40559.</p>
                    <p>Laporan keuangan ini disusun untuk periode yang berakhir pada
                        @if($periode) {{ $periode->tanggal_akhir->translatedFormat('d F Y') }} @else 31 Desember. @endif
                    </p>
                </div>
            </div>

            {{-- Catatan 2: Kebijakan Akuntansi --}}
            <div id="catatan-2" class="catatan-section hidden">
                <div class="flex items-center gap-3 mb-4">
                    <span class="w-8 h-8 rounded-full bg-indigo-600 text-white flex items-center justify-center text-sm font-bold shrink-0">2</span>
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">Catatan 2 – Kebijakan Akuntansi</h3>
                </div>
                <div class="space-y-3 text-sm text-gray-700 dark:text-gray-300 leading-relaxed">
                    <p><strong>Dasar Penyusunan Laporan Keuangan</strong></p>
                    <p>Laporan keuangan disusun berdasarkan Standar Akuntansi Keuangan Entitas Tanpa Akuntabilitas Publik (SAK ETAP) dan PSAK 45 tentang Pelaporan Keuangan Organisasi Nirlaba.</p>
                    <p><strong>Pengakuan Pendapatan</strong></p>
                    <p>Pendapatan diakui pada saat diterima (basis kas). Pendapatan berupa infak, sedekah, dan zakat diakui pada saat diterima oleh entitas.</p>
                    <p><strong>Pengakuan Beban</strong></p>
                    <p>Beban diakui pada saat terjadinya pengeluaran kas (basis kas), kecuali untuk beban penyusutan yang diakui secara berkala.</p>
                    <p><strong>Aset Tetap</strong></p>
                    <p>Aset tetap dicatat berdasarkan biaya perolehan dikurangi akumulasi penyusutan. Penyusutan dihitung menggunakan metode garis lurus berdasarkan estimasi umur manfaat.</p>
                </div>
            </div>

            {{-- Catatan 3: Kas dan Setara Kas --}}
            <div id="catatan-3" class="catatan-section hidden">
                <div class="flex items-center gap-3 mb-4">
                    <span class="w-8 h-8 rounded-full bg-indigo-600 text-white flex items-center justify-center text-sm font-bold shrink-0">3</span>
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">Catatan 3 – Kas dan Setara Kas</h3>
                </div>
                <div class="space-y-3 text-sm text-gray-700 dark:text-gray-300">
                    <p>Kas dan setara kas terdiri dari:</p>
                    <div class="overflow-x-auto mt-3">
                        <table class="w-full text-sm border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden">
                            <thead class="bg-gray-50 dark:bg-gray-800">
                                <tr>
                                    <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500 dark:text-gray-400">Keterangan</th>
                                    <th class="text-right px-4 py-2.5 text-xs font-medium text-gray-500 dark:text-gray-400">Jumlah</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                <tr>
                                    <td class="px-4 py-2.5 text-gray-700 dark:text-gray-300">Kas Utama (Kas Masjid)</td>
                                    <td class="px-4 py-2.5 text-right text-gray-900 dark:text-white">Rp {{ number_format($kasSetaraKas * 0.4, 0, ',', '.') }}</td>
                                </tr>
                                <tr>
                                    <td class="px-4 py-2.5 text-gray-700 dark:text-gray-300">Rekening Bank BSI</td>
                                    <td class="px-4 py-2.5 text-right text-gray-900 dark:text-white">Rp {{ number_format($kasSetaraKas * 0.6, 0, ',', '.') }}</td>
                                </tr>
                                <tr class="bg-gray-50 dark:bg-gray-800 font-semibold">
                                    <td class="px-4 py-2.5 text-gray-900 dark:text-white">Total</td>
                                    <td class="px-4 py-2.5 text-right text-gray-900 dark:text-white">Rp {{ number_format($kasSetaraKas, 0, ',', '.') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Catatan 4: Aset Tetap --}}
            <div id="catatan-4" class="catatan-section hidden">
                <div class="flex items-center gap-3 mb-4">
                    <span class="w-8 h-8 rounded-full bg-indigo-600 text-white flex items-center justify-center text-sm font-bold shrink-0">4</span>
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">Catatan 4 – Aset Tetap</h3>
                </div>
                <div class="space-y-3 text-sm text-gray-700 dark:text-gray-300">
                    <p>Rincian aset tetap per periode pelaporan:</p>
                    <div class="overflow-x-auto mt-3">
                        <table class="w-full text-sm border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden">
                            <thead class="bg-gray-50 dark:bg-gray-800">
                                <tr>
                                    <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500 dark:text-gray-400">Nama Aset</th>
                                    <th class="text-right px-4 py-2.5 text-xs font-medium text-gray-500 dark:text-gray-400">Nilai Perolehan</th>
                                    <th class="text-right px-4 py-2.5 text-xs font-medium text-gray-500 dark:text-gray-400">Akm. Penyusutan</th>
                                    <th class="text-right px-4 py-2.5 text-xs font-medium text-gray-500 dark:text-gray-400">Nilai Buku</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                @forelse($asets as $aset)
                                <tr>
                                    <td class="px-4 py-2.5 text-gray-700 dark:text-gray-300">{{ $aset->nama_aset }}</td>
                                    <td class="px-4 py-2.5 text-right text-gray-900 dark:text-white">Rp {{ number_format($aset->nilai_tercatat, 0, ',', '.') }}</td>
                                    <td class="px-4 py-2.5 text-right text-gray-900 dark:text-white">Rp {{ number_format($aset->akumulasi_real_time, 0, ',', '.') }}</td>
                                    <td class="px-4 py-2.5 text-right font-semibold text-gray-900 dark:text-white">Rp {{ number_format($aset->nilai_buku_real_time, 0, ',', '.') }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-6 text-center text-gray-400 dark:text-gray-500">Belum ada data aset.</td>
                                </tr>
                                @endforelse
                            </tbody>
                            @if($asets->count() > 0)
                            <tfoot>
                                <tr class="bg-gray-50 dark:bg-gray-800 font-semibold">
                                    <td class="px-4 py-2.5 text-gray-900 dark:text-white">Total</td>
                                    <td class="px-4 py-2.5 text-right text-gray-900 dark:text-white">Rp {{ number_format($asets->sum('nilai_tercatat'), 0, ',', '.') }}</td>
                                    <td class="px-4 py-2.5 text-right text-gray-900 dark:text-white">Rp {{ number_format($asets->sum('akumulasi_real_time'), 0, ',', '.') }}</td>
                                    <td class="px-4 py-2.5 text-right text-gray-900 dark:text-white">Rp {{ number_format($totalAsetTetap, 0, ',', '.') }}</td>
                                </tr>
                            </tfoot>
                            @endif
                        </table>
                    </div>
                </div>
            </div>

            {{-- Catatan 5: Dana Infak dan Sedekah --}}
            <div id="catatan-5" class="catatan-section hidden">
                <div class="flex items-center gap-3 mb-4">
                    <span class="w-8 h-8 rounded-full bg-indigo-600 text-white flex items-center justify-center text-sm font-bold shrink-0">5</span>
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">Catatan 5 – Dana Infak dan Sedekah</h3>
                </div>
                <div class="space-y-3 text-sm text-gray-700 dark:text-gray-300 leading-relaxed">
                    <p>Total penerimaan dana infak dan sedekah selama periode ini adalah:</p>
                    <p class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">
                        Rp {{ number_format($totalInfakSedekah, 0, ',', '.') }}
                    </p>
                    <p>Dana infak dan sedekah diterima dari jamaah masjid berupa infak Jumat, infak harian, kencleng, dan donasi kegiatan. Seluruh dana digunakan untuk operasional dan kegiatan masjid sesuai tujuan penggunaannya.</p>
                </div>
            </div>

            {{-- Catatan 6: Beban Operasional --}}
            <div id="catatan-6" class="catatan-section hidden">
                <div class="flex items-center gap-3 mb-4">
                    <span class="w-8 h-8 rounded-full bg-indigo-600 text-white flex items-center justify-center text-sm font-bold shrink-0">6</span>
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">Catatan 6 – Beban Operasional</h3>
                </div>
                <div class="space-y-3 text-sm text-gray-700 dark:text-gray-300 leading-relaxed">
                    <p>Total beban operasional selama periode ini adalah:</p>
                    <p class="text-2xl font-bold text-red-500 dark:text-red-400">
                        Rp {{ number_format($totalBeban, 0, ',', '.') }}
                    </p>
                    <p>Beban operasional mencakup biaya listrik, air, honorarium imam dan marbot, kebersihan, perlengkapan ibadah, dan beban kegiatan lainnya.</p>
                </div>
            </div>

            {{-- Catatan 7: Aset Neto --}}
            <div id="catatan-7" class="catatan-section hidden">
                <div class="flex items-center gap-3 mb-4">
                    <span class="w-8 h-8 rounded-full bg-indigo-600 text-white flex items-center justify-center text-sm font-bold shrink-0">7</span>
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">Catatan 7 – Aset Neto</h3>
                </div>
                <div class="space-y-3 text-sm text-gray-700 dark:text-gray-300 leading-relaxed">
                    <p>Aset neto merupakan selisih antara total aset dengan total liabilitas entitas.</p>
                    <div class="overflow-x-auto mt-3">
                        <table class="w-full text-sm border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden">
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                <tr>
                                    <td class="px-4 py-2.5 text-gray-700 dark:text-gray-300">Total Aset</td>
                                    <td class="px-4 py-2.5 text-right text-gray-900 dark:text-white">Rp {{ number_format($totalAset, 0, ',', '.') }}</td>
                                </tr>
                                <tr>
                                    <td class="px-4 py-2.5 text-gray-700 dark:text-gray-300">Total Liabilitas</td>
                                    <td class="px-4 py-2.5 text-right text-red-500">(Rp {{ number_format($totalLiabilitas, 0, ',', '.') }})</td>
                                </tr>
                                <tr class="bg-gray-50 dark:bg-gray-800 font-semibold">
                                    <td class="px-4 py-2.5 text-gray-900 dark:text-white">Aset Neto</td>
                                    <td class="px-4 py-2.5 text-right text-indigo-600 dark:text-indigo-400">Rp {{ number_format($totalAsetNeto, 0, ',', '.') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Catatan 8: Peristiwa Setelah Periode Pelaporan --}}
            <div id="catatan-8" class="catatan-section hidden">
                <div class="flex items-center gap-3 mb-4">
                    <span class="w-8 h-8 rounded-full bg-indigo-600 text-white flex items-center justify-center text-sm font-bold shrink-0">8</span>
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">Catatan 8 – Peristiwa Setelah Periode Pelaporan</h3>
                </div>
                <div class="space-y-3 text-sm text-gray-700 dark:text-gray-300 leading-relaxed">
                    <p>Hingga tanggal penyelesaian laporan keuangan ini, tidak terdapat peristiwa setelah periode pelaporan yang memerlukan penyesuaian atau pengungkapan dalam laporan keuangan.</p>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
function showCatatan(no) {
    document.querySelectorAll('.catatan-section').forEach(el => el.classList.add('hidden'));
    document.getElementById(`catatan-${no}`).classList.remove('hidden');

    document.querySelectorAll('.catatan-nav-btn').forEach((btn, i) => {
        const isActive = (i + 1) === no;
        btn.classList.toggle('bg-indigo-600', isActive);
        btn.classList.toggle('text-white', isActive);
        btn.classList.toggle('font-medium', isActive);
        btn.classList.toggle('text-gray-700', !isActive);
        btn.classList.toggle('dark:text-gray-300', !isActive);
        btn.classList.toggle('hover:bg-gray-50', !isActive);
        btn.classList.toggle('dark:hover:bg-gray-800', !isActive);

        const badge = btn.querySelector('span > span');
        if (badge) {
            badge.classList.toggle('bg-white/20', isActive);
            badge.classList.toggle('text-white', isActive);
            badge.classList.toggle('bg-gray-100', !isActive);
            badge.classList.toggle('dark:bg-gray-700', !isActive);
            badge.classList.toggle('text-gray-500', !isActive);
            badge.classList.toggle('dark:text-gray-400', !isActive);
        }
    });
}

function changePeriode() {
    const periodeId = document.getElementById('filterPeriode').value;
    const params = new URLSearchParams();
    if (periodeId) params.set('periode_id', periodeId);
    window.location.href = `{{ route('calk.index') }}?${params.toString()}`;
}
</script>
@endsection
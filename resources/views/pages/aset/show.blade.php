<x-modal id="showAsetModal" title="Detail Aset">

    <div class="px-6 py-5 overflow-y-auto space-y-7" style="max-height: 65vh;">

        {{-- Identitas Aset --}}
        <div>
            <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-4">Identitas Aset</p>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-x-6 gap-y-5">
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Nomor Aset</p>
                    <p class="text-sm font-semibold text-gray-900 dark:text-white font-mono">{{ $aset->kode_aset ?? '–' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Nama Aset</p>
                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $aset->nama_aset }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Status</p>
                    @php
                        $statusClass = match($aset->status_aset) {
                            'AKTIF'       => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
                            'TIDAK AKTIF' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
                            default       => 'bg-gray-100 text-gray-600',
                        };
                    @endphp
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusClass }}">
                        {{ ucfirst(strtolower($aset->status_aset)) }}
                    </span>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Lokasi</p>
                    <p class="text-sm text-gray-700 dark:text-gray-300">{{ $aset->lokasi_aset }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Kondisi Aset</p>
                    @php
                        $kondisiClass = match($aset->kondisi_aset) {
                            'BAIK'         => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
                            'RUSAK RINGAN' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
                            'RUSAK BERAT'  => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
                            default        => 'bg-gray-100 text-gray-600',
                        };
                    @endphp
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $kondisiClass }}">
                        {{ ucwords(strtolower($aset->kondisi_aset)) }}
                    </span>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Pemilik</p>
                    <p class="text-sm text-gray-700 dark:text-gray-300">Masjid Lukmanul Hakim</p>
                </div>
            </div>
        </div>

        {{-- Nilai Aset --}}
        <div>
            <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-4">Nilai Aset</p>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-x-6 gap-y-5">
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Nilai Perolehan</p>
                    <p class="text-sm font-semibold text-gray-900 dark:text-white">Rp {{ number_format($aset->nilai_tercatat, 0, ',', '.') }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Akumulasi Penyusutan</p>
                    @if($aset->umur_manfaat)
                        <p class="text-sm font-semibold text-red-500">Rp {{ number_format($aset->akumulasi_real_time, 0, ',', '.') }}</p>
                    @else
                        <p class="text-sm text-gray-400 italic">Tidak disusutkan</p>
                    @endif
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Nilai Buku Saat Ini</p>
                    <p class="text-sm font-semibold text-green-600">Rp {{ number_format($aset->nilai_buku_real_time, 0, ',', '.') }}</p>
                </div>
            </div>
        </div>

        {{-- Informasi Perolehan --}}
        <div>
            <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-4">Informasi Perolehan</p>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-x-6 gap-y-5">
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Sumber Perolehan</p>
                    @php
                        $sumberClass = match($aset->sumber_perolehan) {
                            'Wakaf'        => 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400',
                            'Hibah/Donasi' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
                            'Pembelian'    => 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400',
                            'Infak Jamaah' => 'bg-teal-100 text-teal-700 dark:bg-teal-900/30 dark:text-teal-400',
                            default        => 'bg-gray-100 text-gray-600',
                        };
                    @endphp
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $sumberClass }}">
                        {{ $aset->sumber_perolehan }}
                    </span>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Tanggal Perolehan</p>
                    <p class="text-sm text-gray-700 dark:text-gray-300">{{ $aset->tanggal_perolehan?->translatedFormat('d F Y') ?? '–' }}</p>
                </div>
                @if($aset->nama_pemberi && $aset->nama_pemberi !== '-')
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">{{ $aset->label_pemberi }}</p>
                    <p class="text-sm text-gray-700 dark:text-gray-300">{{ $aset->nama_pemberi }}</p>
                </div>
                @endif
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Jumlah Unit</p>
                    <p class="text-sm text-gray-700 dark:text-gray-300">{{ $aset->jumlah_unit ?? 1 }} Unit</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Dokumen Pendukung</p>
                    @if($aset->dokumen_pendukung)
                        <a href="{{ Storage::url($aset->dokumen_pendukung) }}" target="_blank"
                            class="text-sm text-green-600 underline">Lihat Dokumen</a>
                    @else
                        <p class="text-sm text-gray-400">–</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Penyusutan --}}
        @if($aset->umur_manfaat && $aset->tanggal_mulai_penyusutan)
        <div>
            <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-4">Informasi Penyusutan</p>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-x-6 gap-y-5 mb-5">
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Penyusutan / Bulan</p>
                    <p class="text-sm text-gray-700 dark:text-gray-300">Rp {{ number_format($aset->penyusutan_per_bulan, 0, ',', '.') }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Tanggal Mulai</p>
                    <p class="text-sm text-gray-700 dark:text-gray-300">{{ $aset->tanggal_mulai_penyusutan->translatedFormat('d F Y') }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Umur Manfaat</p>
                    <p class="text-sm text-gray-700 dark:text-gray-300">{{ $aset->umur_manfaat }} Tahun</p>
                </div>
            </div>

            {{-- Progress bar --}}
            <div class="mb-5">
                <div class="flex justify-between text-xs text-gray-400 mb-1.5">
                    <span>Progress Penyusutan</span>
                    <div class="flex items-center gap-2">
                        @if($aset->status_aset === 'TIDAK AKTIF')
                            <span class="text-xs text-red-400 font-medium">Dihentikan</span>
                        @endif
                        <span>{{ number_format($aset->progress_penyusutan, 1) }}%</span>
                    </div>
                </div>
                <div class="w-full bg-gray-100 dark:bg-gray-800 rounded-full h-2">
                    <div class="h-2 rounded-full {{ $aset->status_aset === 'TIDAK AKTIF' ? 'bg-gray-400' : 'bg-green-500' }}"
                        style="width: {{ min($aset->progress_penyusutan, 100) }}%"></div>
                </div>
                @if($aset->status_aset === 'TIDAK AKTIF')
                    <p class="text-xs text-red-400 mt-1">* Penyusutan dihentikan saat aset dinonaktifkan.</p>
                @endif
            </div>

            {{-- Proyeksi Jadwal Penyusutan --}}
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-2">Proyeksi Jadwal Penyusutan (Garis Lurus)</p>
            <p class="text-xs text-gray-400 mb-3">Tabel ini adalah proyeksi teoritis, bukan catatan jurnal aktual.</p>
            <div class="overflow-x-auto rounded-xl border border-gray-100 dark:border-gray-800 mb-6">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-800 border-b border-gray-100 dark:border-gray-800">
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Tahun</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Nilai Awal</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Penyusutan</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Akumulasi</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Nilai Buku</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
                        @php
                            $nilaiAwal         = (float) $aset->nilai_tercatat;
                            $penyTahunan       = $aset->umur_manfaat > 0 ? $nilaiAwal / $aset->umur_manfaat : 0;
                            $akumulasi         = 0;
                            $tahunMulai        = $aset->tanggal_mulai_penyusutan->year;
                            $snapshotAkumulasi = (float) $aset->akumulasi_penyusutan;
                            $isNonaktif        = $aset->status_aset === 'TIDAK AKTIF';
                            $sudahDihentikan   = false;
                        @endphp
                        @for($i = 1; $i <= $aset->umur_manfaat; $i++)
                        @php
                            $nilaiBukuAwal = $nilaiAwal - $akumulasi;
                            $akumulasi    += $penyTahunan;
                            $nilaiBuku     = max($nilaiAwal - $akumulasi, 0);
                            $tahun         = $tahunMulai + $i - 1;
                            $isCurrent     = $tahun == now()->year;
                            $isPast        = $tahun < now()->year;
                            if ($isNonaktif && !$sudahDihentikan && $akumulasi > $snapshotAkumulasi + 1) {
                                $sudahDihentikan = true;
                            }
                        @endphp
                        <tr class="
                            {{ $sudahDihentikan ? 'opacity-30 bg-gray-50 dark:bg-gray-800/30' : '' }}
                            {{ !$sudahDihentikan && $isCurrent ? 'bg-green-50 dark:bg-green-900/10' : '' }}
                            {{ !$sudahDihentikan && $isPast && !$isNonaktif ? 'opacity-50' : '' }}
                            hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                            <td class="px-4 py-2.5 text-xs {{ !$sudahDihentikan && $isCurrent ? 'font-semibold text-green-700 dark:text-green-400' : 'text-gray-700 dark:text-gray-300' }}">
                                {{ $tahun }}
                                @if(!$sudahDihentikan && $isCurrent)
                                    <span class="ml-1 inline-flex items-center px-1.5 py-0.5 rounded text-xs bg-green-100 text-green-700">Tahun Ini</span>
                                @endif
                                @if($sudahDihentikan && ($akumulasi - $penyTahunan) <= $snapshotAkumulasi + 1)
                                    <span class="ml-1 inline-flex items-center px-1.5 py-0.5 rounded text-xs bg-red-100 text-red-600">Dihentikan</span>
                                @endif
                            </td>
                            <td class="px-4 py-2.5 text-right text-xs text-gray-600 dark:text-gray-400">Rp {{ number_format($nilaiBukuAwal, 0, ',', '.') }}</td>
                            <td class="px-4 py-2.5 text-right text-xs {{ $sudahDihentikan ? 'text-gray-300' : 'text-red-500' }}">Rp {{ number_format($penyTahunan, 0, ',', '.') }}</td>
                            <td class="px-4 py-2.5 text-right text-xs text-gray-600 dark:text-gray-400">Rp {{ number_format($akumulasi, 0, ',', '.') }}</td>
                            <td class="px-4 py-2.5 text-right text-xs font-semibold text-gray-900 dark:text-white">Rp {{ number_format($nilaiBuku, 0, ',', '.') }}</td>
                        </tr>
                        @endfor
                    </tbody>
                </table>
            </div>
            <p class="text-xs text-gray-400 mb-6">* Metode: Garis Lurus (Straight-Line)</p>

            {{-- Riwayat Jurnal Penyesuaian Aktual --}}
            @php $jurnalAset = $aset->jurnalPenyesuaian; @endphp
            @if($jurnalAset->count() > 0)
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-2">Riwayat Jurnal Penyesuaian Aktual</p>
            <p class="text-xs text-gray-400 mb-3">Penyusutan yang sudah benar-benar dicatat di jurnal penyesuaian.</p>
            <div class="overflow-x-auto rounded-xl border border-gray-100 dark:border-gray-800">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-800 border-b border-gray-100 dark:border-gray-800">
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Tanggal</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Periode</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Keterangan</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Nominal</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
                        @foreach($jurnalAset as $j)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                            <td class="px-4 py-2.5 text-xs text-gray-700 dark:text-gray-300">
                                {{ $j->tanggal->translatedFormat('d F Y') }}
                            </td>
                            <td class="px-4 py-2.5 text-xs text-gray-500 dark:text-gray-400">
                                {{ $j->periode->nama_periode ?? '—' }}
                            </td>
                            <td class="px-4 py-2.5 text-xs text-gray-500 dark:text-gray-400">
                                {{ $j->keterangan ?? '—' }}
                            </td>
                            <td class="px-4 py-2.5 text-right text-xs font-semibold text-red-500">
                                Rp {{ number_format($j->pivot->nominal, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-2.5 text-center">
                                @if($j->status === 'POSTED')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">Posted</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-700">Draft</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                        {{-- Total --}}
                        <tr class="bg-gray-50 dark:bg-gray-800">
                            <td colspan="3" class="px-4 py-2.5 text-xs font-semibold text-gray-700 dark:text-gray-300">Total Penyusutan Tercatat</td>
                            <td class="px-4 py-2.5 text-right text-xs font-bold text-red-500">
                                Rp {{ number_format($jurnalAset->sum('pivot.nominal'), 0, ',', '.') }}
                            </td>
                            <td></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            @else
            <div class="flex items-center gap-2 px-4 py-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-sm text-gray-500 dark:text-gray-400">Belum ada jurnal penyesuaian yang dicatat untuk aset ini.</p>
            </div>
            @endif

        </div>
        @else
        <div class="flex items-center gap-2 px-4 py-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
            <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-sm text-gray-500 dark:text-gray-400">Aset ini tidak disusutkan.</p>
        </div>
        @endif

        {{-- Keterangan --}}
        @if($aset->keterangan)
        <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-xl">
            <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Keterangan</p>
            <p class="text-sm text-gray-700 dark:text-gray-300">{{ $aset->keterangan }}</p>
        </div>
        @endif

    </div>

    {{-- Footer --}}
    <div class="flex justify-between items-center px-6 py-4 border-t border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/50 rounded-b-2xl">
        <button onclick="openEditModal({{ $aset->id }})"
            class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-yellow-700 border border-yellow-300 dark:border-yellow-700 dark:text-yellow-400 rounded-lg hover:bg-yellow-50 dark:hover:bg-yellow-900/20 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
            </svg>
            Edit Aset
        </button>
        <button onclick="closeModal('showAsetModal')"
            class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
            Tutup
        </button>
    </div>

</x-modal>
@extends('layouts.app')
@section('title', 'Transaksi')
@section('content')

<div class="space-y-4 p-6">
    <div class="bg-white rounded-2xl border border-gray-200 px-6 py-4 flex items-center justify-between">
        <div>
            <h1 class="text-xl font-semibold text-gray-900">Transaksi</h1>
            <p class="text-sm text-gray-500 mt-0.5">
                Periode aktif: {{ now()->translatedFormat('F Y') }}
            </p>
        </div>
        <div class="flex items-center gap-3">
            <button onclick="resetImpor(); openModal('modalImpor')"
                class="h-9 px-4 rounded-xl border border-green-700 text-green-700 text-sm font-medium hover:bg-green-50 transition-colors">
                Impor Transaksi
            </button>
            <button onclick="openModal('modalTambah')"
                class="h-9 px-4 rounded-xl bg-green-700 text-white text-sm font-medium hover:bg-green-800 transition-colors">
                Tambah Transaksi
            </button>
        </div>
    </div>

    {{-- Alert --}}
    <div id="success-alert"
        class="hidden items-center gap-3 bg-green-50 border border-green-200 rounded-xl px-4 py-3 text-sm text-green-700">
        <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
        </svg>
        <span id="success-alert-msg"></span>
    </div>

    <div id="error-alert"
        class="hidden items-center gap-3 bg-red-50 border border-red-200 rounded-xl px-4 py-3 text-sm text-red-700">
        <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
        </svg>
        <span id="error-alert-msg"></span>
    </div>

    <div class="grid grid-cols-3 gap-4">
        {{-- Total --}}
        <div class="bg-white rounded-2xl border border-gray-200 p-5 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-indigo-100 flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                </svg>
            </div>
            <div>
                <p class="text-2xl font-semibold text-gray-900">{{ number_format($stats['total']) }}</p>
                <p class="text-sm text-gray-500 mt-0.5">Total transaksi</p>
            </div>
        </div>

        {{-- Pemasukan --}}
        <div class="bg-white rounded-2xl border border-gray-200 p-5 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-green-100 flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
            </div>
            <div>
                <p class="text-2xl font-semibold text-gray-900">{{ number_format($stats['count_pemasukan']) }}</p>
                <p class="text-sm text-gray-500 mt-0.5">
                    Pemasukan &middot; Rp {{ number_format($stats['jumlah_pemasukan'], 0, ',', '.') }}
                </p>
            </div>
        </div>

        {{-- Pengeluaran --}}
        <div class="bg-white rounded-2xl border border-gray-200 p-5 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-red-100 flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 12H4"/>
                </svg>
            </div>
            <div>
                <p class="text-2xl font-semibold text-gray-900">{{ number_format($stats['count_pengeluaran']) }}</p>
                <p class="text-sm text-gray-500 mt-0.5">
                    Pengeluaran &middot; Rp {{ number_format($stats['jumlah_pengeluaran'], 0, ',', '.') }}
                </p>
            </div>
        </div>
    </div>

    {{-- Tabel --}}
    <div class="bg-white rounded-2xl border border-gray-200">

        {{-- Filter bar --}}
        <div class="px-5 py-3 border-b border-gray-100 flex flex-wrap items-center gap-3">

            {{-- Per page --}}
            <div class="flex items-center gap-2">
                <span class="text-sm text-gray-500">Tampil</span>
                <select id="filterPerPage" onchange="applyFilter()"
                    class="h-8 px-2 text-sm border border-gray-200 rounded-lg hover:border-gray-300 focus:outline-none focus:ring-1 focus:ring-green-500 focus:border-green-500 transition-colors">
                    @foreach ([10,25,50] as $n)
                        <option value="{{ $n }}" {{ request('per_page', 10) == $n ? 'selected' : '' }}>{{ $n }}</option>
                    @endforeach
                </select>
                <span class="text-sm text-gray-500">data</span>
            </div>

            <div class="ml-auto flex items-center gap-2 flex-wrap">

                <label class="flex items-center gap-2 bg-white border border-gray-200 rounded-xl px-3 py-1.5 cursor-pointer hover:border-gray-300 transition-colors">
                    <input type="checkbox" id="filterPeriodeAktif" onchange="onTogglePeriodeAktif()"
                        {{ request()->boolean('periode_aktif') ? 'checked' : '' }}
                        class="rounded border-gray-300 text-green-600 focus:ring-green-500">
                    <span class="text-sm text-gray-600">Periode aktif saja</span>
                </label>
            
                {{-- Tanggal --}}
                <div class="relative">
                    <div class="bg-white border border-gray-200 rounded-xl px-3 py-1.5 flex items-center gap-2 hover:border-gray-300 transition-colors cursor-pointer"
                        id="dateRangeDisplay" onclick="toggleDatePicker()">
                        <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <span id="dateRangeLabel" class="text-sm text-gray-500 whitespace-nowrap">Pilih tanggal</span>
                        <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>

                    {{-- Hidden inputs --}}
                    <input type="hidden" id="filterTanggalDari" value="{{ request('dari') }}">
                    <input type="hidden" id="filterTanggalSampai" value="{{ request('sampai') }}">

                    {{-- Dropdown picker --}}
                    <div id="datePickerDropdown"
                        class="hidden absolute top-full left-0 mt-2 z-50 bg-white border border-gray-200 rounded-2xl shadow-xl p-4 w-72">

                        {{-- Preset --}}
                        <div class="flex flex-wrap gap-1.5 mb-3">
                            <button type="button" onclick="setPreset('today')"
                                class="preset-btn px-2.5 py-1 text-xs border border-gray-200 rounded-lg text-gray-600 hover:bg-green-50 hover:border-green-400 hover:text-green-700 transition-colors">
                                Hari ini
                            </button>
                            <button type="button" onclick="setPreset('yesterday')"
                                class="preset-btn px-2.5 py-1 text-xs border border-gray-200 rounded-lg text-gray-600 hover:bg-green-50 hover:border-green-400 hover:text-green-700 transition-colors">
                                Kemarin
                            </button>
                            <button type="button" onclick="setPreset('this_week')"
                                class="preset-btn px-2.5 py-1 text-xs border border-gray-200 rounded-lg text-gray-600 hover:bg-green-50 hover:border-green-400 hover:text-green-700 transition-colors">
                                Minggu ini
                            </button>
                            <button type="button" onclick="setPreset('this_month')"
                                class="preset-btn px-2.5 py-1 text-xs border border-gray-200 rounded-lg text-gray-600 hover:bg-green-50 hover:border-green-400 hover:text-green-700 transition-colors">
                                Bulan ini
                            </button>
                            <button type="button" onclick="setPreset('last_month')"
                                class="preset-btn px-2.5 py-1 text-xs border border-gray-200 rounded-lg text-gray-600 hover:bg-green-50 hover:border-green-400 hover:text-green-700 transition-colors">
                                Bulan lalu
                            </button>
                            <button type="button" onclick="setPreset('this_year')"
                                class="preset-btn px-2.5 py-1 text-xs border border-gray-200 rounded-lg text-gray-600 hover:bg-green-50 hover:border-green-400 hover:text-green-700 transition-colors">
                                Tahun ini
                            </button>
                        </div>

                        <div class="border-t border-gray-100 pt-3 mb-3">
                            <p class="text-xs text-gray-400 mb-2">Atau pilih rentang manual:</p>
                            <input id="flatpickrInput" class="w-full h-9 px-3 text-xs border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-500/20 focus:border-green-500"
                                placeholder="Pilih rentang tanggal">
                        </div>

                        <div class="flex items-center justify-between pt-2 border-t border-gray-100">
                            <button type="button" onclick="resetTanggal()"
                                class="text-xs text-gray-400 hover:text-red-500 transition-colors">
                                Reset
                            </button>
                            <button type="button" onclick="applyTanggal()"
                                class="h-7 px-3 text-xs bg-green-700 text-white rounded-lg hover:bg-green-800 transition-colors">
                                Terapkan
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Akun --}}
                <div class="bg-white border border-gray-200 rounded-xl px-3 py-1.5 flex items-center gap-2 hover:border-gray-300 transition-colors">
                    <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                    </svg>
                    <select id="filterAkun" onchange="applyFilter()"
                        class="text-sm text-gray-600 border-0 outline-none bg-transparent">
                        <option value="">Semua Akun</option>
                        @foreach ($akuns as $a)
                            <option value="{{ $a->id }}" {{ request('akun_id') == $a->id ? 'selected' : '' }}>
                                {{ $a->kode_akun }} – {{ $a->nama_akun }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Search --}}
                <div class="bg-white border border-gray-200 rounded-xl px-3 py-1.5 flex items-center gap-2 hover:border-gray-300 transition-colors">
                    <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input id="filterSearch" type="text" value="{{ request('search') }}"
                        placeholder="Cari transaksi..."
                        onkeydown="if(event.key==='Enter') applyFilter()"
                        class="text-sm text-gray-600 border-0 outline-none bg-transparent w-36">
                </div>

                {{-- Reset --}}
                @if(request()->hasAny(['dari','sampai','kategori_id','akun_id','search']))
                    <a href="{{ route('dashboard.transaksi.index') }}"
                        class="h-8 px-3 flex items-center gap-1.5 text-sm text-gray-500 border border-gray-200 rounded-xl hover:bg-gray-50 transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Reset
                    </a>
                @endif
            </div>
        </div>

        {{-- Tabel --}}
        <div class="overflow-x-auto">
            <table class="w-full text-sm table-fixed min-w-[1100px]">
                <thead>
                    <tr class="border-b border-gray-100">
                        <th class="text-center text-xs font-semibold text-gray-500 uppercase tracking-wider px-3 py-3 w-[3%] whitespace-nowrap">No</th>
                        <th class="text-center text-xs font-semibold text-gray-500 uppercase tracking-wider px-3 py-3 w-[7%] whitespace-nowrap">Tanggal</th>
                        <th class="text-center text-xs font-semibold text-gray-500 uppercase tracking-wider px-3 py-3 w-[9%] whitespace-nowrap">Jumlah</th>
                        <th class="text-center text-xs font-semibold text-gray-500 uppercase tracking-wider px-3 py-3 w-[15%] whitespace-nowrap">Keterangan</th>
                        <th class="text-center text-xs font-semibold text-gray-500 uppercase tracking-wider px-3 py-3 w-[7%] whitespace-nowrap">Kategori</th>
                        <th class="text-center text-xs font-semibold text-gray-500 uppercase tracking-wider px-2 py-3 w-[10%] whitespace-nowrap">Jenis</th>
                        <th class="text-center text-xs font-semibold text-gray-500 uppercase tracking-wider px-2 py-3 w-[9%] whitespace-nowrap">Status</th>
                        <th class="text-center text-xs font-semibold text-gray-500 uppercase tracking-wider px-2 py-3 w-[9%] whitespace-nowrap">Jurnal</th>
                        <th class="text-center text-xs font-semibold text-gray-500 uppercase tracking-wider px-3 py-3 w-[18%] whitespace-nowrap">Detail Jurnal</th>
                        <th class="text-center text-xs font-semibold text-gray-500 uppercase tracking-wider px-3 py-3 w-[4%] whitespace-nowrap">Bukti</th>
                        <th class="text-right text-xs font-semibold text-gray-500 uppercase tracking-wider px-3 py-3 w-[9%] whitespace-nowrap">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                @forelse ($transaksis as $i => $t)
                    @php
                        $jurnal       = $t->jurnal->firstWhere('jenis_jurnal', 'UMUM');
                        $detailJurnal = $jurnal?->detailJurnal ?? collect();

                        $jurnalLines = $detailJurnal->where('tipe', 'DEBIT')->values()
                            ->concat($detailJurnal->where('tipe', 'KREDIT')->values());

                        $dariBendahara = is_null($t->status_approval);
                        $dariApproval  = $t->status_approval === 'APPROVED';
                        $dariKencleng  = $t->relationLoaded('kencleng') && !is_null($t->kencleng);
                        $dariKegiatan  = !is_null($t->kegiatan_id);

                        $isUnmapped = $dariApproval && $t->status_jurnal === 'UNMAPPED';

                        $bisaEditHapus = $isUnmapped || $jurnal?->status === 'DRAFT';
                    @endphp
                    <tr class="hover:bg-gray-50 transition-colors align-top">
                        <td class="px-3 py-3 text-gray-400 text-xs">{{ $transaksis->firstItem() + $i }}</td>
                        <td class="px-3 py-3 text-gray-700 text-xs">{{ $t->tanggal_transaksi->translatedFormat('d M Y') }}</td>
                        <td class="px-3 py-3 font-medium text-gray-900 text-xs break-words">
                            Rp {{ number_format($t->jumlah, 0, ',', '.') }}
                        </td>
                        <td class="px-3 py-3 text-gray-600 text-xs">
                            <span class="line-clamp-2 break-words">{{ $t->deskripsi ?? '-' }}</span>
                        </td>
                        <td class="px-3 py-3 text-gray-600 text-xs break-words">{{ $t->kategoriTransaksi?->nama_kategori ?? '-' }}</td>
                        <td class="px-2 py-3 text-xs">
                            <span @class([
                                'inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium',
                                'bg-green-100 text-green-800' => $t->jenis_transaksi === 'PEMASUKAN',
                                'bg-pink-100 text-pink-800'   => $t->jenis_transaksi === 'PENGELUARAN',
                            ])>
                                {{ $t->jenis_transaksi === 'PEMASUKAN' ? 'Pemasukan' : 'Pengeluaran' }}
                            </span>
                        </td>
                        <td class="px-2 py-3 text-xs">
                            @if($dariApproval)
                                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">
                                    Disetujui
                                </span>
                            @else
                                <span class="text-gray-300 text-xs">–</span>
                            @endif
                        </td>
                        <td class="px-2 py-3 text-xs">
                            @if($t->status_jurnal === 'UNMAPPED')
                                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700 text-center">
                                    Belum Dipetakan
                                </span>
                            @elseif($t->status_jurnal === 'MAPPED')
                                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700 text-center">
                                    Sudah Dipetakan
                                </span>
                            @else
                                <span class="text-gray-300 text-xs">–</span>
                            @endif
                        </td>
                        <td class="px-3 py-3 text-xs text-gray-600">
                            @forelse($jurnalLines as $line)
                                <div class="flex items-center justify-between gap-2 mb-0.5">
                                    <span class="break-words {{ $line->tipe === 'KREDIT' ? 'text-gray-400' : '' }}">
                                        {{ $line->akun->nama_akun ?? '-' }}
                                    </span>
                                    <span class="font-medium whitespace-nowrap {{ $line->tipe === 'DEBIT' ? 'text-red-600' : 'text-green-700' }}">
                                        Rp {{ number_format($line->nominal, 0, ',', '.') }}
                                    </span>
                                </div>
                            @empty
                                <span class="text-gray-300">-</span>
                            @endforelse
                        </td>
                        <td class="px-3 py-3 text-center">
                            @if($t->buktiTransaksi->isNotEmpty())
                                <button onclick="lihatBukti({{ $t->id }})" title="Lihat bukti"
                                    class="text-blue-500 hover:text-blue-700 transition-colors">
                                    <svg class="w-4 h-4 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </button>
                            @else
                                <span class="text-gray-300 text-xs">-</span>
                            @endif
                        </td>
                        <td class="px-3 py-3">
                            <div class="flex items-center justify-end gap-1">
                                @if($bisaEditHapus)
                                    <button onclick="editTransaksi({{ $t->id }})" title="{{ $isUnmapped ? 'Petakan Akun' : 'Edit' }}"
                                        class="p-1.5 transition-colors {{ $isUnmapped ? 'text-gray-400 hover:text-amber-700' : 'text-gray-400 hover:text-gray-700' }}">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </button>
                                    <button type="button" onclick="konfirmasiHapus({{ $t->id }})" title="Hapus"
                                        class="p-1.5 text-gray-400 hover:text-red-500 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                @else
                                    <span class="text-gray-300 text-xs">–</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11" class="px-4 py-16 text-center text-gray-400 text-sm">
                            Belum ada data transaksi.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($transaksis->hasPages())
        <div class="flex items-center justify-between px-5 py-4 border-t border-gray-100 flex-wrap gap-3">
            <div class="flex items-center gap-1">
                {{-- Previous --}}
                @if($transaksis->onFirstPage())
                    <span class="px-3 py-1.5 text-sm text-gray-300 border border-gray-200 rounded-lg">Previous</span>
                @else
                    <a href="{{ $transaksis->previousPageUrl() }}"
                    class="px-3 py-1.5 text-sm text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">Previous</a>
                @endif

                {{-- Page Numbers --}}
                @foreach($transaksis->getUrlRange(1, $transaksis->lastPage()) as $page => $url)
                    <a href="{{ $url }}"
                    class="w-8 h-8 flex items-center justify-center text-sm rounded-lg transition-colors
                        {{ $page === $transaksis->currentPage()
                            ? 'bg-green-600 text-white font-medium'
                            : 'text-gray-600 border border-gray-200 hover:bg-gray-50' }}">
                        {{ $page }}
                    </a>
                @endforeach

                {{-- Next --}}
                @if($transaksis->hasMorePages())
                    <a href="{{ $transaksis->nextPageUrl() }}"
                    class="px-3 py-1.5 text-sm text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">Next</a>
                @else
                    <span class="px-3 py-1.5 text-sm text-gray-300 border border-gray-200 rounded-lg">Next</span>
                @endif
            </div>

            <span class="text-xs text-gray-400">
                Showing {{ $transaksis->firstItem() }} to {{ $transaksis->lastItem() }} of {{ $transaksis->total() }} entries
            </span>
        </div>
        @endif
    </div>
</div>

<x-modal id="modalTambah" title="Tambah Transaksi">
    @include('pages.operasional.transaksi.create', [
        'akuns'     => $akuns,
        'dompets'   => $dompets,
    ])
</x-modal>

<x-modal id="modalEdit" title="Edit Transaksi">
    @include('pages.operasional.transaksi.edit', [
        'akuns'     => $akuns,
        'dompets'   => $dompets,
    ])
</x-modal>

<x-modal id="modalImpor" title="Impor Transaksi">
    @include('pages.operasional.transaksi.import', [
        'dompets' => $dompets,
    ])
</x-modal>

<x-confirm-modal
    id="modalHapus"
    title="Hapus Transaksi"
    message="Yakin ingin menghapus transaksi ini? Tindakan ini tidak dapat dibatalkan."
/>

<x-modal id="modalDuplikat" title="Transaksi Serupa Ditemukan">
    <div class="max-w-sm mx-auto text-center space-y-5 py-2">

        <div class="w-16 h-16 rounded-full bg-amber-100 flex items-center justify-center mx-auto">
            <svg class="w-8 h-8 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
        </div>

        <div class="space-y-1">
            <p class="text-sm font-medium text-gray-800">Sudah ada transaksi dengan data serupa</p>
            <p class="text-xs text-gray-400">Periksa detail berikut sebelum melanjutkan</p>
        </div>

        {{-- Detail duplikat --}}
        <div id="duplikatDetail"
            class="text-left bg-gray-50 border border-gray-200 rounded-xl divide-y divide-gray-100">
            <div class="flex items-center justify-between px-4 py-2.5">
                <span class="text-xs text-gray-500">Tanggal</span>
                <span id="dd_tanggal" class="text-xs font-medium text-gray-800"></span>
            </div>
            <div class="flex items-center justify-between px-4 py-2.5">
                <span class="text-xs text-gray-500">Jumlah</span>
                <span id="dd_jumlah" class="text-xs font-medium text-gray-800"></span>
            </div>
            <div class="flex items-center justify-between px-4 py-2.5">
                <span class="text-xs text-gray-500">Jenis</span>
                <span id="dd_jenis" class="text-xs font-medium text-gray-800"></span>
            </div>
            <div class="flex items-center justify-between px-4 py-2.5">
                <span class="text-xs text-gray-500">Kategori</span>
                <span id="dd_kategori" class="text-xs font-medium text-gray-800"></span>
            </div>
            <div class="flex items-center justify-between px-4 py-2.5">
                <span class="text-xs text-gray-500">Dompet</span>
                <span id="dd_dompet" class="text-xs font-medium text-gray-800"></span>
            </div>
            <div class="flex items-start justify-between px-4 py-2.5 gap-4">
                <span class="text-xs text-gray-500 shrink-0">Keterangan</span>
                <span id="dd_deskripsi" class="text-xs font-medium text-gray-800 text-right"></span>
            </div>
        </div>

        <p class="text-xs text-gray-400">
            Klik <span class="font-medium">Tetap Simpan</span> jika ini memang bukan duplikat.
        </p>

        <div class="flex items-center justify-center gap-3 pt-1">
            <button type="button" onclick="closeModal('modalDuplikat')"
                class="h-9 px-5 text-sm border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50 transition-colors">
                Batal
            </button>
            <button type="button" onclick="konfirmasiDuplikat()"
                class="h-9 px-5 text-sm bg-amber-500 text-white rounded-xl font-medium hover:bg-amber-600 transition-colors">
                Tetap Simpan
            </button>
        </div>
    </div>
</x-modal>

<x-modal id="modalBukti" title="Bukti Transaksi">
    <div id="buktiContainer" class="space-y-3">
    </div>
</x-modal>

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

function konfirmasiHapus(id) {
    const form = document.getElementById('modalHapusForm');
    form.action = `/dashboard/transaksi/${id}`;
    openModal('modalHapus');
}

function editTransaksi(id) {
    const btn = document.querySelector(`button[onclick="editTransaksi(${id})"]`);
    if (btn) btn.classList.add('opacity-50', 'pointer-events-none');

    fetch(`/dashboard/transaksi/${id}`, {
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
        }
    })
    .then(r => {
        if (!r.ok) throw new Error('HTTP ' + r.status);
        return r.json();
    })
    .then(({ data }) => {
        if (!data) throw new Error('Data tidak ditemukan');

        const f = document.getElementById('formEdit');

        f.reset();
        document.getElementById('listBuktiEdit').innerHTML = '';
        document.getElementById('editErrors').classList.add('hidden');
        document.getElementById('editErrorList').innerHTML = '';

        f.action = `/dashboard/transaksi/${id}`;

        const setVal = (name, val) => {
            const el = f.querySelector(`[name="${name}"]`);
            if (el) el.value = val ?? '';
        };

        if (typeof fpEditTanggal !== 'undefined' && fpEditTanggal) {
            fpEditTanggal.setDate(data.tanggal_transaksi?.substring(0, 10), true);
        }
        setVal('dompet_id',         data.dompet_id);
        setVal('jenis_transaksi',   data.jenis_transaksi);
        setVal('deskripsi',         data.deskripsi);

        renderExistingBukti(data.bukti_transaksi ?? []);
        document.getElementById('listBuktiEdit').innerHTML = '';
        document.getElementById('inputBuktiEdit').value = '';

        // Isi ulang tabel jurnal dari jurnal_entries (multi akun debit/kredit)
        const tbody = document.getElementById('jurnalEditBody');
        tbody.innerHTML = '';
        const entries = data.jurnal_entries ?? [];

        if (entries.length > 0) {
            entries.forEach(e =>
                buatBarisJurnal('jurnalEditBody', 'jurnalEdit', akunListEdit, e.tipe, e.akun_id, e.nominal)
            );
        } else {
            // Transaksi belum dipetakan ke akun (status UNMAPPED)
            const jumlahAwal = data.jumlah ?? '';
            buatBarisJurnal('jurnalEditBody', 'jurnalEdit', akunListEdit, 'DEBIT', '', jumlahAwal);
            buatBarisJurnal('jurnalEditBody', 'jurnalEdit', akunListEdit, 'KREDIT', '', jumlahAwal);
        }
        hitungTotalJurnal('jurnalEditBody', 'jurnalEdit');

        openModal('modalEdit');
    })
    .catch(err => {
        console.error('editTransaksi error:', err);
        alert('Gagal memuat data transaksi: ' + err.message);
    })
    .finally(() => {
        if (btn) btn.classList.remove('opacity-50', 'pointer-events-none');
    });
}

async function hapusBukti(buktiId) {
    if (!await confirmAsync('Hapus bukti ini?', { confirmLabel: 'Hapus' })) return;

    try {
        const res  = await fetch(`/dashboard/transaksi/bukti/${buktiId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                'Accept': 'application/json',
            }
        });
        const json = await res.json();

        if (json.success) {
            document.getElementById(`bukti-${buktiId}`)?.remove();
        } else {
            alert('Gagal menghapus bukti.');
        }
    } catch (e) {
        console.error(e);
        alert('Gagal menghubungi server.');
    }
}

function lihatBukti(id) {
    fetch(`/dashboard/transaksi/${id}`, { headers: { 'Accept': 'application/json' } })
        .then(r => r.json())
        .then(({ data }) => {
            const items = data.bukti_transaksi ?? [];
            const container = document.getElementById('buktiContainer');

            if (items.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-8 text-gray-400 text-sm">
                        Tidak ada bukti transaksi.
                    </div>`;
                openModal('modalBukti');
                return;
            }

            container.innerHTML = items.map(b => {
                const url  = `/storage/${b.path_file}`;
                const nama = b.nama_file ?? b.path_file.split('/').pop();
                const ext  = nama.split('.').pop().toLowerCase();
                const isPdf   = ext === 'pdf';
                const isImage = ['jpg','jpeg','png','webp','gif'].includes(ext);

                if (isImage) {
                    return `
                        <div class="space-y-2">
                            <p class="text-xs text-gray-500 font-medium">${nama}</p>
                            <img src="${url}" alt="${nama}"
                                class="w-full rounded-xl border border-gray-200 object-contain max-h-96">
                            <a href="${url}" target="_blank"
                                class="inline-flex items-center gap-1.5 text-xs text-green-700 hover:underline">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                </svg>
                                Buka di tab baru
                            </a>
                        </div>`;
                }

                if (isPdf) {
                    return `
                        <div class="space-y-2">
                            <p class="text-xs text-gray-500 font-medium">${nama}</p>
                            <iframe src="${url}" class="w-full rounded-xl border border-gray-200"
                                style="height:480px"></iframe>
                            <a href="${url}" target="_blank"
                                class="inline-flex items-center gap-1.5 text-xs text-green-700 hover:underline">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                </svg>
                                Buka di tab baru
                            </a>
                        </div>`;
                }

                return `
                    <a href="${url}" target="_blank"
                        class="flex items-center gap-3 px-4 py-3 border border-gray-200 rounded-xl hover:bg-gray-50 transition-colors">
                        <svg class="w-8 h-8 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414A1 1 0 0120 9.414V19a2 2 0 01-2 2z"/>
                        </svg>
                        <div>
                            <p class="text-sm font-medium text-gray-800">${nama}</p>
                            <p class="text-xs text-gray-400">Klik untuk membuka</p>
                        </div>
                        <svg class="w-4 h-4 text-gray-400 ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                        </svg>
                    </a>`;
            }).join('');

            openModal('modalBukti');
        })
        .catch(() => {
            document.getElementById('buktiContainer').innerHTML = `
                <div class="text-center py-8 text-red-400 text-sm">
                    Gagal memuat bukti transaksi.
                </div>`;
            openModal('modalBukti');
        });
}

function showAlert(type, message) {
    const el  = document.getElementById(type + '-alert');
    const msg = document.getElementById(type + '-alert-msg');
    msg.textContent = message;
    el.classList.remove('hidden');
    el.classList.add('flex');
    // Auto hide setelah 5 detik
    setTimeout(() => {
        el.classList.add('hidden');
        el.classList.remove('flex');
    }, 5000);
}

// ── Flatpickr ─────────────────────────────────────────────
let fp;
let selectedDari   = '{{ request('dari') }}';
let selectedSampai = '{{ request('sampai') }}';

document.addEventListener('DOMContentLoaded', function () {
    const alertData = sessionStorage.getItem('alert');
    if (alertData) {
        const { type, message } = JSON.parse(alertData);
        sessionStorage.removeItem('alert');
        showAlert(type, message);
    }

    const formHapus = document.getElementById('modalHapusForm');
    if (formHapus) {
        formHapus.addEventListener('submit', async function (e) {
            e.preventDefault();

            const btn = document.getElementById('modalHapus').querySelector('button[type="submit"]');
            if (btn) btn.disabled = true;

            try {
                const fd  = new FormData(this);
                const res = await fetch(this.action, {
                    method: 'POST',
                    body: fd,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept': 'application/json',
                    },
                });
                const data = await res.json();

                closeModal('modalHapus');

                sessionStorage.setItem('alert', JSON.stringify({
                    type: data.success ? 'success' : 'error',
                    message: data.message ?? (data.success ? 'Transaksi berhasil dihapus.' : 'Transaksi gagal dihapus.')
                }));
                window.location.reload();

            } catch {
                closeModal('modalHapus');
                sessionStorage.setItem('alert', JSON.stringify({
                    type: 'error',
                    message: 'Gagal menghubungi server.'
                }));
                window.location.reload();
            } finally {
                if (btn) btn.disabled = false;
            }
        });
    }

    fp = flatpickr('#flatpickrInput', {
        mode: 'range',
        dateFormat: 'Y-m-d',
        inline: true,
        locale: {
            firstDayOfWeek: 1,
            weekdays: {
                shorthand: ['Min','Sen','Sel','Rab','Kam','Jum','Sab'],
                longhand:  ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'],
            },
            months: {
                shorthand: ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'],
                longhand:  ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'],
            },
        },
        defaultDate: selectedDari && selectedSampai
            ? [selectedDari, selectedSampai]
            : (selectedDari ? [selectedDari] : []),
        onReady: function (selectedDates, dateStr, instance) {
            instance.calendarContainer.classList.add('kalender-kecil');
        },
        onChange: function (selectedDates) {
            if (selectedDates.length === 2) {
                selectedDari   = fp.formatDate(selectedDates[0], 'Y-m-d');
                selectedSampai = fp.formatDate(selectedDates[1], 'Y-m-d');
            } else if (selectedDates.length === 1) {
                selectedDari   = fp.formatDate(selectedDates[0], 'Y-m-d');
                selectedSampai = selectedDari;
            }
        },
    });

    // Tampilkan label awal jika ada filter aktif
    updateDateLabel();

    if (document.getElementById('filterPeriodeAktif').checked) {
        document.getElementById('dateRangeDisplay').classList.add('opacity-50', 'pointer-events-none');
    }

    // Tutup dropdown saat klik di luar
    document.addEventListener('click', function (e) {
        const dropdown = document.getElementById('datePickerDropdown');
        const display  = document.getElementById('dateRangeDisplay');
        if (!dropdown.contains(e.target) && !display.contains(e.target)) {
            dropdown.classList.add('hidden');
        }
    });
});

function toggleDatePicker() {
    document.getElementById('datePickerDropdown').classList.toggle('hidden');
}

function setPreset(preset) {
    const now   = new Date();
    let dari, sampai;

    switch (preset) {
        case 'today':
            dari = sampai = now;
            break;
        case 'yesterday':
            const yesterday = new Date(now);
            yesterday.setDate(now.getDate() - 1);
            dari = sampai = yesterday;
            break;
        case 'this_week':
            dari   = new Date(now);
            dari.setDate(now.getDate() - now.getDay() + 1); // Senin
            sampai = new Date(dari);
            sampai.setDate(dari.getDate() + 6); // Minggu
            break;
        case 'this_month':
            dari   = new Date(now.getFullYear(), now.getMonth(), 1);
            sampai = new Date(now.getFullYear(), now.getMonth() + 1, 0);
            break;
        case 'last_month':
            dari   = new Date(now.getFullYear(), now.getMonth() - 1, 1);
            sampai = new Date(now.getFullYear(), now.getMonth(), 0);
            break;
        case 'this_year':
            dari   = new Date(now.getFullYear(), 0, 1);
            sampai = new Date(now.getFullYear(), 11, 31);
            break;
    }

    fp.setDate([dari, sampai]);
    selectedDari   = fp.formatDate(dari, 'Y-m-d');
    selectedSampai = fp.formatDate(sampai, 'Y-m-d');
}

function applyTanggal() {
    document.getElementById('filterTanggalDari').value   = selectedDari;
    document.getElementById('filterTanggalSampai').value = selectedSampai;
    updateDateLabel();
    document.getElementById('datePickerDropdown').classList.add('hidden');
    applyFilter();
}

function resetTanggal() {
    selectedDari   = '';
    selectedSampai = '';
    fp.clear();
    document.getElementById('filterTanggalDari').value   = '';
    document.getElementById('filterTanggalSampai').value = '';
    document.getElementById('dateRangeLabel').textContent = 'Pilih tanggal';
    document.getElementById('dateRangeLabel').classList.remove('text-gray-700');
    document.getElementById('dateRangeLabel').classList.add('text-gray-500');
    document.getElementById('datePickerDropdown').classList.add('hidden');
    applyFilter();
}

function updateDateLabel() {
    const label = document.getElementById('dateRangeLabel');
    if (selectedDari && selectedSampai) {
        if (selectedDari === selectedSampai) {
            label.textContent = formatTanggal(selectedDari);
        } else {
            label.textContent = formatTanggal(selectedDari) + ' – ' + formatTanggal(selectedSampai);
        }
        label.classList.remove('text-gray-500');
        label.classList.add('text-gray-700');
    }
}

function formatTanggal(str) {
    const d = new Date(str);
    return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
}

function applyFilter() {
    const p             = new URLSearchParams();
    const perPage       = document.getElementById('filterPerPage').value;
    const periodeAktif  = document.getElementById('filterPeriodeAktif').checked;
    const dari          = document.getElementById('filterTanggalDari').value;
    const sampai        = document.getElementById('filterTanggalSampai').value;
    const akun          = document.getElementById('filterAkun').value;
    const search        = document.getElementById('filterSearch').value;

    if (perPage && perPage !== '10') p.set('per_page', perPage);

    if (periodeAktif) {
        p.set('periode_aktif', '1');
    } else {
        if (dari)   p.set('dari', dari);
        if (sampai) p.set('sampai', sampai);
    }

    if (akun)   p.set('akun_id', akun);
    if (search) p.set('search', search);

    window.location.search = p.toString();
}

function onTogglePeriodeAktif() {
    const checked = document.getElementById('filterPeriodeAktif').checked;
    if (checked) {
        document.getElementById('filterTanggalDari').value   = '';
        document.getElementById('filterTanggalSampai').value = '';
        document.getElementById('dateRangeDisplay').classList.add('opacity-50', 'pointer-events-none');
    } else {
        document.getElementById('dateRangeDisplay').classList.remove('opacity-50', 'pointer-events-none');
    }
    applyFilter();
}
</script>
@endsection
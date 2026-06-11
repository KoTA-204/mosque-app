@extends('layouts.app')

@section('title', 'Transaksi')

@section('content')
<div class="p-6 space-y-4">

    <div class="bg-white rounded-2xl border border-gray-200 px-6 py-4 flex items-center justify-between">
        <div>
            <h1 class="text-xl font-semibold text-gray-900">Review Transaksi</h1>
            @if(!empty($meta['periode']))
                <p class="text-sm text-gray-500 mt-0.5">
                    Periode {{ $meta['periode'] }}
                </p>
            @endif
        </div>
        <a href="{{ route('dashboard.transaksi.index') }}"
            class="h-9 px-4 rounded-xl border border-gray-200 text-sm text-gray-600 hover:bg-gray-50 transition-colors flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kembali
        </a>
    </div>

    <div class="grid grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl border border-gray-200 p-4 text-center">
            <p class="text-3xl font-semibold text-gray-900">{{ $stats['total'] }}</p>
            <p class="text-sm text-gray-500 mt-1">Total baris</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-200 p-4 text-center">
            <p class="text-3xl font-semibold text-green-700">{{ $stats['bersih'] }}</p>
            <p class="text-sm text-gray-500 mt-1">Perlu diklasifikasi</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-200 p-4 text-center">
            <p class="text-3xl font-semibold text-amber-500">{{ $stats['duplikat'] }}</p>
            <p class="text-sm text-gray-500 mt-1">Duplikat (dilewati)</p>
        </div>
    </div>

    @if(!empty($warnings))
    <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4 flex items-start gap-3">
        <svg class="w-5 h-5 text-amber-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
        </svg>
        <div class="text-sm text-amber-700">
            <strong>Peringatan parser:</strong>
            <ul class="mt-1 list-disc list-inside space-y-0.5">
                @foreach($warnings as $w)
                    <li>{{ $w }}</li>
                @endforeach
            </ul>
        </div>
    </div>
    @endif

    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
        <div class="px-5 py-3 border-b border-gray-100 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                </svg>
                <span class="text-sm font-medium text-gray-800">Impor Mutasi Bank</span>
                <span class="text-xs text-gray-400">Klasifikasi akun debit dan kredit</span>
            </div>
        </div>

        <form id="formKlasifikasi">
            @csrf
            <input type="hidden" name="import_key" value="{{ $key }}">

            <div class="overflow-x-auto">
                <table class="w-full text-sm" style="min-width:900px">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100 text-left">
                            <th class="px-4 py-3 text-xs font-medium text-gray-500 w-8">
                                <input type="checkbox" id="checkAll" onchange="toggleCheckAll(this)"
                                    class="rounded border-gray-300">
                            </th>
                            <th class="px-3 py-3 text-xs font-medium text-gray-500">No Ref</th>
                            <th class="px-3 py-3 text-xs font-medium text-gray-500">Tanggal</th>
                            <th class="px-3 py-3 text-xs font-medium text-gray-500 text-right">Jumlah</th>
                            <th class="px-3 py-3 text-xs font-medium text-gray-500">Keterangan</th>
                            <th class="px-3 py-3 text-xs font-medium text-gray-500 w-52">Akun Debit</th>
                            <th class="px-3 py-3 text-xs font-medium text-gray-500 w-52">Akun Kredit</th>
                            <th class="px-3 py-3 text-xs font-medium text-gray-500 w-8 text-center">...</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                    @foreach($rows as $i => $row)
                        <tr @class([
                            'transition-colors',
                            'bg-amber-50/60 opacity-60' => $row['is_duplikat'],
                            'hover:bg-gray-50'          => !$row['is_duplikat'],
                        ])>
                            <td class="px-4 py-3">
                                @if(!$row['is_duplikat'])
                                    <input type="checkbox" name="klasifikasi[{{ $i }}][skip]" value="0"
                                        class="rowCheck rounded border-gray-300"
                                        data-idx="{{ $i }}">
                                    <input type="hidden" name="klasifikasi[{{ $i }}][no_referensi]"
                                        value="{{ $row['no_referensi'] }}">
                                @else
                                    <svg class="w-4 h-4 text-amber-400 mx-auto" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                @endif
                            </td>
                            <td class="px-3 py-3 text-xs text-gray-500 font-mono">
                                {{ Str::limit($row['no_referensi'], 14) }}
                                @if($row['is_duplikat'])
                                    <span class="ml-1 px-1.5 py-0.5 bg-amber-100 text-amber-700 rounded text-xs">Duplikat</span>
                                @endif
                            </td>
                            <td class="px-3 py-3 text-xs text-gray-600 whitespace-nowrap">
                                {{ isset($row['waktu_transaksi'])
                                    ? \Carbon\Carbon::parse($row['waktu_transaksi'])->format('d M Y')
                                    : '-' }}
                            </td>
                            <td class="px-3 py-3 text-xs font-medium text-right whitespace-nowrap @if($row['jenis_transaksi']==='PENGELUARAN') text-red-600 @else text-green-700 @endif">
                                Rp {{ number_format($row['jumlah'], 0, ',', '.') }}
                            </td>
                            <td class="px-3 py-3 text-xs text-gray-600 max-w-xs">
                                <div class="line-clamp-2">{{ $row['deskripsi'] ?: '-' }}</div>
                                @if($row['nama_pengirim'])
                                    <div class="text-gray-400 mt-0.5">{{ $row['nama_pengirim'] }}</div>
                                @endif
                            </td>
                            {{-- Akun Debit --}}
                            <td class="px-3 py-3">
                                @if(!$row['is_duplikat'])
                                    <select name="klasifikasi[{{ $i }}][akun_debit_id]" required
                                        class="w-full h-8 px-2 text-xs border border-gray-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-green-500 focus:border-green-500">
                                        <option value="">Pilih akun</option>
                                        @foreach($akuns as $a)
                                            <option value="{{ $a->id }}">{{ $a->kode_akun }} – {{ $a->nama_akun }}</option>
                                        @endforeach
                                    </select>
                                @else
                                    <span class="text-xs text-gray-400">-</span>
                                @endif
                            </td>
                            {{-- Akun Kredit --}}
                            <td class="px-3 py-3">
                                @if(!$row['is_duplikat'])
                                    <select name="klasifikasi[{{ $i }}][akun_kredit_id]" required
                                        class="w-full h-8 px-2 text-xs border border-gray-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-green-500 focus:border-green-500">
                                        <option value="">Pilih akun</option>
                                        @foreach($akuns as $a)
                                            <option value="{{ $a->id }}">{{ $a->kode_akun }} – {{ $a->nama_akun }}</option>
                                        @endforeach
                                    </select>
                                @else
                                    <span class="text-xs text-gray-400">-</span>
                                @endif
                            </td>
                            {{-- Skip --}}
                            <td class="px-3 py-3 text-center">
                                @if(!$row['is_duplikat'])
                                    <button type="button"
                                        onclick="skipRow({{ $i }}, this)"
                                        class="text-xs text-gray-400 hover:text-red-500 transition-colors"
                                        title="Lewati baris ini">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Error --}}
            <div id="reviewErrorBox" class="hidden mx-5 my-3 p-3 bg-red-50 border border-red-200 rounded-xl">
                <ul id="reviewErrorList" class="text-sm text-red-600 space-y-0.5 list-disc list-inside"></ul>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-2xl border border-gray-200 px-6 py-4 flex items-center justify-between">
        <span class="text-sm text-gray-500">
            Showing 1 to {{ $stats['total'] }} of {{ $stats['total'] }} entries
        </span>
        <button type="button" onclick="simpanKlasifikasi()"
            id="btnSimpanKlasifikasi"
            class="h-9 px-5 bg-green-700 text-white text-sm font-medium rounded-xl hover:bg-green-800 transition-colors flex items-center gap-2">
            <svg id="spinnerKlasifikasi" class="hidden w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
            </svg>
            Simpan
        </button>
    </div>

</div>

@include('components.modal', [
    'id'    => 'modalHasilImport',
    'title' => 'Impor Transaksi',
    'slot'  => view('pages.transaksi.import-result'),
])

<script>
function toggleCheckAll(master) {
    document.querySelectorAll('.rowCheck').forEach(cb => cb.checked = master.checked);
}

function skipRow(idx, btn) {
    const row = btn.closest('tr');
    row.classList.toggle('opacity-40');

    const skipInput = document.querySelector(`[name="klasifikasi[${idx}][skip]"]`);
    if (skipInput) {
        skipInput.value = skipInput.value === '1' ? '0' : '1';
        // Toggle required pada select di baris ini
        row.querySelectorAll('select').forEach(sel => {
            if (skipInput.value === '1') sel.removeAttribute('required');
            else sel.setAttribute('required', '');
        });
    }
    btn.classList.toggle('text-gray-400');
    btn.classList.toggle('text-red-500');
}

async function simpanKlasifikasi() {
    const btn     = document.getElementById('btnSimpanKlasifikasi');
    const spinner = document.getElementById('spinnerKlasifikasi');
    const errBox  = document.getElementById('reviewErrorBox');
    const errList = document.getElementById('reviewErrorList');

    // Validasi: setiap baris non-skip & non-duplikat harus punya akun
    let valid = true;
    errList.innerHTML = '';

    document.querySelectorAll('tbody tr:not(.opacity-60)').forEach((row, i) => {
        const selects = row.querySelectorAll('select');
        selects.forEach(sel => {
            if (sel.hasAttribute('required') && !sel.value) {
                valid = false;
                sel.classList.add('border-red-400');
            } else {
                sel.classList.remove('border-red-400');
            }
        });
    });

    if (!valid) {
        errList.insertAdjacentHTML('beforeend', '<li>Pilih akun debit dan kredit untuk setiap baris yang aktif.</li>');
        errBox.classList.remove('hidden');
        errBox.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        return;
    }

    errBox.classList.add('hidden');
    btn.disabled = true;
    spinner.classList.remove('hidden');

    try {
        const fd  = new FormData(document.getElementById('formKlasifikasi'));
        const res = await fetch('{{ route("dashboard.transaksi.import.simpan") }}', {
            method: 'POST',
            body: fd,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                'Accept': 'application/json',
            },
        });
        const data = await res.json();

        if (data.success && data.type === 'import_success') {
            // Isi modal sukses
            document.getElementById('hasilTersimpan').textContent = data.tersimpan;
            document.getElementById('hasilDuplikat').textContent  = data.duplikat;
            document.getElementById('hasilTotal').textContent     = data.total;
            document.getElementById('hasilPeriode').textContent   = data.periode;
            document.getElementById('hasilState').setAttribute('data-state', 'sukses');
            openModal('modalHasilImport');
        } else {
            if (res.status === 422 && data.errors) {
                Object.values(data.errors).flat().forEach(msg => {
                    errList.insertAdjacentHTML('beforeend', `<li>${msg}</li>`);
                });
            } else {
                errList.insertAdjacentHTML('beforeend', `<li>${data.message ?? 'Terjadi kesalahan.'}</li>`);
            }
            errBox.classList.remove('hidden');
        }
    } catch {
        errList.insertAdjacentHTML('beforeend', '<li>Gagal menghubungi server. Coba lagi.</li>');
        errBox.classList.remove('hidden');
    } finally {
        btn.disabled = false;
        spinner.classList.add('hidden');
    }
}
</script>
@endsection
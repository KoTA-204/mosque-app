@extends('layouts.app')
@section('title', 'Jurnal Pembuka')
@section('content')
<div class="space-y-4 p-6">

    {{-- ── Header ─────────────────────────────────────────────────────────── --}}
    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 px-6 py-4
                flex items-center justify-between">
        <div>
            <h1 class="text-xl font-semibold text-gray-900 dark:text-white">Jurnal Pembuka</h1>
            @if($periodeAktif = \App\Models\Periode::aktif()->latest('tanggal_awal')->first())
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                    Periode aktif: {{ $periodeAktif->nama_periode }}
                </p>
            @endif
        </div>
        @if(auth()->user()->hasPermission('CREATE_JURNAL_PEMBUKA'))
        <a href="{{ route('dashboard.jurnal-pembuka.create') }}"
        class="inline-flex items-center gap-2 border border-green-700 text-green-700
                text-sm font-medium px-4 py-2 rounded-xl hover:bg-green-50 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Buat Jurnal
        </a>
        @endif
    </div>

    {{-- ── Flash message ───────────────────────────────────────────────────── --}}
    @if(session('success'))
        <x-jurnal.alert type="success" :message="session('success')"/>
    @endif
    @if(session('error'))
        <x-jurnal.alert type="error" :message="session('error')"/>
    @endif

    {{-- ── Stats ──────────────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-3 gap-4">
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-5
                    flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-indigo-100 dark:bg-indigo-900/30
                        flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2
                           M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
            <div>
                <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $stats['total'] }}</p>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Total jurnal pembuka</p>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-5
                    flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-green-100 dark:bg-green-900/30
                        flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <div>
                <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $stats['posted'] }}</p>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Posted</p>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-5
                    flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-amber-100 dark:bg-amber-900/30
                        flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6 text-amber-500" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $stats['draft'] }}</p>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Draft</p>
            </div>
        </div>
    </div>

    {{-- ── Tabel ───────────────────────────────────────────────────────────── --}}
    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 overflow-hidden">

        {{-- Toolbar --}}
        <x-jurnal.table-toolbar
            :route="route('dashboard.jurnal-pembuka.index')"
            :perPage="$perPage"
            :search="$search"
            :hiddenParams="['periode' => $periode, 'status' => $status]"
        >
            <x-slot name="filters">
                {{-- Filter Periode --}}
                <select name="periode" onchange="document.getElementById('filterForm').submit()"
                    class="text-sm border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-1.5
                           bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300
                           outline-none focus:border-green-400">
                    <option value="">Pilih Periode</option>
                    @foreach($periodes as $p)
                        <option value="{{ $p->id }}" {{ $periode == $p->id ? 'selected' : '' }}>
                            {{ $p->nama_periode }}
                        </option>
                    @endforeach
                </select>

                {{-- Filter Status --}}
                <select name="status" onchange="document.getElementById('filterForm').submit()"
                    class="text-sm border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-1.5
                           bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300
                           outline-none focus:border-green-400">
                    <option value="">Pilih Status</option>
                    <option value="POSTED" {{ $status === 'POSTED' ? 'selected' : '' }}>Posted</option>
                    <option value="DRAFT"  {{ $status === 'DRAFT'  ? 'selected' : '' }}>Draft</option>
                </select>
            </x-slot>
        </x-jurnal.table-toolbar>

        {{-- Tabel --}}
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-800/50 border-b border-gray-100 dark:border-gray-800 text-left">
                        <th class="px-4 py-3 text-xs font-medium text-gray-500 w-10">No</th>
                        <th class="px-4 py-3 text-xs font-medium text-gray-500">Kode</th>
                        <th class="px-4 py-3 text-xs font-medium text-gray-500">Periode</th>
                        <th class="px-4 py-3 text-xs font-medium text-gray-500">Keterangan</th>
                        <th class="px-4 py-3 text-xs font-medium text-gray-500 text-right">Debit</th>
                        <th class="px-4 py-3 text-xs font-medium text-gray-500 text-right">Kredit</th>
                        <th class="px-4 py-3 text-xs font-medium text-gray-500 text-center">Status</th>
                        <th class="px-4 py-3 text-xs font-medium text-gray-500 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
                @forelse($jurnals as $i => $j)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/30 transition-colors cursor-pointer"
                        onclick="bukaDrawer({{ $j->id }})">
                        <td class="px-4 py-3 text-gray-400 text-xs">
                            {{ $jurnals->firstItem() + $i }}
                        </td>
                        <td class="px-4 py-3 font-medium text-green-700 dark:text-green-400 text-sm">
                            {{ $j->kode_jurnal }}
                        </td>
                        <td class="px-4 py-3 text-gray-700 dark:text-gray-300">
                            {{ $j->periode?->nama_periode ?? $j->tanggal->format('M Y') }}
                        </td>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-400 max-w-xs">
                            <span class="line-clamp-1">{{ $j->keterangan ?? '—' }}</span>
                        </td>
                        <td class="px-4 py-3 text-right text-red-600 dark:text-red-400 font-medium">
                            Rp {{ number_format($j->total_debit, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-right text-green-700 dark:text-green-400 font-medium">
                            Rp {{ number_format($j->total_kredit, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($j->status === 'POSTED')
                                <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold
                                             bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400">
                                    POSTED
                                </span>
                            @else
                                <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold
                                             bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">
                                    DRAFT
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center" onclick="event.stopPropagation()">
                            <div class="flex items-center justify-center gap-1.5">
                                @if($j->status === 'DRAFT')
                                    <a href="{{ route('dashboard.jurnal-pembuka.edit', $j) }}"
                                    class="w-7 h-7 rounded-lg border border-gray-200 dark:border-gray-700
                                            flex items-center justify-center text-gray-500 dark:text-gray-400
                                            hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                                    title="Edit">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536
                                                L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                        </svg>
                                    </a>

                                    {{-- Posting --}}
                                    <button type="button"
                                        onclick="konfirmasiPosting({{ $j->id }})"
                                        class="w-7 h-7 rounded-lg border border-green-200 dark:border-green-900/50
                                            bg-green-50 dark:bg-green-900/20 flex items-center justify-center
                                            text-green-600 hover:bg-green-100 dark:hover:bg-green-900/40 transition-colors"
                                        title="Posting">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </button>

                                    {{-- Hapus --}}
                                    <button type="button"
                                        onclick="hapusJurnal({{ $j->id }})"  {{-- ← tetap sama --}}
                                        class="w-7 h-7 rounded-lg border border-red-200 dark:border-red-900/50
                                            bg-red-50 dark:bg-red-900/20 flex items-center justify-center
                                            text-red-500 hover:bg-red-100 dark:hover:bg-red-900/40 transition-colors"
                                        title="Hapus">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858
                                                L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button> 
                                @else
                                    <span class="text-xs text-gray-400 px-2">—</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-16 text-center text-gray-400 text-sm">
                            Belum ada data jurnal pembuka.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <x-jurnal.table-pagination
            :paginator="$jurnals"
            :queryParams="['search' => $search, 'periode' => $periode, 'status' => $status, 'per_page' => $perPage]"
        />
    </div>

</div>

{{-- ── Drawer Detail ───────────────────────────────────────────────────────── --}}
<x-jurnal.drawer title="Detail Jurnal Pembuka"/>

{{-- ── Modal Konfirmasi Hapus ─────────────────────────────────────────────── --}}
<x-confirm-modal
    id="deleteJurnalModal"
    title="Hapus Jurnal Pembuka"
    message="Jurnal pembuka yang dihapus tidak dapat dikembalikan."
/>

@endsection

@push('scripts')
<script>
const CSRF = document.querySelector('meta[name=csrf-token]').content;

// ── Buka drawer & load data ───────────────────────────────────────────────────
async function bukaDrawer(id) {
    openDrawer();

    const res  = await fetch(`/dashboard/jurnal-pembuka/${id}`, {
        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF }
    });
    const json = await res.json();

    if (!json.success) {
        document.getElementById('drawerContent').innerHTML =
            `<p class="text-sm text-red-500">Gagal memuat data.</p>`;
        return;
    }

    const d = json.data;
    const fmtRp = n => 'Rp ' + new Intl.NumberFormat('id-ID').format(n);
    const statusHtml = d.status === 'POSTED'
        ? `<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold
                        bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400">
               <span class="w-1.5 h-1.5 rounded-full bg-green-500 inline-block"></span> Posted</span>`
        : `<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold
                        bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">
               <span class="w-1.5 h-1.5 rounded-full bg-amber-500 inline-block"></span> Draft</span>`;

    const detailRows = d.detail.map(row => `
        <tr class="border-b border-gray-50 dark:border-gray-800">
            <td class="py-2 text-sm text-gray-700 dark:text-gray-300">${row.akun}</td>
            <td class="py-2 text-center">
                <span class="inline-flex w-5 h-5 items-center justify-center rounded text-xs font-bold
                    ${row.tipe === 'DEBIT'
                        ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400'
                        : 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400'}">
                    ${row.tipe === 'DEBIT' ? 'D' : 'K'}
                </span>
            </td>
            <td class="py-2 text-right text-sm ${row.tipe === 'DEBIT' ? 'text-red-600' : 'text-gray-300'}">${row.tipe === 'DEBIT' ? fmtRp(row.nominal) : '—'}</td>
            <td class="py-2 text-right text-sm ${row.tipe === 'KREDIT' ? 'text-green-700' : 'text-gray-300'}">${row.tipe === 'KREDIT' ? fmtRp(row.nominal) : '—'}</td>
        </tr>
    `).join('');

    document.getElementById('drawerContent').innerHTML = `
        <div class="mb-4">
            <p class="text-lg font-semibold text-green-700 dark:text-green-400">${d.kode_jurnal}</p>
            <div class="mt-1">${statusHtml}</div>
        </div>

        <div class="mb-5">
            <p class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-2">Informasi Jurnal</p>
            <div class="space-y-2">
                ${infoRow('Tanggal dibuat', d.tanggal)}
                ${infoRow('Periode', d.periode ?? '—')}
                ${infoRow('Keterangan', d.keterangan)}
                ${infoRow('Dibuat oleh', d.dibuat_oleh)}
            </div>
        </div>

        <div>
            <p class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-2">Detail Debit & Kredit</p>
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-gray-700">
                        <th class="pb-2 text-left text-xs font-medium text-gray-400">Akun</th>
                        <th class="pb-2 text-center text-xs font-medium text-gray-400">Pos.</th>
                        <th class="pb-2 text-right text-xs font-medium text-gray-400">Debit</th>
                        <th class="pb-2 text-right text-xs font-medium text-gray-400">Kredit</th>
                    </tr>
                </thead>
                <tbody>${detailRows}</tbody>
            </table>

            <div class="mt-3 rounded-xl bg-gray-50 dark:bg-gray-800/50
                        border border-gray-100 dark:border-gray-800 p-3
                        flex items-center justify-between">
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Total</span>
                <div class="flex items-center gap-4">
                    <div class="text-right">
                        <p class="text-xs text-gray-400">Debit</p>
                        <p class="text-sm font-bold text-red-600">${fmtRp(d.total_debit)}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs text-gray-400">Kredit</p>
                        <p class="text-sm font-bold text-green-700">${fmtRp(d.total_kredit)}</p>
                    </div>
                    <div class="flex items-center gap-1 text-xs font-medium
                        ${d.is_balance ? 'text-green-600' : 'text-red-500'}">
                        ${d.is_balance
                            ? '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Seimbang'
                            : '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01"/></svg> Belum seimbang'}
                    </div>
                </div>
            </div>
        </div>
    `;
}

function infoRow(label, value) {
    return `
        <div class="flex justify-between text-sm py-1.5 border-b border-gray-50 dark:border-gray-800">
            <span class="text-gray-500 dark:text-gray-400">${label}</span>
            <span class="font-medium text-gray-800 dark:text-gray-200 text-right max-w-xs">${value ?? '—'}</span>
        </div>
    `;
}

async function konfirmasiPosting(id) {
    if (!await confirmAsync('Yakin ingin memposting jurnal ini? Status tidak dapat dikembalikan ke Draft.', { title: 'Posting Jurnal', confirmLabel: 'Posting', confirmClass: 'bg-green-600 hover:bg-green-700' })) return;

    fetch(`/dashboard/jurnal-pembuka/${id}/posting`, {
        method: 'PATCH',
        headers: {
            'X-CSRF-TOKEN': CSRF,
            'Accept': 'application/json',
        },
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) window.location.reload();
        else alert(d.message);
    })
    .catch(() => alert('Gagal menghubungi server.'));
}

// ── Hapus jurnal ──────────────────────────────────────────────────────────────
async function hapusJurnal(id) {
    if (!await confirmAsync('Yakin ingin menghapus jurnal pembuka ini?', { confirmLabel: 'Hapus' })) return;

    fetch(`/dashboard/jurnal-pembuka/${id}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) window.location.reload();
        else alert(d.message);
    });
}

function hapusJurnal(id) {
    const form = document.getElementById('deleteJurnalModalForm');
    form.action = `/dashboard/jurnal-pembuka/${id}`;

    const modal = document.getElementById('deleteJurnalModal');
    modal.style.display = 'flex';
}

function openModal(id) {
    const modal = document.getElementById(id);
    modal.style.display = 'flex';
}

function closeModal(id) {
    const modal = document.getElementById(id);
    modal.style.display = 'none';
}

// ── Drawer helpers ────────────────────────────────────────────────────────────
function openDrawer() {
    document.getElementById('drawer').classList.remove('translate-x-full');
    document.getElementById('drawerOverlay').classList.remove('hidden');
    document.getElementById('drawerContent').innerHTML = `
        <div class="flex items-center justify-center py-10 text-gray-400 gap-2">
            <svg class="animate-spin w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M21 12a9 9 0 1 1-6.219-8.56"/>
            </svg>
            <span class="text-sm">Memuat...</span>
        </div>
    `;
}

function closeDrawer() {
    document.getElementById('drawer').classList.add('translate-x-full');
    document.getElementById('drawerOverlay').classList.add('hidden');
}
</script>
@endpush